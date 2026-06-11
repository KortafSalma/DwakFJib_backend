<?php

namespace App\Http\Controllers\Api;

use App\Models\Medication;
use App\Models\StockMovement;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryAnalyticsController extends Controller
{
    public function expiryMonitoring(Request $request)
    {
        $pharmacy = Auth::user()->pharmacy;

        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy profile required'], 403);
        }

        $medications = Medication::where('pharmacy_id', $pharmacy->id)
            ->select(['id', 'name', 'generic_name', 'stock', 'expiry_date', 'batch_number', 'category', 'price', 'low_stock_threshold'])
            ->get();

        $now = now();
        $in30Days = $now->copy()->addDays(30);
        $in90Days = $now->copy()->addDays(90);

        $grouped = [
            'expired' => [],
            'expiring_within_30_days' => [],
            'expiring_within_90_days' => [],
            'expiring_later' => [],
            'no_expiry' => [],
        ];

        foreach ($medications as $med) {
            $item = [
                'id' => $med->id,
                'name' => $med->name,
                'generic_name' => $med->generic_name,
                'stock' => $med->stock,
                'batch_number' => $med->batch_number,
                'category' => $med->category,
                'price' => $med->price,
                'days_until_expiry' => $med->expiry_date ? $now->diffInDays($med->expiry_date, false) : null,
                'expiry_date' => $med->expiry_date?->format('Y-m-d'),
                'potential_loss' => $med->stock * $med->price,
            ];

            if (!$med->expiry_date) {
                $grouped['no_expiry'][] = $item;
            } elseif ($med->expiry_date->lt($now)) {
                $item['status'] = 'expired';
                $grouped['expired'][] = $item;
            } elseif ($med->expiry_date->lte($in30Days)) {
                $item['status'] = 'expiring_soon';
                $grouped['expiring_within_30_days'][] = $item;
            } elseif ($med->expiry_date->lte($in90Days)) {
                $item['status'] = 'expiring_medium';
                $grouped['expiring_within_90_days'][] = $item;
            } else {
                $item['status'] = 'valid';
                $grouped['expiring_later'][] = $item;
            }
        }

        $totalPotentialLoss = collect($grouped['expired'])->sum('potential_loss')
            + collect($grouped['expiring_within_30_days'])->sum('potential_loss');

        return response()->json([
            'grouped' => $grouped,
            'summary' => [
                'total_expired' => count($grouped['expired']),
                'total_expiring_30d' => count($grouped['expiring_within_30_days']),
                'total_expiring_90d' => count($grouped['expiring_within_90_days']),
                'total_valid' => count($grouped['expiring_later']),
                'total_no_expiry' => count($grouped['no_expiry']),
                'potential_loss_amount' => round($totalPotentialLoss, 2),
            ],
        ]);
    }

    public function lowStockForecast(Request $request)
    {
        $pharmacy = Auth::user()->pharmacy;

        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy profile required'], 403);
        }

        $medications = Medication::where('pharmacy_id', $pharmacy->id)
            ->where('stock', '>', 0)
            ->get();

        $daysBack = $request->input('days', 90);
        $since = now()->subDays($daysBack);
        $forecasts = [];

        foreach ($medications as $med) {
            $outMovements = StockMovement::where('medication_id', $med->id)
                ->where('type', 'OUT')
                ->where('created_at', '>=', $since)
                ->sum('quantity');

            $inMovements = StockMovement::where('medication_id', $med->id)
                ->where('type', 'IN')
                ->where('created_at', '>=', $since)
                ->sum('quantity');

            $dailyConsumption = $daysBack > 0 ? ($outMovements / $daysBack) : 0;
            $dailyReplenishment = $daysBack > 0 ? ($inMovements / $daysBack) : 0;
            $netDailyChange = $dailyReplenishment - $dailyConsumption;

            $daysUntilStockout = $dailyConsumption > 0
                ? floor($med->stock / $dailyConsumption)
                : null;

            $predictedStock30d = $med->stock + ($netDailyChange * 30);
            $predictedStock90d = $med->stock + ($netDailyChange * 90);

            $risk = 'none';
            if ($daysUntilStockout !== null) {
                if ($daysUntilStockout <= 7) $risk = 'critical';
                elseif ($daysUntilStockout <= 30) $risk = 'high';
                elseif ($daysUntilStockout <= 60) $risk = 'medium';
                elseif ($daysUntilStockout <= 90) $risk = 'low';
            }

            if ($dailyConsumption > 0 || $med->isLowStock()) {
                $forecasts[] = [
                    'id' => $med->id,
                    'name' => $med->name,
                    'category' => $med->category,
                    'current_stock' => $med->stock,
                    'low_stock_threshold' => $med->low_stock_threshold,
                    'daily_consumption' => round($dailyConsumption, 2),
                    'daily_replenishment' => round($dailyReplenishment, 2),
                    'days_until_stockout' => $daysUntilStockout,
                    'risk' => $risk,
                    'predicted_stock_30d' => round(max(0, $predictedStock30d), 0),
                    'predicted_stock_90d' => round(max(0, $predictedStock90d), 0),
                    'is_low_stock' => $med->isLowStock(),
                ];
            }
        }

        usort($forecasts, function ($a, $b) {
            $riskOrder = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3, 'none' => 4];
            $aRisk = $riskOrder[$a['risk']] ?? 4;
            $bRisk = $riskOrder[$b['risk']] ?? 4;
            return $aRisk <=> $bRisk;
        });

        $summary = [
            'total_analyzed' => count($forecasts),
            'critical' => count(array_filter($forecasts, fn($f) => $f['risk'] === 'critical')),
            'high_risk' => count(array_filter($forecasts, fn($f) => $f['risk'] === 'high')),
            'medium_risk' => count(array_filter($forecasts, fn($f) => $f['risk'] === 'medium')),
            'low_stock_count' => count(array_filter($forecasts, fn($f) => $f['is_low_stock'])),
            'analysis_period_days' => $daysBack,
        ];

        return response()->json([
            'forecasts' => $forecasts,
            'summary' => $summary,
        ]);
    }

    public function movementHistory(Request $request)
    {
        $pharmacy = Auth::user()->pharmacy;

        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy profile required'], 403);
        }

        $query = StockMovement::whereHas('medication', function ($q) use ($pharmacy) {
            $q->where('pharmacy_id', $pharmacy->id);
        })->with(['medication:id,name,category', 'user:id,name']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('medication_id')) {
            $query->where('medication_id', $request->medication_id);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('medication', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $movements = $query->latest()->paginate($request->input('per_page', 30));

        $aggregates = StockMovement::whereHas('medication', function ($q) use ($pharmacy) {
            $q->where('pharmacy_id', $pharmacy->id);
        })
            ->selectRaw("type, COUNT(*) as count, SUM(quantity) as total_quantity")
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        return response()->json([
            'movements' => $movements->items(),
            'meta' => [
                'current_page' => $movements->currentPage(),
                'last_page' => $movements->lastPage(),
                'total' => $movements->total(),
                'per_page' => $movements->perPage(),
            ],
            'aggregates' => [
                'in_count' => (int) ($aggregates['IN']->count ?? 0),
                'in_quantity' => (int) ($aggregates['IN']->total_quantity ?? 0),
                'out_count' => (int) ($aggregates['OUT']->count ?? 0),
                'out_quantity' => (int) ($aggregates['OUT']->total_quantity ?? 0),
                'adjustment_count' => (int) ($aggregates['ADJUSTMENT']->count ?? 0),
                'expired_count' => (int) ($aggregates['EXPIRED']->count ?? 0),
            ],
        ]);
    }

    public function reorderRecommendations(Request $request)
    {
        $pharmacy = Auth::user()->pharmacy;

        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy profile required'], 403);
        }

        $medications = Medication::where('pharmacy_id', $pharmacy->id)
            ->where(function ($q) {
                $q->whereColumn('stock', '<=', 'low_stock_threshold')
                    ->orWhere('stock', '<=', 0);
            })
            ->get();

        $daysBack = 90;
        $since = now()->subDays($daysBack);
        $recommendations = [];

        foreach ($medications as $med) {
            $outMovements = StockMovement::where('medication_id', $med->id)
                ->where('type', 'OUT')
                ->where('created_at', '>=', $since)
                ->sum('quantity');

            $dailyConsumption = $daysBack > 0 ? ($outMovements / $daysBack) : 0;

            $leadTimeDays = 7;
            $safetyStock = (int) ceil($dailyConsumption * 14);
            $reorderPoint = (int) ceil($dailyConsumption * $leadTimeDays) + $safetyStock;
            $suggestedQuantity = max($reorderPoint - $med->stock, $med->low_stock_threshold);

            $urgency = 'low';
            if ($med->stock <= 0) $urgency = 'critical';
            elseif ($med->stock <= $med->low_stock_threshold / 2) $urgency = 'high';
            elseif ($med->stock <= $med->low_stock_threshold) $urgency = 'medium';

            $recommendations[] = [
                'id' => $med->id,
                'name' => $med->name,
                'category' => $med->category,
                'current_stock' => $med->stock,
                'low_stock_threshold' => $med->low_stock_threshold,
                'reorder_point' => $reorderPoint,
                'suggested_quantity' => max(1, $suggestedQuantity),
                'daily_consumption' => round($dailyConsumption, 2),
                'safety_stock' => $safetyStock,
                'urgency' => $urgency,
                'estimated_cost' => round(max(1, $suggestedQuantity) * $med->price, 2),
                'price_per_unit' => $med->price,
            ];
        }

        usort($recommendations, function ($a, $b) {
            $urgencyOrder = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];
            $aUrgency = $urgencyOrder[$a['urgency']] ?? 3;
            $bUrgency = $urgencyOrder[$b['urgency']] ?? 3;
            return $aUrgency <=> $bUrgency;
        });

        $totalCost = collect($recommendations)->sum('estimated_cost');

        return response()->json([
            'recommendations' => $recommendations,
            'summary' => [
                'total_to_reorder' => count($recommendations),
                'critical' => count(array_filter($recommendations, fn($r) => $r['urgency'] === 'critical')),
                'high' => count(array_filter($recommendations, fn($r) => $r['urgency'] === 'high')),
                'medium' => count(array_filter($recommendations, fn($r) => $r['urgency'] === 'medium')),
                'total_estimated_cost' => $totalCost,
                'total_suggested_units' => collect($recommendations)->sum('suggested_quantity'),
            ],
        ]);
    }

    public function trends(Request $request)
    {
        $pharmacy = Auth::user()->pharmacy;

        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy profile required'], 403);
        }

        $daysBack = $request->input('days', 90);
        $since = now()->subDays($daysBack);

        $dailyMovements = StockMovement::whereHas('medication', function ($q) use ($pharmacy) {
            $q->where('pharmacy_id', $pharmacy->id);
        })
            ->where('created_at', '>=', $since)
            ->selectRaw("DATE(created_at) as date, type, SUM(quantity) as total_quantity, COUNT(*) as count")
            ->groupBy(DB::raw('DATE(created_at)'), 'type')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        $dailyChart = [];
        $dateCursor = $since->copy();
        while ($dateCursor->lte(now())) {
            $dateKey = $dateCursor->format('Y-m-d');
            $dayData = $dailyMovements->get($dateKey, collect());
            $dailyChart[] = [
                'date' => $dateKey,
                'in' => (int) $dayData->where('type', 'IN')->sum('total_quantity'),
                'out' => (int) $dayData->where('type', 'OUT')->sum('total_quantity'),
                'adjustments' => (int) $dayData->where('type', 'ADJUSTMENT')->sum('total_quantity'),
                'expired' => (int) $dayData->where('type', 'EXPIRED')->sum('total_quantity'),
            ];
            $dateCursor->addDay();
        }

        $categorySummary = Medication::where('pharmacy_id', $pharmacy->id)
            ->selectRaw("COALESCE(category, 'Uncategorized') as category, COUNT(*) as count, SUM(stock) as total_stock, SUM(stock * price) as total_value")
            ->groupBy('category')
            ->get();

        $totalMovements = StockMovement::whereHas('medication', function ($q) use ($pharmacy) {
            $q->where('pharmacy_id', $pharmacy->id);
        })
            ->where('created_at', '>=', $since)
            ->selectRaw("type, COUNT(*) as count, SUM(quantity) as total_quantity")
            ->groupBy('type')
            ->get();

        $globalTrend = [
            'total_in' => (int) $totalMovements->where('type', 'IN')->sum('total_quantity'),
            'total_out' => (int) $totalMovements->where('type', 'OUT')->sum('total_quantity'),
            'total_adjustments' => (int) $totalMovements->where('type', 'ADJUSTMENT')->sum('total_quantity'),
            'total_expired' => (int) $totalMovements->where('type', 'EXPIRED')->sum('total_quantity'),
            'net_flow' => (int) ($totalMovements->where('type', 'IN')->sum('total_quantity')
                - $totalMovements->where('type', 'OUT')->sum('total_quantity')),
        ];

        $topMoving = StockMovement::whereHas('medication', function ($q) use ($pharmacy) {
            $q->where('pharmacy_id', $pharmacy->id);
        })
            ->where('type', 'OUT')
            ->where('created_at', '>=', $since)
            ->selectRaw("medication_id, SUM(quantity) as total_moved")
            ->groupBy('medication_id')
            ->orderByDesc('total_moved')
            ->take(10)
            ->with('medication:id,name,category,stock,price')
            ->get()
            ->map(fn($m) => [
                'id' => $m->medication->id,
                'name' => $m->medication->name,
                'category' => $m->medication->category,
                'total_moved' => (int) $m->total_moved,
                'current_stock' => $m->medication->stock,
            ]);

        return response()->json([
            'daily_chart' => $dailyChart,
            'category_summary' => $categorySummary,
            'global_trend' => $globalTrend,
            'top_moving' => $topMoving,
        ]);
    }
}

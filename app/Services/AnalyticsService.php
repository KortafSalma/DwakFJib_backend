<?php

namespace App\Services;

use App\Models\User;
use App\Models\Pharmacy;
use App\Models\Medication;
use App\Models\Reservation;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Distributor;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public static function getDashboardStats()
    {
        return [
            'users' => [
                'total' => User::count(),
                'active' => User::where('is_active', true)->count(),
                'banned' => User::whereNotNull('banned_at')->count(),
                'by_role' => User::selectRaw('role, count(*) as count')
                    ->groupBy('role')
                    ->get()
                    ->pluck('count', 'role'),
                'recent_signups' => User::where('created_at', '>=', now()->subDays(7))->count(),
            ],
            'pharmacies' => [
                'total' => Pharmacy::count(),
                'verified' => Pharmacy::where('is_verified', true)->count(),
                'pending_verification' => Pharmacy::where('is_verified', false)->count(),
                'average_rating' => round(Pharmacy::where('is_verified', true)->avg('rating'), 2),
            ],
            'medications' => [
                'total' => Medication::count(),
                'in_stock' => Medication::inStock()->count(),
                'out_of_stock' => Medication::where('stock', 0)->count(),
                'low_stock' => Medication::lowStock()->count(),
                'expired' => Medication::expired()->count(),
                'categories' => Medication::selectRaw('category, count(*) as count')
                    ->whereNotNull('category')
                    ->groupBy('category')
                    ->get()
                    ->pluck('count', 'category'),
            ],
            'reservations' => [
                'total' => Reservation::count(),
                'pending' => Reservation::where('status', 'PENDING')->count(),
                'paid' => Reservation::where('status', 'PAID')->count(),
                'completed' => Reservation::where('status', 'COMPLETED')->count(),
                'cancelled' => Reservation::where('status', 'CANCELLED')->count(),
                'expired' => Reservation::where('status', 'EXPIRED')->count(),
            ],
            'orders' => [
                'total' => Order::count(),
                'pending' => Order::where('status', 'PENDING')->count(),
                'confirmed' => Order::where('status', 'CONFIRMED')->count(),
                'shipped' => Order::where('status', 'SHIPPED')->count(),
                'delivered' => Order::where('status', 'DELIVERED')->count(),
                'cancelled' => Order::where('status', 'CANCELLED')->count(),
            ],
            'payments' => [
                'total' => Payment::count(),
                'total_revenue' => Payment::where('status', 'COMPLETED')->sum('amount'),
                'pending' => Payment::where('status', 'PENDING')->sum('amount'),
                'completed' => Payment::where('status', 'COMPLETED')->sum('amount'),
                'refunded' => Payment::where('status', 'REFUNDED')->sum('amount'),
            ],
            'distributors' => [
                'total' => Distributor::count(),
            ],
            'reviews' => [
                'total' => Review::count(),
                'average_rating' => round(Review::avg('rating'), 2),
                'recent' => Review::recent(7)->count(),
            ],
        ];
    }

    public static function getRevenueChartData($days = 30)
    {
        return Payment::where('status', 'COMPLETED')
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($row) => [
                'date' => $row->date,
                'total' => (float) $row->total,
                'count' => (int) $row->count,
            ]);
    }

    public static function getReservationChartData($days = 30)
    {
        return Reservation::where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, status, COUNT(*) as count')
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get()
            ->groupBy('date')
            ->map(fn($statuses) => [
                'date' => $statuses->first()->date,
                'pending' => $statuses->where('status', 'PENDING')->sum('count'),
                'paid' => $statuses->where('status', 'PAID')->sum('count'),
                'completed' => $statuses->where('status', 'COMPLETED')->sum('count'),
                'cancelled' => $statuses->where('status', 'CANCELLED')->sum('count'),
            ])
            ->values();
    }

    public static function getTopMedications($limit = 10)
    {
        return Medication::selectRaw('medications.id, medications.name, medications.category, SUM(reservations.quantity) as total_reserved')
            ->join('reservations', 'medications.id', '=', 'reservations.medication_id')
            ->whereIn('reservations.status', ['PAID', 'COMPLETED'])
            ->groupBy('medications.id', 'medications.name', 'medications.category')
            ->orderByDesc('total_reserved')
            ->limit($limit)
            ->get();
    }

    public static function getTopPharmacies($limit = 10)
    {
        return Pharmacy::selectRaw('pharmacies.id, pharmacies.name, pharmacies.city, pharmacies.rating, COUNT(reservations.id) as total_reservations')
            ->leftJoin('reservations', 'pharmacies.id', '=', 'reservations.pharmacy_id')
            ->groupBy('pharmacies.id', 'pharmacies.name', 'pharmacies.city', 'pharmacies.rating')
            ->orderByDesc('total_reservations')
            ->limit($limit)
            ->get();
    }

    public static function getActivityTimeline($limit = 20)
    {
        $reservations = Reservation::with(['user', 'medication'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn($r) => [
                'type' => 'reservation',
                'action' => $r->status,
                'user' => $r->user?->name,
                'details' => "{$r->medication?->name} (Qty: {$r->quantity})",
                'created_at' => $r->created_at,
            ]);

        $orders = Order::with(['pharmacy', 'distributor'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn($o) => [
                'type' => 'order',
                'action' => $o->status,
                'user' => $o->pharmacy?->name,
                'details' => "Order {$o->order_number}",
                'created_at' => $o->created_at,
            ]);

        return $reservations->concat($orders)
            ->sortByDesc('created_at')
            ->take($limit)
            ->values();
    }
}

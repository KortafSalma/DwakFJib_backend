<?php

namespace App\Services;

use App\Models\Medication;
use App\Models\Reservation;
use App\Models\Order;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class ExportService
{
    public static function exportMedications($filters = [])
    {
        $query = Medication::with('pharmacy');

        if (!empty($filters['pharmacy_id'])) {
            $query->where('pharmacy_id', $filters['pharmacy_id']);
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['in_stock'])) {
            $query->inStock();
        }

        $medications = $query->get();

        $headers = ['ID', 'Name', 'Category', 'Dosage', 'Stock', 'Price', 'Pharmacy', 'Expiry Date', 'Batch Number', 'Created At'];

        $rows = $medications->map(fn($m) => [
            $m->id,
            $m->name,
            $m->category ?? '',
            $m->dosage ?? '',
            $m->stock,
            $m->price,
            $m->pharmacy?->name ?? '',
            $m->expiry_date?->format('Y-m-d') ?? '',
            $m->batch_number ?? '',
            $m->created_at->format('Y-m-d H:i:s'),
        ])->toArray();

        return self::toCsv($headers, $rows, 'medications_export');
    }

    public static function exportReservations($filters = [])
    {
        $query = Reservation::with(['user', 'pharmacy', 'medication']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['pharmacy_id'])) {
            $query->where('pharmacy_id', $filters['pharmacy_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $reservations = $query->get();

        $headers = ['ID', 'User', 'Pharmacy', 'Medication', 'Quantity', 'Deposit', 'Status', 'Prescription', 'Expires At', 'Created At'];

        $rows = $reservations->map(fn($r) => [
            $r->id,
            $r->user?->name ?? '',
            $r->pharmacy?->name ?? '',
            $r->medication?->name ?? '',
            $r->quantity,
            $r->deposit_amount,
            $r->status,
            $r->prescription_file ? 'Yes' : 'No',
            $r->expires_at?->format('Y-m-d H:i:s') ?? '',
            $r->created_at->format('Y-m-d H:i:s'),
        ])->toArray();

        return self::toCsv($headers, $rows, 'reservations_export');
    }

    public static function exportOrders($filters = [])
    {
        $query = Order::with(['pharmacy', 'distributor', 'payment']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['pharmacy_id'])) {
            $query->where('pharmacy_id', $filters['pharmacy_id']);
        }

        if (!empty($filters['distributor_id'])) {
            $query->where('distributor_id', $filters['distributor_id']);
        }

        $orders = $query->get();

        $headers = ['ID', 'Order Number', 'Pharmacy', 'Distributor', 'Total Amount', 'Status', 'Payment Status', 'Delivery Date', 'Created At'];

        $rows = $orders->map(fn($o) => [
            $o->id,
            $o->order_number,
            $o->pharmacy?->name ?? '',
            $o->distributor?->name ?? '',
            $o->total_amount,
            $o->status,
            $o->payment?->status ?? 'No Payment',
            $o->delivery_date?->format('Y-m-d H:i:s') ?? '',
            $o->created_at->format('Y-m-d H:i:s'),
        ])->toArray();

        return self::toCsv($headers, $rows, 'orders_export');
    }

    public static function exportStockMovements($filters = [])
    {
        $query = StockMovement::with(['medication', 'user', 'reservation']);

        if (!empty($filters['medication_id'])) {
            $query->where('medication_id', $filters['medication_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $movements = $query->latest()->get();

        $headers = ['ID', 'Medication', 'Type', 'Quantity', 'Stock Before', 'Stock After', 'User', 'Reason', 'Created At'];

        $rows = $movements->map(fn($m) => [
            $m->id,
            $m->medication?->name ?? '',
            $m->type,
            $m->quantity,
            $m->stock_before,
            $m->stock_after,
            $m->user?->name ?? 'System',
            $m->reason ?? '',
            $m->created_at->format('Y-m-d H:i:s'),
        ])->toArray();

        return self::toCsv($headers, $rows, 'stock_movements_export');
    }

    protected static function toCsv($headers, $rows, $filename)
    {
        $callback = function () use ($headers, $rows) {
            $file = fopen('php://output', 'w');

            fputcsv($file, $headers);

            foreach ($rows as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}_" . now()->format('Y-m-d') . ".csv\"",
            'Cache-Control' => 'no-store',
        ]);
    }
}

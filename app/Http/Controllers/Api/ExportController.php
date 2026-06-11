<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExportService;
use Illuminate\Support\Facades\Auth;

class ExportController extends Controller
{
    public function medications()
    {
        $filters = request()->only(['pharmacy_id', 'category', 'in_stock']);

        return ExportService::exportMedications($filters);
    }

    public function reservations()
    {
        $filters = request()->only(['status', 'pharmacy_id', 'date_from', 'date_to']);

        return ExportService::exportReservations($filters);
    }

    public function orders()
    {
        $filters = request()->only(['status', 'pharmacy_id', 'distributor_id']);

        return ExportService::exportOrders($filters);
    }

    public function stockMovements()
    {
        $filters = request()->only(['medication_id', 'type', 'date_from', 'date_to']);

        return ExportService::exportStockMovements($filters);
    }
}

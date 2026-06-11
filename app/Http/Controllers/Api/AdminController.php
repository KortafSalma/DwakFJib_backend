<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dashboard()
    {
        return response()->json(AnalyticsService::getDashboardStats());
    }

    public function revenueChart()
    {
        $days = request('days', 30);

        return response()->json([
            'data' => AnalyticsService::getRevenueChartData($days),
            'period' => "{$days} days",
        ]);
    }

    public function reservationChart()
    {
        $days = request('days', 30);

        return response()->json([
            'data' => AnalyticsService::getReservationChartData($days),
            'period' => "{$days} days",
        ]);
    }

    public function topMedications()
    {
        $limit = request('limit', 10);

        return response()->json([
            'data' => AnalyticsService::getTopMedications($limit),
        ]);
    }

    public function topPharmacies()
    {
        $limit = request('limit', 10);

        return response()->json([
            'data' => AnalyticsService::getTopPharmacies($limit),
        ]);
    }

    public function activityTimeline()
    {
        $limit = request('limit', 20);

        return response()->json([
            'data' => AnalyticsService::getActivityTimeline($limit),
        ]);
    }
}

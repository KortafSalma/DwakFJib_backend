<?php

namespace App\Http\Controllers\Api;

use App\Models\LoyalPatient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LoyalPatientController extends Controller
{
    public function index()
    {
        $pharmacy = Auth::user()->pharmacy;

        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy not found'], 404);
        }

        $patients = LoyalPatient::where('pharmacy_id', $pharmacy->id)
            ->with('user')
            ->orderBy('total_spent', 'desc')
            ->paginate(20);

        return response()->json([
            'data' => $patients->items(),
            'meta' => [
                'current_page' => $patients->currentPage(),
                'last_page' => $patients->lastPage(),
                'total' => $patients->total(),
                'top_tiers' => [
                    'platine' => LoyalPatient::where('pharmacy_id', $pharmacy->id)->where('tier', 'Platine')->count(),
                    'or' => LoyalPatient::where('pharmacy_id', $pharmacy->id)->where('tier', 'Or')->count(),
                    'argent' => LoyalPatient::where('pharmacy_id', $pharmacy->id)->where('tier', 'Argent')->count(),
                    'bronze' => LoyalPatient::where('pharmacy_id', $pharmacy->id)->where('tier', 'Bronze')->count(),
                ],
            ],
        ]);
    }

    public function show($userId)
    {
        $pharmacy = Auth::user()->pharmacy;

        $patient = LoyalPatient::where('pharmacy_id', $pharmacy->id)
            ->where('user_id', $userId)
            ->with('user')
            ->first();

        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }

        return response()->json(['data' => $patient]);
    }
}

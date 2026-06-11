<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\MedicalCertificate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class FileController extends Controller
{
    public function downloadPrescription(Reservation $reservation)
    {
        if (!$reservation->prescription_file) {
            return response()->json([
                'message' => 'No prescription file attached'
            ], 404);
        }

        $this->authorizeAccess($reservation);

        if (!Storage::exists($reservation->prescription_file)) {
            return response()->json([
                'message' => 'File not found on server'
            ], 404);
        }

        return Storage::download($reservation->prescription_file);
    }

    public function downloadCertificate(MedicalCertificate $certificate)
    {
        if (!$certificate->file_path) {
            return response()->json([
                'message' => 'No certificate file attached'
            ], 404);
        }

        $this->authorizeCertificateAccess($certificate);

        if (!Storage::exists($certificate->file_path)) {
            return response()->json([
                'message' => 'File not found on server'
            ], 404);
        }

        return Storage::download($certificate->file_path);
    }

    protected function authorizeAccess(Reservation $reservation)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return;
        }

        if ($user->role === 'PHARMACY' && $reservation->pharmacy->user_id === $user->id) {
            return;
        }

        if ($reservation->user_id === $user->id) {
            return;
        }

        abort(403, 'Unauthorized');
    }

    protected function authorizeCertificateAccess(MedicalCertificate $certificate)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return;
        }

        if ($certificate->user_id === $user->id) {
            return;
        }

        abort(403, 'Unauthorized');
    }
}

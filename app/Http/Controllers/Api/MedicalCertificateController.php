<?php

namespace App\Http\Controllers\Api;

use App\Models\MedicalCertificate;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicalCertificateRequest;
use App\Http\Requests\UpdateMedicalCertificateRequest;
use App\Http\Resources\MedicalCertificateResource;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MedicalCertificateController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = MedicalCertificate::with('user')->latest();

        if ($user->role !== User::ROLE_ADMIN) {
            $query->where('user_id', $user->id);
        }

        if (request()->has('status')) {
            $query->where('status', request('status'));
        }

        return MedicalCertificateResource::collection($query->paginate(10));
    }

    public function store(StoreMedicalCertificateRequest $request)
    {
        $path = null;

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('medical-certificates');
        }

        $certificate = MedicalCertificate::create([
            'user_id' => Auth::id(),
            'file_path' => $path,
            'issue_date' => $request->issue_date,
            'expiry_date' => $request->expiry_date,
            'status' => 'PENDING',
        ]);

        AuditService::logCreated($certificate);

        NotificationService::sendToAdmins(
            'New Medical Certificate',
            'A new medical certificate is pending review.',
            ['certificate_id' => $certificate->id]
        );

        return response()->json([
            'message' => 'Medical certificate uploaded successfully',
            'data' => new MedicalCertificateResource($certificate)
        ], 201);
    }

    public function show(MedicalCertificate $medicalCertificate)
    {
        $this->authorize('view', $medicalCertificate);

        return new MedicalCertificateResource(
            $medicalCertificate->load('user')
        );
    }

    public function update(UpdateMedicalCertificateRequest $request, MedicalCertificate $medicalCertificate)
    {
        $this->authorize('update', $medicalCertificate);

        $oldValues = $medicalCertificate->toArray();

        $medicalCertificate->update($request->validated());

        if ($request->status === 'VERIFIED') {
            NotificationService::sendToUser(
                $medicalCertificate->user,
                'Certificate Verified',
                'Your medical certificate has been verified.',
                'ALERT',
                ['certificate_id' => $medicalCertificate->id]
            );
        }

        AuditService::logUpdated($medicalCertificate, $oldValues, $medicalCertificate->toArray());

        return response()->json([
            'message' => 'Medical certificate updated successfully',
            'data' => new MedicalCertificateResource($medicalCertificate)
        ]);
    }

    public function destroy(MedicalCertificate $medicalCertificate)
    {
        $this->authorize('delete', $medicalCertificate);

        if ($medicalCertificate->file_path) {
            Storage::delete($medicalCertificate->file_path);
        }

        AuditService::logDeleted($medicalCertificate);

        $medicalCertificate->delete();

        return response()->json([
            'message' => 'Medical certificate deleted successfully'
        ]);
    }
}

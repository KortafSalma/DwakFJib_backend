<?php

namespace App\Http\Controllers\Api;

use App\Models\Pharmacy;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePharmacyRequest;
use App\Http\Requests\UpdatePharmacyRequest;
use App\Http\Resources\PharmacyResource;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PharmacyController extends Controller
{
    public function index()
    {
        $query = Pharmacy::withCount('medications');

        if (request()->has('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('address', 'ilike', "%{$search}%")
                  ->orWhere('city', 'ilike', "%{$search}%");
            });
        }

        if (request()->has('city')) {
            $query->where('city', request('city'));
        }

        if (request()->has('verified')) {
            $query->where('is_verified', request('verified') === 'true');
        }

        if (request()->has('lat') && request()->has('lng')) {
            $radius = request('radius', 50);
            $query->nearby(request('lat'), request('lng'), $radius);
        }

        if (request()->has('min_rating')) {
            $query->where('rating', '>=', request('min_rating'));
        }

        $pharmacies = $query->latest()->paginate(10);

        return PharmacyResource::collection($pharmacies);
    }

    public function store(StorePharmacyRequest $request)
    {
        $this->authorize('create', Pharmacy::class);

        $pharmacy = DB::transaction(function () use ($request) {
            return Pharmacy::create([
                'user_id' => Auth::id(),
                'name' => $request->name,
                'address' => $request->address,
                'phone' => $request->phone,
                'city' => $request->city,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);
        });

        AuditService::logCreated($pharmacy);

        NotificationService::sendToAdmins(
            'New Pharmacy Registration',
            "{$pharmacy->name} has registered and is pending verification.",
            ['pharmacy_id' => $pharmacy->id]
        );

        return response()->json([
            'message' => 'Pharmacy created successfully',
            'data' => new PharmacyResource($pharmacy)
        ], 201);
    }

    public function show(Pharmacy $pharmacy)
    {
        return new PharmacyResource(
            $pharmacy->loadCount('medications')
        );
    }

    public function update(UpdatePharmacyRequest $request, Pharmacy $pharmacy)
    {
        $this->authorize('update', $pharmacy);

        $oldValues = $pharmacy->toArray();

        $pharmacy->update($request->validated());

        AuditService::logUpdated($pharmacy, $oldValues, $pharmacy->toArray());

        return response()->json([
            'message' => 'Pharmacy updated successfully',
            'data' => new PharmacyResource($pharmacy)
        ]);
    }

    public function destroy(Pharmacy $pharmacy)
    {
        $this->authorize('delete', $pharmacy);

        AuditService::logDeleted($pharmacy);

        $pharmacy->delete();

        return response()->json([
            'message' => 'Pharmacy deleted successfully'
        ]);
    }

    public function verify(Pharmacy $pharmacy)
    {
        $this->authorize('update', $pharmacy);

        $oldValues = $pharmacy->toArray();

        $pharmacy->update(['is_verified' => true]);

        AuditService::log('verified', $pharmacy, $oldValues, $pharmacy->toArray());

        NotificationService::sendToUser(
            $pharmacy->user,
            'Pharmacy Verified',
            "Your pharmacy {$pharmacy->name} has been verified.",
            'ALERT',
            ['pharmacy_id' => $pharmacy->id]
        );

        return response()->json([
            'message' => 'Pharmacy verified successfully',
            'data' => new PharmacyResource($pharmacy)
        ]);
    }

    public function medications(Pharmacy $pharmacy)
    {
        $medications = $pharmacy->medications()
            ->inStock()
            ->notExpired()
            ->latest()
            ->paginate(10);

        return \App\Http\Resources\MedicationResource::collection($medications);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Models\Medication;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicationRequest;
use App\Http\Requests\UpdateMedicationRequest;
use App\Http\Resources\MedicationResource;
use App\Services\AuditService;
use App\Services\StockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MedicationController extends Controller
{
    public function index()
    {
        $query = Medication::with('pharmacy');

        if (request()->has('search')) {
            $query->search(request('search'));
        }

        if (request()->has('category')) {
            $query->byCategory(request('category'));
        }

        if (request()->has('min_price') && request()->has('max_price')) {
            $query->byPriceRange(request('min_price'), request('max_price'));
        }

        if (request()->has('in_stock')) {
            $query->inStock();
        }

        if (request()->has('pharmacy_id')) {
            $query->where('pharmacy_id', request('pharmacy_id'));
        }

        if (request()->has('not_expired')) {
            $query->notExpired();
        }

        if (request()->has('is_derma')) {
            $query->where('is_derma', request('is_derma') === 'true');
        }

        $medications = $query->latest()->paginate(10);

        return MedicationResource::collection($medications);
    }

    public function store(StoreMedicationRequest $request)
    {
        $pharmacy = Auth::user()->pharmacy;

        if (!$pharmacy) {
            return response()->json([
                'message' => 'You must have a pharmacy to add medications'
            ], 403);
        }

        $medication = DB::transaction(function () use ($request, $pharmacy) {
            $data = [
                'pharmacy_id' => $pharmacy->id,
                'name' => $request->name,
                'generic_name' => $request->generic_name,
                'description' => $request->description,
                'dosage' => $request->dosage,
                'stock' => $request->stock,
                'price' => $request->price,
                'category' => $request->category,
                'is_derma' => $request->is_derma ?? false,
                'discount_percent' => $request->discount_percent ?? 0,
                'barcode' => $request->barcode ?? self::generateBarcode(),
                'requires_prescription' => $request->requires_prescription ?? false,
                'expiry_date' => $request->expiry_date,
                'batch_number' => $request->batch_number,
                'low_stock_threshold' => $request->low_stock_threshold ?? 10,
            ];

            $photoFields = ['photo_front', 'photo_back', 'photo_left', 'photo_right', 'photo_top'];
            foreach ($photoFields as $field) {
                if ($request->hasFile($field)) {
                    $data[$field] = $request->file($field)->store('medications/photos', 'public');
                }
            }

            return Medication::create($data);
        });

        AuditService::logCreated($medication);

        StockService::increment($medication, $request->stock, Auth::id(), null, 'Initial stock');

        return response()->json([
            'message' => 'Medication created successfully',
            'data' => new MedicationResource($medication)
        ], 201);
    }

    public function show(Medication $medication)
    {
        return new MedicationResource(
            $medication->load('pharmacy')
        );
    }

    public function update(UpdateMedicationRequest $request, Medication $medication)
    {
        $this->authorize('update', $medication);

        $oldValues = $medication->toArray();
        $data = $request->validated();

        $photoFields = ['photo_front', 'photo_back', 'photo_left', 'photo_right', 'photo_top'];
        foreach ($photoFields as $field) {
            if ($request->hasFile($field)) {
                if ($medication->$field) {
                    Storage::disk('public')->delete($medication->$field);
                }
                $data[$field] = $request->file($field)->store('medications/photos', 'public');
            }
        }

        $medication->update($data);

        AuditService::logUpdated($medication, $oldValues, $medication->toArray());

        return response()->json([
            'message' => 'Medication updated successfully',
            'data' => new MedicationResource($medication)
        ]);
    }

    public function destroy(Medication $medication)
    {
        $this->authorize('delete', $medication);

        $photoFields = ['photo_front', 'photo_back', 'photo_left', 'photo_right', 'photo_top'];
        foreach ($photoFields as $field) {
            if ($medication->$field) {
                Storage::disk('public')->delete($medication->$field);
            }
        }

        AuditService::logDeleted($medication);

        $medication->delete();

        return response()->json([
            'message' => 'Medication deleted successfully'
        ]);
    }

    public function stockHistory(Medication $medication)
    {
        $this->authorize('view', $medication);

        $movements = $medication->stockMovements()
            ->with('user')
            ->recent(request('days', 30))
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $movements->items(),
            'meta' => [
                'current_stock' => $medication->stock,
                'total_movements' => $movements->total(),
            ],
        ]);
    }

    public function adjustStock(Medication $medication)
    {
        $this->authorize('update', $medication);

        request()->validate([
            'new_stock' => 'required|integer|min:0',
            'reason' => 'nullable|string|max:500',
        ]);

        $movement = StockService::adjust(
            $medication,
            request('new_stock'),
            Auth::id(),
            request('reason', 'Manual adjustment')
        );

        return response()->json([
            'message' => 'Stock adjusted successfully',
            'data' => $movement,
            'current_stock' => $medication->fresh()->stock,
        ]);
    }

    public function scanBarcode($barcode)
    {
        $medication = Medication::where('barcode', $barcode)->with('pharmacy')->first();

        if (!$medication) {
            return response()->json(['message' => 'Medication not found for this barcode'], 404);
        }

        return new MedicationResource($medication);
    }

    public function purchase(Medication $medication)
    {
        $this->authorize('update', $medication);

        request()->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $quantity = request('quantity');

        if ($medication->stock < $quantity) {
            return response()->json(['message' => 'Insufficient stock'], 400);
        }

        $movement = StockService::decrement(
            $medication,
            $quantity,
            Auth::id(),
            null,
            'Sale - ' . $quantity . ' units'
        );

        return response()->json([
            'message' => 'Purchase completed successfully',
            'data' => $movement,
            'current_stock' => $medication->fresh()->stock,
        ]);
    }

    protected static function generateBarcode(): string
    {
        $prefix = 'DWF';
        $timestamp = now()->format('YmdHis');
        $random = strtoupper(substr(uniqid(), -4));
        return $prefix . $timestamp . $random;
    }
}

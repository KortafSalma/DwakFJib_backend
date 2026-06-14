<?php

namespace App\Http\Controllers\Api;

use App\Models\Pharmacy;
use App\Models\Medication;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\PharmacyResource;
use App\Http\Resources\MedicationResource;
use App\Http\Resources\UserResource;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function searchMedications()
    {
        $query = request('q', '');

        if (empty($query)) {
            return response()->json([
                'success' => true,
                'message' => 'Please provide a search query',
                'data' => [],
                'errors' => [],
            ]);
        }

        $medications = Medication::where('stock', '>', 0)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('generic_name', 'like', "%{$query}%")
                  ->orWhere('category', 'like', "%{$query}%");
            })
            ->with(['pharmacy:id,name,address,city,latitude,longitude'])
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Search results retrieved',
            'data' => MedicationResource::collection($medications),
            'pagination' => [
                'current_page' => $medications->currentPage(),
                'last_page' => $medications->lastPage(),
                'per_page' => $medications->perPage(),
                'total' => $medications->total(),
            ],
            'errors' => [],
        ]);
    }

    public function nearbyPharmacies()
    {
        $latitude = request('lat');
        $longitude = request('lng');
        $radius = request('radius', 10);

        if (!$latitude || !$longitude) {
            return response()->json([
                'success' => false,
                'message' => 'Latitude and longitude are required',
                'data' => null,
                'errors' => ['location' => ['Please provide lat and lng parameters']],
            ], 400);
        }

        $pharmacies = Pharmacy::verified()
            ->nearby($latitude, $longitude, $radius)
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Nearby pharmacies retrieved',
            'data' => PharmacyResource::collection($pharmacies),
            'pagination' => [
                'current_page' => $pharmacies->currentPage(),
                'last_page' => $pharmacies->lastPage(),
                'per_page' => $pharmacies->perPage(),
                'total' => $pharmacies->total(),
            ],
            'errors' => [],
        ]);
    }

    public function myReservations()
    {
        $user = Auth::user();

        $query = $user->reservations()
            ->with(['medication', 'pharmacy:id,name,address'])
            ->latest();

        if (request()->has('status')) {
            $query->where('status', request('status'));
        }

        $reservations = $query->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Reservations retrieved',
            'data' => $reservations->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'medication' => $reservation->medication->name,
                    'pharmacy' => $reservation->pharmacy->name,
                    'quantity' => $reservation->quantity,
                    'status' => $reservation->status,
                    'deposit_amount' => $reservation->deposit_amount,
                    'expires_at' => $reservation->expires_at,
                    'created_at' => $reservation->created_at,
                    'is_expired' => $reservation->isExpired(),
                ];
            }),
            'pagination' => [
                'current_page' => $reservations->currentPage(),
                'last_page' => $reservations->lastPage(),
                'per_page' => $reservations->perPage(),
                'total' => $reservations->total(),
            ],
            'errors' => [],
        ]);
    }

    public function reservationSummary()
    {
        $user = Auth::user();

        $summary = [
            'total' => $user->reservations()->count(),
            'pending' => $user->reservations()->where('status', 'PENDING')->count(),
            'confirmed' => $user->reservations()->where('status', 'CONFIRMED')->count(),
            'completed' => $user->reservations()->where('status', 'COMPLETED')->count(),
            'cancelled' => $user->reservations()->where('status', 'CANCELLED')->count(),
            'expired' => $user->reservations()->where('status', 'EXPIRED')->count(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Reservation summary retrieved',
            'data' => $summary,
            'errors' => [],
        ]);
    }

    public function updateNotificationPreferences()
    {
        $user = Auth::user();

        $preferences = $user->notificationPreference()->firstOrCreate([]);

        $validated = request()->validate([
            'email_notifications' => 'sometimes|boolean',
            'in_app_notifications' => 'sometimes|boolean',
            'reservation_notifications' => 'sometimes|boolean',
            'order_notifications' => 'sometimes|boolean',
            'stock_notifications' => 'sometimes|boolean',
            'delivery_notifications' => 'sometimes|boolean',
            'system_notifications' => 'sometimes|boolean',
        ]);

        $preferences->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated',
            'data' => $preferences,
            'errors' => [],
        ]);
    }

    public function updatePhoto()
    {
        $user = Auth::user();

        request()->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        $path = request()->file('photo')->store('users/photos', 'public');

        $user->update(['photo' => $path]);

        return response()->json([
            'message' => 'Photo updated successfully',
            'data' => new UserResource($user),
        ]);
    }

    public function deletePhoto()
    {
        $user = Auth::user();

        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
            $user->update(['photo' => null]);
        }

        return response()->json([
            'message' => 'Photo deleted successfully',
            'data' => new UserResource($user),
        ]);
    }

    public function notificationPreferences()
    {
        $user = Auth::user();

        $preferences = $user->notificationPreference()->firstOrCreate([]);

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences retrieved',
            'data' => $preferences,
            'errors' => [],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Models\Favorite;
use App\Models\Pharmacy;
use App\Http\Controllers\Controller;
use App\Http\Resources\PharmacyResource;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $favorites = $user->favoritePharmacies()
            ->withCount('medications')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Favorites retrieved successfully',
            'data' => PharmacyResource::collection($favorites),
            'pagination' => [
                'current_page' => $favorites->currentPage(),
                'last_page' => $favorites->lastPage(),
                'per_page' => $favorites->perPage(),
                'total' => $favorites->total(),
            ],
            'errors' => [],
        ]);
    }

    public function store(int $pharmacyId)
    {
        $user = Auth::user();
        $pharmacy = Pharmacy::findOrFail($pharmacyId);

        $favorite = Favorite::firstOrCreate([
            'user_id' => $user->id,
            'pharmacy_id' => $pharmacy->id,
        ]);

        if ($favorite->wasRecentlyCreated) {
            AuditService::logCreated($favorite);
        }

        return response()->json([
            'success' => true,
            'message' => $favorite->wasRecentlyCreated ? 'Pharmacy added to favorites' : 'Already in favorites',
            'data' => new PharmacyResource($pharmacy),
            'errors' => [],
        ], $favorite->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(int $pharmacyId)
    {
        $user = Auth::user();

        $favorite = Favorite::where('user_id', $user->id)
            ->where('pharmacy_id', $pharmacyId)
            ->firstOrFail();

        $favorite->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pharmacy removed from favorites',
            'data' => null,
            'errors' => [],
        ]);
    }

    public function check(int $pharmacyId)
    {
        $user = Auth::user();

        $isFavorite = Favorite::where('user_id', $user->id)
            ->where('pharmacy_id', $pharmacyId)
            ->exists();

        return response()->json([
            'success' => true,
            'message' => 'Favorite status retrieved',
            'data' => ['is_favorite' => $isFavorite],
            'errors' => [],
        ]);
    }
}

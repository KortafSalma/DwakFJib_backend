<?php

namespace App\Http\Controllers\Api;

use App\Models\Pharmacy;
use App\Models\Review;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function index()
    {
        $query = Review::with(['user', 'pharmacy', 'reservation'])->latest();

        if (request()->has('pharmacy_id')) {
            $query->where('pharmacy_id', request('pharmacy_id'));
        }

        if (request()->has('rating')) {
            $query->byRating(request('rating'));
        }

        if (request()->has('verified')) {
            $query->verified();
        }

        return ReviewResource::collection($query->paginate(10));
    }

    public function store(StoreReviewRequest $request)
    {
        $pharmacy = Pharmacy::findOrFail($request->pharmacy_id);
        $user = Auth::user();

        $existingReview = Review::where('pharmacy_id', $pharmacy->id)
            ->where('user_id', $user->id)
            ->whereNull('reservation_id')
            ->first();

        if ($existingReview) {
            return response()->json([
                'message' => 'You have already reviewed this pharmacy'
            ], 409);
        }

        $isVerifiedPurchase = false;

        if ($request->reservation_id) {
            $reservation = $user->reservations()
                ->where('id', $request->reservation_id)
                ->where('pharmacy_id', $pharmacy->id)
                ->whereIn('status', ['PAID', 'COMPLETED'])
                ->first();

            if ($reservation) {
                $isVerifiedPurchase = true;
            }
        }

        $review = DB::transaction(function () use ($request, $pharmacy, $user, $isVerifiedPurchase) {
            return Review::create([
                'pharmacy_id' => $pharmacy->id,
                'user_id' => $user->id,
                'reservation_id' => $request->reservation_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'is_verified_purchase' => $isVerifiedPurchase,
            ]);
        });

        $this->updatePharmacyRating($pharmacy);

        AuditService::logCreated($review);

        NotificationService::sendToUser(
            $pharmacy->user,
            'New Review',
            "{$user->name} rated {$pharmacy->name} {$review->rating}/5 stars.",
            'ALERT',
            ['review_id' => $review->id, 'pharmacy_id' => $pharmacy->id]
        );

        return response()->json([
            'message' => 'Review submitted successfully',
            'data' => new ReviewResource($review->load(['user', 'pharmacy']))
        ], 201);
    }

    public function show(Review $review)
    {
        return new ReviewResource(
            $review->load(['user', 'pharmacy', 'reservation'])
        );
    }

    public function update(UpdateReviewRequest $request, Review $review)
    {
        $this->authorize('update', $review);

        $oldValues = $review->toArray();

        $review->update($request->validated());

        $this->updatePharmacyRating($review->pharmacy);

        AuditService::logUpdated($review, $oldValues, $review->toArray());

        return response()->json([
            'message' => 'Review updated successfully',
            'data' => new ReviewResource($review)
        ]);
    }

    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);

        $pharmacy = $review->pharmacy;

        AuditService::logDeleted($review);

        $review->delete();

        $this->updatePharmacyRating($pharmacy);

        return response()->json([
            'message' => 'Review deleted successfully'
        ]);
    }

    public function pharmacyReviews(Pharmacy $pharmacy)
    {
        $reviews = $pharmacy->reviews()
            ->with('user')
            ->latest()
            ->paginate(10);

        $ratingDistribution = $pharmacy->reviews()
            ->selectRaw('rating, count(*) as count')
            ->groupBy('rating')
            ->orderByDesc('rating')
            ->get()
            ->pluck('count', 'rating');

        return response()->json([
            'data' => ReviewResource::collection($reviews),
            'summary' => [
                'average_rating' => $pharmacy->rating,
                'total_reviews' => $pharmacy->total_reviews,
                'rating_distribution' => $ratingDistribution,
            ],
        ]);
    }

    protected function updatePharmacyRating(Pharmacy $pharmacy)
    {
        $stats = $pharmacy->reviews()
            ->selectRaw('count(*) as total, avg(rating) as avg_rating')
            ->first();

        $pharmacy->update([
            'total_reviews' => $stats->total,
            'rating' => $stats->avg_rating ?? 0,
        ]);
    }
}

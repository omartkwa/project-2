<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Rating;

use App\Models\Booking;
use Illuminate\Http\Request;

class RatingController extends Controller
{
public function addRating(Request $request, $apartmentId)
{
    $user = $request->user();

    $request->validate([
        'rating' => 'required|integer|min:1|max:5',
        'comment' => 'nullable|string|max:500',
    ]);

    // التحقق أن المستأجر قام بالحجز من قبل للشقة (approved)
    $hasBooking = Booking::where('user_id', $user->id)
        ->where('apartment_id', $apartmentId)
        ->where('status', 'approved')
        ->exists();

    if (! $hasBooking) {
        return response()->json([
            'message' => 'You can only rate an apartment you have booked.'
        ], 403);
    }

    // منع تكرار التقييم لنفس الشقة
    $rating = Rating::updateOrCreate(
        [
            'user_id' => $user->id,
            'apartment_id' => $apartmentId,
        ],
        [
            'rating' => $request->rating,
            'comment' => $request->comment
        ]
    );

    return response()->json([
        'message' => 'Rating saved successfully.',
        'data' => $rating
    ]);
}
public function getApartmentRatings(Request $request, $apartmentId)
{
    // التحقق من وجود الشقة
    $apartment = Apartment::find($apartmentId);

    if (! $apartment) {
        return response()->json([
            'message' => 'Apartment not found.'
        ], 404);
    }

    $perPage = $request->input('per_page', 10);

    // حساب المتوسط وعدد التقييمات لجميع التقييمات
    $allRatings = Rating::where('apartment_id', $apartmentId);
    $averageRating = round($allRatings->avg('rating'), 2);
    $totalRatings = $allRatings->count();

    // جلب التقييمات للصفحة الحالية مع اسم المستأجر
    $ratingsPaginated = Rating::with('user:id,first_name,last_name')
        ->where('apartment_id', $apartmentId)
        ->orderBy('created_at', 'desc')
        ->simplePaginate($perPage);

    // تجهيز البيانات للـ JSON
    $ratingsData = $ratingsPaginated->items();
    $ratingsData = collect($ratingsData)->map(function($r) {
        return [
            'user_id' => $r->user->id,
            'user_name' => $r->user->first_name . ' ' . $r->user->last_name,
            'rating' => $r->rating,
            'comment' => $r->comment,
            'created_at' => $r->created_at->toDateTimeString()
        ];
    });

    return response()->json([
        'apartment_id' => $apartmentId,
        'average_rating' => $averageRating,
        'total_ratings' => $totalRatings,
        'ratings' => $ratingsData,
        'next_page_url' => $ratingsPaginated->nextPageUrl(),
        'prev_page_url' => $ratingsPaginated->previousPageUrl(),
    ]);
}
public  function updateRating(Request $request, $apartmentId)
{
    $user = $request->user();

    $request->validate([
        'rating' => 'sometimes|integer|min:1|max:5',
        'comment' => 'sometimes|string|max:500',
    ]);

    $rating = Rating::where('user_id', $user->id)
        ->where('apartment_id', $apartmentId)
        ->first();

    if (!$rating) {
        return response()->json([
            'message' => 'Rating not found or you are not authorized.'
        ], 404);
    }

    if ($request->has('rating')) {
        $rating->rating = $request->rating;
    }

    if ($request->has('comment')) {
        $rating->comment = $request->comment;
    }

    $rating->save();

    return response()->json([
        'message' => 'Rating updated successfully.',
        'data' => $rating
    ]);
}
public function deleteRating(Request $request, $apartmentId)
{
    $user = $request->user();

    $rating = Rating::where('user_id', $user->id)
        ->where('apartment_id', $apartmentId)
        ->first();

    if (!$rating) {
        return response()->json([
            'message' => 'Rating not found or you are not authorized.'
        ], 404);
    }

    $rating->delete();

    return response()->json([
        'message' => 'Rating deleted successfully.'
    ]);
}
}

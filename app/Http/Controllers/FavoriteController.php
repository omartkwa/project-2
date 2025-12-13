<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FavoriteController extends Controller
{
public function addFavorite(Request $request, $apartmentId)
{
    $user = $request->user();

    $user->favoriteApartments()->syncWithoutDetaching([$apartmentId]);

    return response()->json(['message' => 'Apartment added to favorites.']);
}

// إزالة شقة من المفضلة
public function removeFavorite(Request $request, $apartmentId)
{
    $user = $request->user();

    $user->favoriteApartments()->detach($apartmentId);

    return response()->json(['message' => 'Apartment removed from favorites.']);
}

// جلب جميع الشقق المفضلة للمستخدم
public function listFavorites(Request $request)
{
    $user = $request->user();

    $favorites = $user->favoriteApartments()->with('owner')->get();

    return response()->json([
        'message' => 'Favorite apartments fetched successfully.',
        'data' => $favorites
    ]);
}}

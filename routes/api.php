<?php
use App\Http\Controllers\ApartmentController;

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\RatingController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/admin/users/{id}/approve', [AdminUserController::class, 'approve']);
    Route::post('/admin/users/{id}/disactive', [AdminUserController::class, 'disactive']);
    Route::post('/apartments/store', [ApartmentController::class, 'store']);
     Route::get('/apartments/search', [ApartmentController::class, 'search']); 
    Route::get('/apartments', [ApartmentController::class, 'index']);
    Route::get('/apartment/{id}', [ApartmentController::class, 'show']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::get('/updateProfile', [AuthController::class, 'updateProfile']);
Route::get('/admin/users/{id}/upgrade', [AdminUserController::class, 'upgradeToLandlord']);
Route::get('/admin/pending-users', [AdminUserController::class, 'pendingUsers']);
});
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/apartments/{apartment}/book', [BookingController::class, 'store']);

    Route::post('/bookings/{booking}', [BookingController::class, 'update']);

    Route::get('/bookings/{booking}', [BookingController::class, 'cancel']);

    Route::get('/owner/bookings', [BookingController::class, 'ownerIndex']);

    Route::post('/bookings/{booking}/approve', [BookingController::class, 'approve']);
    Route::post('/bookings/{booking}/reject', [BookingController::class, 'reject']);
});
Route::middleware('auth:sanctum')->get('/user/bookings', [BookingController::class, 'userBookings']);

Route::middleware('auth')->group(function () {
    Route::post('/favorites/{apartmentId}', [FavoriteController::class, 'addFavorite']);
    Route::delete('/favorites/{apartmentId}', [FavoriteController::class, 'removeFavorite']);
    Route::get('/favorites', [FavoriteController::class, 'listFavorites']);
});
Route::middleware('auth')->group(function () {
    Route::post('/apartments/{apartmentId}/rate', [RatingController::class, 'addRating']);
});
Route::middleware('auth')->group(function () {
    Route::put('/apartments/{apartmentId}/rate', [RatingController::class, 'updateRating']);
    Route::delete('/apartments/{apartmentId}/rate', [RatingController::class, 'deleteRating']);
});
Route::middleware(['auth:sanctum', 'is_admin'])
    ->get('/admin/apartments/pending', [AdminUserController::class, 'pendingApartments']);

Route::middleware(['auth:sanctum', 'is_admin'])
    ->get('/admin/apartments/{id}/approve', [AdminUserController::class, 'approveApartment']);
Route::get('/users', function (Request $request) {
    return 'omar';
});
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

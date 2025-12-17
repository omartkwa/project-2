<?php
use App\Http\Controllers\ApartmentController;

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\RatingController;



 Route::post('/register', [AuthController::class, 'register']);
 Route::post('/login',    [AuthController::class, 'login']);

 
Route::middleware('auth:sanctum')->group(function () {
 Route::get('/admin/users/{id}/approve', [AdminUserController::class, 'approve']);
 Route::get('/admin/users/{id}/disactive', [AdminUserController::class, 'disactive']);
 Route::get('/admin/users/{id}/active', [AdminUserController::class, 'active']);
 Route::get('/admin/users/{id}/upgrade', [AdminUserController::class, 'upgradeToLandlord']);
 Route::get('/admin/pending-users', [AdminUserController::class, 'pendingUsers']);
 Route::get('/admin/{id}/showUser', [AdminUserController::class, 'showUser']);
 Route::get('/admin/apartments/pending', [AdminUserController::class, 'pendingApartments']);
 Route::get('/admin/stats', [AdminUserController::class, 'stats']);
 Route::get('/admin/apartments/{id}/approve', [AdminUserController::class, 'approveApartment']);
 Route::get('/apartments', [ApartmentController::class, 'index']);
 Route::post('/apartments/store', [ApartmentController::class, 'store']);
 Route::get('/apartment/{id}', [ApartmentController::class, 'show']);
 Route::get('/apartments/search', [ApartmentController::class, 'search']); 
 Route::post('/apartments/{apartment}/book', [BookingController::class, 'store']);
 Route::post('/bookings/{booking}', [BookingController::class, 'update']);
 Route::get('/owner/bookings', [BookingController::class, 'ownerIndex']);
 Route::post('/bookings/{booking}/approve', [BookingController::class, 'approve']);
 Route::get('/user/bookings', [BookingController::class, 'userBookings']);
 Route::get('/bookings/{booking}', [BookingController::class, 'cancel']);
 Route::post('/bookings/{booking}/reject', [BookingController::class, 'reject']);
 Route::post('/apartments/{apartmentId}/rate/add', [RatingController::class, 'addRating']);
 Route::post('/apartments/{apartmentId}/rate/update', [RatingController::class, 'updateRating']);
 Route::get('/apartments/{apartmentId}/rate/delete', [RatingController::class, 'deleteRating']);
 Route::get('/logout', [AuthController::class, 'logout']);
 Route::get('/profile', [AuthController::class, 'profile']);
 Route::get('/updateProfile', [AuthController::class, 'updateProfile']);
 Route::get('/favorites/add/{apartmentId}', [FavoriteController::class, 'addToFavorite']);
 Route::get('/favorites/remove/{apartmentId}', [FavoriteController::class, 'removeFromFavorite']);
 Route::get('/favorites', [FavoriteController::class, 'myFavorites']);
 Route::get('/user/upgradeToLandlord',[AuthController::class,'upgradeToLandlord']);
 Route::post('/user/changePassword',[AuthController::class,'changePassword']);
 Route::get('/user/verified',[AuthController::class,'verified']);

});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

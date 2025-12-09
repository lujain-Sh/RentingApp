<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\ApartmentRentalsController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/apartments/{apartment_id}/rentals',[ApartmentRentalsController::class,'createRental'])->middleware('auth:sanctum');
Route::get('/user/rentals',[ApartmentRentalsController::class,'getUserRentals'])->middleware('auth:sanctum');
Route::put('/user/rentals/{rental_id}/cancel',[ApartmentRentalsController::class,'cancelRental'])->middleware('auth:sanctum');
Route::put('/user/rentals/{rental_id}/update',[ApartmentRentalsController::class,'updateRental'])->middleware('auth:sanctum');

Route::prefix('/user')->group(function()
{
    Route::post('/register',[UserController::class,'register']);
    Route::post('/login',[UserController::class,'login']);
    Route::post('/logout',[UserController::class,'logout'])->middleware('auth:sanctum');
});


Route::prefix('/admin')->group(function()
{
    Route::get('/users',[AdminController::class,'index']);
    Route::get('/users/{id}',[AdminController::class,'show']);
    Route::get('/pending_users',[AdminController::class,'pending_users']);
    Route::get('/approved_users',[AdminController::class,'approved_users']);

    Route::put('/users/{id}/approve',[AdminController::class,'approveAccount']);
    Route::put('/users/{id}/reject',[AdminController::class,'rejectAccount']);

});

Route::prefix('/apartments')->group(function()
{
    Route::post('/create',[ApartmentController::class,'create_apartment'])->middleware('auth:sanctum');
    Route::get('/', [ApartmentController::class, 'index']);
    Route::get('/{id}', [ApartmentController::class, 'show']);
    // Route::get('/filter/governorate', [ApartmentController::class, 'filterByGovernorate']);
    // Route::get('/filter/city', [ApartmentController::class, 'filterByCity']);
    // Route::get('/filter/price-range', [ApartmentController::class, 'filterByPriceRange']);
    // Route::get('/filter/bedrooms', [ApartmentController::class, 'filterByBedrooms']);
    // Route::get('/filter/bathrooms', [ApartmentController::class, 'filterByBathrooms']);
    // Route::get('/filter/area', [ApartmentController::class, 'filterByArea']);
    // Route::get('/filter/balcony', [ApartmentController::class, 'filterByBalcony']);
});


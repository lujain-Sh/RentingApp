<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\ApartmentRatingController;
use App\Http\Controllers\ApartmentRentalController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('/user')->group(function()
{
    Route::put('/rentals/{rental_id}/update',[ApartmentRentalController::class,'updateRental'])->middleware('auth:sanctum');
    Route::put('/rentals/{rental_id}/approve',[ApartmentRentalController::class,'approveRental'])->middleware('auth:sanctum');
    Route::put('/rentals/{rental_id}/reject',[ApartmentRentalController::class,'rejectRental'])->middleware('auth:sanctum');
    Route::put('/rentals/{rental_id}/cancel',[ApartmentRentalController::class,'cancelRental'])->middleware('auth:sanctum');

    Route::get('/rentals',[ApartmentRentalController::class,'getUserRentals'])->middleware('auth:sanctum');
    Route::get('/my_past_rentals',[ApartmentRentalController::class,'getUserPastRentals'])->middleware('auth:sanctum');
    Route::get('/my_ongoing_rentals',[ApartmentRentalController::class,'getUserOngoingRentals'])->middleware('auth:sanctum');

    Route::post('/register',[UserController::class,'register']);
    Route::post('/login',[UserController::class,'login']);
    Route::post('/logout',[UserController::class,'logout'])->middleware('auth:sanctum');
    // Route::get('/check-approve',[UserController::class,'checkApprove']);  
});


Route::prefix('/apartments')->group(function()
{

    Route::get('/{id}', [ApartmentController::class, 'show']);
    Route::get('/', [ApartmentController::class, 'myApartments'])->middleware('auth:sanctum');

    Route::post('/create',[ApartmentController::class,'create_apartment'])->middleware('auth:sanctum');
    Route::get('/filter', [ApartmentController::class, 'filterApartment']);

    Route::post('/{apartment_id}/rentals',[ApartmentRentalController::class,'createRental'])->middleware('auth:sanctum');

    Route::get('/{id}/ratings', [ApartmentRatingController::class, 'listByApartment']);
    Route::post('/rentals/{rental_id}/ratings', [ApartmentRatingController::class, 'createRating'])->middleware('auth:sanctum');

    Route::post('/{update_request_id}/rentals/approve',[ApartmentRentalController::class,'approveRentalUpdate'])->middleware('auth:sanctum');
    Route::post('/{update_request_id}/rentals/reject',[ApartmentRentalController::class,'rejectRentalUpdate'])->middleware('auth:sanctum');
});

Route::prefix('governorates')->group(function() {
    Route::get('/{governorate}/cities', [CityController::class, 'byGovernorate']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/apartments/{id}/favorite', [FavoriteController::class, 'toggle']);
    Route::get('/favorites', [FavoriteController::class, 'index']);
});
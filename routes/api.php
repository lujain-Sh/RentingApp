<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\ApartmentRatingController;
use App\Http\Controllers\ApartmentRentalController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\RentalUpdateController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('/user')->group(function()
{
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
    Route::post('/{apartment_id}/rate', [ApartmentRatingController::class, 'createRating'])->middleware('auth:sanctum');
    Route::get('/{apartment_id}/can-rate', [ApartmentRatingController::class, 'canRate'])->middleware('auth:sanctum');
});


Route::middleware('auth:sanctum')->group(function () 
{
    Route::prefix('rental-update-requests')->group(function () 
    {
        Route::put('/{rental_id}/update',[RentalUpdateController::class,'updateRental'])->middleware('auth:sanctum');
        // lists
        Route::get('/mine', [RentalUpdateController::class, 'getUserRentalUpdateRequests']);
        Route::get('/incoming', [RentalUpdateController::class, 'incomingRentalUpdateRequests']);
        // actions on a request
        Route::put('/{request}/approve', [RentalUpdateController::class, 'approveRentalUpdate']);
        Route::put('/{request}/reject', [RentalUpdateController::class, 'rejectRentalUpdate']);
        Route::put('/{request}/cancel', [RentalUpdateController::class, 'cancelRentalUpdate']);
    });

});


Route::prefix('governorates')->group(function() {
    Route::get('/{governorate}/cities', [CityController::class, 'byGovernorate']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/apartments/{id}/favorite', [FavoriteController::class, 'toggle']);
    Route::get('/favorites', [FavoriteController::class, 'index']);
});
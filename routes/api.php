<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\ApartmentRentalController;
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
    Route::post('/register',[UserController::class,'register']);
    Route::post('/login',[UserController::class,'login']);
    Route::post('/logout',[UserController::class,'logout'])->middleware('auth:sanctum');
    Route::get('/check-approve',[UserController::class,'checkApprove']);  
});


Route::prefix('/apartments')->group(function()
{
    Route::post('/{apartment_id}/rentals',[ApartmentRentalController::class,'createRental'])->middleware('auth:sanctum');
    Route::post('/create',[ApartmentController::class,'create_apartment'])->middleware('auth:sanctum');
    Route::get('/', [ApartmentController::class, 'index']);
    Route::get('/filter', [ApartmentController::class, 'filterApartment']);
    Route::get('/{id}', [ApartmentController::class, 'show']);
});


<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\ApartmentRatingController;
use App\Http\Controllers\ApartmentRentalController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RentalUpdateController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

Route::post('/fcm-test', function (Request $request) {
    $request->validate(['token' => 'required|string']);

    $messaging = app('firebase.messaging');

    $message = CloudMessage::withTarget('token', $request->token)
        ->withNotification(Notification::create('Laravel ✅', 'Connected to Firebase FCM lets goooooooooooo!'));

    $messaging->send($message);

    return response()->json(['ok' => true]);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('/user')->group(function()
{
    Route::get('/rentals',[ApartmentRentalController::class,'getLandlordRentalsWithUpdateRequests'])->middleware('auth:sanctum');
    Route::put('/rentals/{rental_id}/approve',[ApartmentRentalController::class,'approveRental']); //->middleware('auth:sanctum');
    Route::put('/rentals/{rental_id}/reject',[ApartmentRentalController::class,'rejectRental']); //->middleware('auth:sanctum');
    Route::put('/rentals/{rental_id}/cancel',[ApartmentRentalController::class,'cancelRental'])->middleware('auth:sanctum');

    Route::get('/rentals',[ApartmentRentalController::class,'getUserRentals'])->middleware('auth:sanctum');
    Route::get('/my_past_rentals',[ApartmentRentalController::class,'getUserPastRentals'])->middleware('auth:sanctum');
    Route::get('/my_ongoing_rentals',[ApartmentRentalController::class,'getUserOngoingRentals'])->middleware('auth:sanctum');

    Route::post('/register',[UserController::class,'register']);
    Route::post('/login',[UserController::class,'login']);
    Route::post('/logout',[UserController::class,'logout'])->middleware('auth:sanctum');
    Route::get('/check-approve',[UserController::class,'checkApprove']);
    Route::post('/fcm-token', [UserController::class, 'storeFcmToken'])->middleware('auth:sanctum');
});


Route::prefix('/apartments')->group(function()
{
    Route::get('/filter', [ApartmentController::class, 'filterApartment']);

    Route::get('/{id}', [ApartmentController::class, 'show']);
    Route::get('/', [ApartmentController::class, 'myApartments'])->middleware('auth:sanctum');

    Route::post('/create',[ApartmentController::class,'create_apartment'])->middleware('auth:sanctum');

    Route::post('/{apartment_id}/rentals',[ApartmentRentalController::class,'createRental'])->middleware('auth:sanctum');

    Route::get('/{apartment_id}/can-rate', [ApartmentRatingController::class, 'canRate'])->middleware('auth:sanctum');
    Route::post('/{apartment_id}/rate', [ApartmentRatingController::class, 'createRating'])->middleware('auth:sanctum');
    Route::put('/{rate_id}/rate/update',[ApartmentRatingController::class,'editRating'])->middleware('auth:sanctum');
    Route::get('/{id}/ratings', [ApartmentRatingController::class, 'listByApartment']);
});


// Route::middleware('auth:sanctum')->group(function ()
// {
// Route::prefix('rental-update-requests')->middleware('auth:sanctum')->group(function ()
// {
Route::prefix('rental-update-requests')->group(function ()
{
    Route::put('/{rental_id}/update',[RentalUpdateController::class,'updateRental'])->middleware('auth:sanctum');
    // lists
    Route::get('/mine', [RentalUpdateController::class, 'getUserRentalUpdateRequests']);
    Route::get('/incoming', [RentalUpdateController::class, 'incomingRentalUpdateRequests']);
    // actions on a request
    Route::put('/{update_request_id}/approve', [RentalUpdateController::class, 'approveRentalUpdate']);
    Route::put('/{request}/reject', [RentalUpdateController::class, 'rejectRentalUpdate']);
    Route::put('/{request}/cancel', [RentalUpdateController::class, 'cancelRentalUpdate'])->middleware('auth:sanctum');
});
// });

Route::prefix('notifications')->middleware('auth:sanctum')->group(function ()
{
    Route::get('/', [NotificationController::class, 'index']);
    Route::put('/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
});


Route::prefix('governorates')->group(function() {
    Route::get('/{governorate}/cities', [CityController::class, 'byGovernorate']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/apartments/{id}/favorite', [FavoriteController::class, 'toggle']);
    Route::get('/favorites', [FavoriteController::class, 'index']);
});


// logout with fcm delete
// removed middleware for approve and reject for now
// changed the notification service to use Kreait for now
// downloaded some librabies + linked firebase
// added a method to store fcm token + fixed fcm field migrate
// made the getUserOngoingRentals and getUserPastRentals return the apartment in the response

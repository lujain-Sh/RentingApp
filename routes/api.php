<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


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


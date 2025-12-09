<?php

namespace App\Http\Controllers;

use App\Http\Requests\createRentalRequest;
use App\Models\ApartmentRentals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApartmentRentalsController extends Controller
{
    //rental logic here
    // e.g., create rental, view rentals user id, edit rental ,remove retal.
    //
    public function createRental(createRentalRequest $request)
    {
        $user_id=Auth::user()->id;
        $validatedData=$request->validated();
        $validatedData['user_id']=$user_id;
        if(ApartmentRentals::where('apartment_id',$validatedData['apartment_id'],'is_canceled'===false)
            ->where('is_canceled', false)
            ->where(function($query) use ($validatedData){
                $query->whereBetween('rental_start_date',[$validatedData['rental_start_date'],$validatedData['rental_end_date']])
                      ->orWhereBetween('rental_end_date',[$validatedData['rental_start_date'],$validatedData['rental_end_date']])
                      ->orWhere(function($q) use ($validatedData){
                          $q->where('rental_start_date','<=',$validatedData['rental_start_date'])
                            ->where('rental_end_date','>=',$validatedData['rental_end_date']);
                      });
            })->exists()){
                return response()->json([
                    'message'=>'apartment is already rented for the selected dates',
                ],422);
            }
        $rental=ApartmentRentals::create($validatedData);
        return response()->json([
            'message'=>'rental created successfully',
            'rental_id'=>$rental->id,
        ],201);
    }
    public function getUserRentals()
    {
        $user_id=Auth::user()->id;
        $rentals=ApartmentRentals::where('user_id',$user_id)->with('apartment')->get();
        return response()->json([
            'rentals'=>$rentals,
        ],200);
    }
    public function cancelRental($rental_id)
    {
        $user_id=Auth::user()->id;
        $rental=ApartmentRentals::where('id',$rental_id)->where('user_id',$user_id)->first();
        if(!$rental){
            return response()->json([
                'message'=>'rental not found',
            ],404);
        }
        $rental->is_canceled = true;
        $rental->save();
        return response()->json([
            'message'=>'rental cancelled successfully',
        ],200);
    }
    public function updateRental($rental_id,createRentalRequest $request)
    {
        $user_id=Auth::user()->id;
        $rental=ApartmentRentals::where('id',$rental_id)->where('user_id',$user_id)->first();
        if(!$rental){
            return response()->json([
                'message'=>'rental not found',
            ],404);
        }
        $validatedData=$request->validated();
        $rental->update($validatedData);
        return response()->json([
            'message'=>'rental updated successfully',
        ],200);
    }
}

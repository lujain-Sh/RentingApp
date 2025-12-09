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
    public function createRental(createRentalRequest $request, $apartment_id)
    {
        $user_id=Auth::user()->id;
        $validatedData=$request->validated();
        $validatedData['user_id']=$user_id;
        $validatedData['apartment_id'] = $apartment_id;
        $exists = ApartmentRentals::where('apartment_id', $apartment_id)
        ->where('is_canceled', false)
        ->where(function ($query) use ($validatedData) {
            $query->where('rental_start_date', '<=', $validatedData['rental_end_date'])
                  ->where('rental_end_date', '>=', $validatedData['rental_start_date']);
        })
        ->exists();

        if ($exists) {
            return response()->json([
            'message' => 'Apartment is already rented for the selected dates',
            ], 422);
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
        $rental = ApartmentRentals::where('id', $rental_id)
        ->where('user_id', $user_id)
        ->first();
        $validatedData=$request->validated();
        if (!$rental) {
            return response()->json([
                'message' => 'Rental not found',
            ], 404);
        }
        $apartment_id = $rental->apartment_id;
        $exists = ApartmentRentals::where('apartment_id', $apartment_id)
        ->where('id', '!=', $rental_id)
        ->where('is_canceled', false)
        ->where(function ($query) use ($validatedData) {
            $query->where('rental_start_date', '<=', $validatedData['rental_end_date'])
                  ->where('rental_end_date', '>=', $validatedData['rental_start_date']);
        })
        ->exists();

    if ($exists) {
        return response()->json([
            'message' => 'Apartment is already rented for the selected dates',
        ], 422);
    }
        $rental->update($validatedData);
        return response()->json([
            'message'=>'rental updated successfully',
        ],200);
    }
}

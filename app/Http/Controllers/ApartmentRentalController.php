<?php

namespace App\Http\Controllers;

use App\Http\Requests\createRentalRequest;
use App\Http\Requests\UpdateRentalRequest;
use App\Models\ApartmentRental;
use App\Services\RentalService;
use Illuminate\Support\Facades\Auth;

class ApartmentRentalController extends Controller
{
    protected $rentalService;
    
    public function __construct(RentalService $rentalService)
    {
        $this->rentalService = $rentalService;
    }
    
    public function createRental(CreateRentalRequest $request, $apartment_id)
    {
        $user_id=Auth::user()->id;
        $validatedData=$request->validated();
        $validatedData['user_id']=$user_id;
        $validatedData['apartment_id'] = $apartment_id;
        $startDate = $validatedData['rental_start_date'];
        $endDate = $validatedData['rental_end_date'];

        if ($this->rentalService->checkOverlapForCreate($apartment_id, $startDate, $endDate)) {
            return response()->json(['message' => 'Apartment is already rented for the selected dates'], 422);
        }
        $rental=ApartmentRental::create($validatedData);
        return response()->json(['message'=>'rental created successfully','rental_id'=>$rental->id,],201);
    }

    public function cancelRental($rental_id)
    {
        $user_id=Auth::user()->id;
        $rental= $this->getUserRental($rental_id,$user_id);
        if(!$rental){
            return response()->json(['message'=>'rental not found',],404);
        }
        if ($rental->is_canceled) {
            return response()->json(['message'=>'rental is already canceled'],422);
        }
        $rental->is_canceled = true;
        $rental->save();
        return response()->json(['message'=>'rental cancelled successfully',],200);
    }

    public function updateRental(UpdateRentalRequest $request,$rental_id)
    {
        $user_id=Auth::user()->id;
        $rental = $this->getUserRental($rental_id, $user_id);
        $validatedData=$request->validated();
        $startDate = $validatedData['rental_start_date'];
        $endDate = $validatedData['rental_end_date'];
        
        if (!$rental) {
            return response()->json(['message' => 'Rental not found',], 404);
        }
        if($rental->is_canceled){
            return response()->json(['message'=>'cannot update a canceled rental',],422);
        }
        if ($this->rentalService->areDatesSameAsCurrent($rental->id, $startDate, $endDate)) {
            return response()->json(['message' => 'Rental dates are unchanged'], 200);
        }
        if ($this->rentalService->checkOverlapForUpdate($rental->apartment_id,$startDate,$endDate,$rental->id)) {
            return response()->json(['message' => 'Apartment is already rented for the selected dates'], 422);
        }
        $rental->update($validatedData);
        return response()->json(['message'=>'rental updated successfully',],200);
    }

    private function getUserRental($rental_id): ?ApartmentRental
    {   
        $user_id = Auth::user()->id;
        return ApartmentRental::where('id', $rental_id)
            ->where('user_id', $user_id)->first();
    }

    public function getUserRentals()
    {
        $user_id = Auth::user()->id;
        $rentals = ApartmentRental::where('user_id', $user_id)->get();
        return response()->json($rentals, 200);
    }


}

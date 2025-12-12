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
        $startDate = new \DateTime($validatedData['rental_start_date']);
        $endDate = new \DateTime($validatedData['rental_end_date']);
        $numberOfDays = $endDate->diff($startDate)->days + 1;

        if ($this->rentalService->checkOverlapForCreate($apartment_id, $startDate, $endDate)) {
            return response()->json(['message' => 'Apartment is already rented for the selected dates'], 422);
        }
        $validatedData['total_rental_price'] = $this->rentalService->calculateTotalPrice($apartment_id, $numberOfDays);
        $rental=ApartmentRental::create($validatedData);
        return response()->json(['message'=>'rental created successfully','rental_id'=>$rental->id],201);
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
        $startDate = new \DateTime($validatedData['rental_start_date']);
        $endDate = new \DateTime($validatedData['rental_end_date']);
        $numberOfDays = $endDate->diff($startDate)->days + 1;
        $validatedData['total_rental_price'] = $this->rentalService->calculateTotalPrice($rental->apartment_id, $numberOfDays);
        
        if (!$rental) {
            return response()->json(['message' => 'Rental not found',], 404);
        }
        if($rental->is_canceled){
            return response()->json(['message'=>'cannot update a canceled rental',],422);
        }
        if($rental->is_admin_approved){
            return response()->json(['message'=>'cannot update an approved rental',],422);
        }
        if ($this->rentalService->areDatesSameAsCurrent($rental_id, $startDate, $endDate)) {
            return response()->json(['message' => 'Rental dates are unchanged'], 200);
        }
        if ($this->rentalService->checkOverlapForUpdate($rental->apartment_id,$startDate,$endDate,$rental_id)) {
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
        $rentals = Auth::user()->rentals;
        return response()->json($rentals, 200);
    }

    public function approveRental($rental_id)
    {
        $rental=ApartmentRental::findOrFail($rental_id);
        if($rental->is_canceled){
            return response()->json(['message'=>'cannot approve a canceled rental',],422);
        }
        $rental->update(['is_admin_approved' => true]);
        return response()->json(['message'=>'rental approved !','rental'=>$rental], 200);
        if(!$rental){
            return response()->json(['message'=>'rental not found',],404);
        }
    }

    public function rejectRental($rental_id)
    {
        $rental=ApartmentRental::findOrFail($rental_id);
        if($rental->is_canceled){
            return response()->json(['message'=>'cannot reject a canceled rental',],422);
        }
        $rental->update(['is_admin_approved' => false]);
        return response()->json(['message'=>'rental rejected !','rental'=>$rental], 200);
        if(!$rental){
            return response()->json(['message'=>'rental not found',],404);
        }
    }


}

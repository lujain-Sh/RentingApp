<?php

namespace App\Http\Controllers;

use App\Http\Requests\createRentalRequest;
use App\Http\Requests\CreateRentalUpdateRequest;
use App\Models\Apartment;
use App\Models\ApartmentRental;
use App\Models\RentalUpdateRequest;
use App\Services\RentalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApartmentRentalController extends Controller
{
    //edit update migrate
    //get landloard rentals
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
        $apartment=Apartment::find($apartment_id);
        if(!$apartment){
            return response()->json(['message'=>'apartment not found',],404);
        }
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
        if ($rental->pendingUpdateRequest) {
            $rental->pendingUpdateRequest->update(['status' => 'rejected']);
        }
        $rental->is_canceled = true;
        $rental->save();
        return response()->json(['message'=>'rental cancelled successfully',],200);
    }

    public function updateRental(CreateRentalUpdateRequest $request, $rental_id)
    {
        $rental = $this->getUserRental($rental_id);

        if (!$rental) {
            return response()->json(['message' => 'Rental not found'], 404);
        }

        if ($rental->is_canceled) {
            return response()->json(['message' => 'Cannot update a canceled rental'], 422);
        }

        $validatedData = $request->validated();
        $startDate = new \DateTime($validatedData['rental_start_date']);
        $endDate = new \DateTime($validatedData['rental_end_date']);
        
        if ($this->rentalService->areDatesSameAsCurrent($rental_id, $startDate, $endDate)) {
            return response()->json(['message' => 'Rental dates are unchanged'], 200);
        }

        if ($this->rentalService->checkOverlapForUpdate($rental->apartment_id, $startDate, $endDate, $rental_id)) {
            return response()->json(['message' => 'Apartment is already rented for the selected dates'], 422);
        }

        $numberOfDays = $endDate->diff($startDate)->days + 1;
        $validatedData['total_rental_price'] = $this->rentalService->calculateTotalPrice($rental->apartment_id, $numberOfDays);

        if ($rental->is_landlord_approved) {
            return $this->handleApprovedRentalUpdate($rental, $validatedData);
        }

        $rental->update($validatedData);
        return response()->json(['message' => 'Rental updated successfully'], 200);
    }

    private function handleApprovedRentalUpdate(ApartmentRental $rental, array $data)
    {
        if ($this->rentalService->hasPendingUpdateRequest($rental->id)) {
            return response()->json(['message' => 'A pending update request already exists'], 422);
        }

        $updateRequest = $this->rentalService->createUpdateRequest($rental, $data);

        return response()->json([
            'message' => 'Your rental is already approved. An update request has been sent to the landlord.',
            'update_request_id' => $updateRequest->id
        ], 202); 
    }

    public function approveRentalUpdate($update_request_id)
    {
        $updateRequest = RentalUpdateRequest::with('rental.apartment')->findOrFail($update_request_id);
    
        if ($updateRequest->status !== 'pending') {
            return response()->json(['message' => 'This request has already been processed'], 422);
        }
        
        if ($updateRequest->rental->apartment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        if ($updateRequest->status !== 'pending') {
            return response()->json(['message' => 'This request has already been processed'], 422);
        }

        $rental = $this->rentalService->applyUpdateRequest($updateRequest);

        return response()->json([
            'message' => 'Rental update approved and applied',
            'rental' => $rental
        ], 200);
    }

    public function rejectRentalUpdate(Request $request, $update_request_id)
    {
        $updateRequest = RentalUpdateRequest::with('rental.apartment')->findOrFail($update_request_id);

        if ($updateRequest->status !== 'pending') {
            return response()->json(['message' => 'This request has already been processed'], 422);
        }
    
        if ($updateRequest->rental->apartment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized. Only the landlord can reject updates.'], 403);
        }
        
        $reason = $request->input('rejection_reason');
        $this->rentalService->rejectUpdateRequest($updateRequest, $reason);

        return response()->json(['message' => 'Rental update rejected'], 200);
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
        if(!$rental){
            return response()->json(['message'=>'rental not found',],404);
        }
        if($rental->is_canceled){
            return response()->json(['message'=>'cannot approve a canceled rental',],422);
        }
        $rental->update(['is_landlord_approved' => true]);
        return response()->json(['message'=>'rental approved !','rental'=>$rental], 200);
    }

    public function rejectRental($rental_id)
    {
        $rental=ApartmentRental::findOrFail($rental_id);

        if(!$rental){
            return response()->json(['message'=>'rental not found',],404);
        }
        if($rental->is_canceled){
            return response()->json(['message'=>'cannot reject a canceled rental',],422);
        }
        if($rental->is_landlord_approved){
            return response()->json(['message'=>'cannot reject an approved rental',],422);
        }
        $rental->update(['is_landlord_approved' => false]);
        return response()->json(['message'=>'rental rejected !','rental'=>$rental], 200);
    }


}

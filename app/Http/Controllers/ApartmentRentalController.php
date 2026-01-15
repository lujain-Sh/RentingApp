<?php

namespace App\Http\Controllers;

use App\Http\Requests\createRentalRequest;
use App\Http\Requests\CreateRentalUpdateRequest;
use App\Models\Apartment;
use App\Models\ApartmentRental;
use App\Models\RentalUpdateRequest;
use App\Services\NotificationService;
use App\Services\RentalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApartmentRentalController extends Controller
{
    //edit update migrate
    //get landloard rentals
    protected $rentalService, $notificationService;

    public function __construct(RentalService $rentalService, NotificationService $notificationService)
    {
        $this->rentalService = $rentalService;
        $this->notificationService = $notificationService;
    }

    public function createRental(CreateRentalRequest $request, $apartment_id)
    {
        $user_id = Auth::user()->id;
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

        $exists = ApartmentRental::query()
            ->where('user_id', $user_id)
            ->where('apartment_id', $apartment_id)
            ->whereDate('rental_start_date', $startDate)
            ->whereDate('rental_end_date', $endDate)
            ->whereIn('status', ['pending','approved'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'You already have an active rental request for these dates.'
            ], 409);
        }

        $rental=ApartmentRental::create($validatedData);

        $rental->unsetRelation('apartment');
        return response()->json([
            'message'=>'rental created successfully',
            'rental_id'=>$rental->id,
            // 'rental'=>$rental,
        ],201);
    }

    public function getUserRentals()
    {
        $rentals = Auth::user()->rentals;
        return response()->json($rentals, 200);
    }

    private function getUserRental($rental_id): ?ApartmentRental
    {
        $user_id = Auth::user()->id;
        return ApartmentRental::where('id', $rental_id)
            ->where('user_id', $user_id)->first();
    }

    public function cancelRental($rental_id)
    {
        $rental= $this->getUserRental($rental_id);
        if(!$rental){
            return response()->json(['message'=>'rental not found',],404);
        }
        if ($rental->isCanceled()) {
            return response()->json(['message'=>'rental is already canceled'],422);
        }
        if ($rental->isApproved()) {
            return response()->json(['message'=>'rental is already approved !'],422);
        }
        if ($rental->isRejected()) {
            return response()->json(['message'=>'rental is already reject !'],422);
        }
        if ($rental->pendingUpdateRequest) { //????
            $rental->pendingUpdateRequest->update(['status' => 'rejected']);
        }
        $rental->update(['status' => 'canceled']);
        return response()->json(['message'=>'rental cancelled successfully',],200);
    }

    public function approveRental($rental_id)
    {
        $rental=ApartmentRental::with('apartment')->findOrFail($rental_id);

        if($rental->apartment->user_id !== Auth::id()){
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if(!$rental){
            return response()->json(['message'=>'rental not found',],404);
        }
        if($rental->isCanceled()){
            return response()->json(['message'=>'cannot approve a canceled rental',],422);
        }

        if ($this->rentalService->hasApprovedOverlap(
                    $rental->apartment_id , $rental->rental_start_date,
                    $rental->rental_end_date , $rental->id))
        {
            $rental->update(['status' => 'rejected']);
            $rental->unsetRelation('apartment');
            return response()->json([
                'message' => 'Rental auto-rejected due to date conflict', // think later how to fix conf better
                'rental' => $rental
            ], 409);
        }

        $rental->update(['status' => 'approved']);
        $rental->unsetRelation('apartment');


        $this->notificationService->sendToUser(
            $rental->user,
            'Rental Approved🎉',
            'Your rental request for apartment ID '.$rental->apartment_id.' has been approved.',
            ['rental_id' => $rental->id]
        );

        return response()->json(['message'=>'rental approved !','rental'=>$rental], 200);
    }

    public function rejectRental($rental_id)
    {
        $rental=ApartmentRental::with('apartment')->findOrFail($rental_id);

        if($rental->apartment->user_id !== Auth::id()){
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if(!$rental){
            return response()->json(['message'=>'rental not found',],404);
        }
        if($rental->isCanceled()){
            return response()->json(['message'=>'cannot reject a canceled rental',],422);
        }
        if($rental->isApproved()){
            return response()->json(['message'=>'cannot reject an approved rental',],422);
        }
        $rental->update(['status' => 'rejected']);
        $rental->unsetRelation('apartment');

        $this->notificationService->sendToUser(
            $rental->user,
            'Rental Rejected❌',
            'Your rental request for apartment ID '.$rental->apartment_id.' has been rejected.',
            ['rental_id' => $rental->id]
        );

        return response()->json(['message'=>'rental rejected !','rental'=>$rental], 200);
    }

    // future and ongoing rentals
    public function getUserOngoingRentals()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $ongoingRentals = $user->rentals()
            ->whereIn('status', ['pending','approved'])
            ->where('rental_end_date', '>=', $today)
            ->get();

        return response()->json($ongoingRentals, 200);
    }

    // past rentals , rejected or canceled rentals
    public function getUserPastRentals()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $pastRentals = $user->rentals()
            ->where(function ($query) use ($today) {
                $query->where('rental_end_date', '<', $today)
                      ->orWhereIn('status', ['rejected', 'canceled']);
            })
            ->get();

        return response()->json($pastRentals, 200);
    }


}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRentalUpdateRequest;
use App\Models\ApartmentRental;
use App\Models\RentalUpdateRequest;
use App\Services\NotificationService;
use App\Services\RentalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RentalUpdateController extends Controller
{
    protected $rentalService,$notificationService;

    public function __construct(RentalService $rentalService , NotificationService $notificationService)
    {
        $this->rentalService = $rentalService;
        $this->notificationService = $notificationService;
    }

    public function updateRental(CreateRentalUpdateRequest $request, $rental_id)
    {
        $user_id = Auth::user()->id;

        $rental = ApartmentRental::where('id', $rental_id)
            ->where('user_id', $user_id)->first();

        if (!$rental) {
            return response()->json(['message' => 'Rental not found'], 404);
        }

        if ($rental->isCanceled()) {
            return response()->json(['message' => 'Cannot update a canceled rental'], 422);
        }
        if ($rental->isRejected()) {
            return response()->json(['message' => 'Cannot update a rejected rental'], 422);
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

        if ($rental->isApproved()) {
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

        // if ($updateRequest->rental->apartment->user_id !== Auth::id()) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        $startDate = new \DateTime($updateRequest->requested_start_date);
        $endDate = new \DateTime($updateRequest->requested_end_date);
        if ($this->rentalService->checkOverlapForUpdate($updateRequest->rental->apartment_id, $startDate, $endDate, $updateRequest->rental->id)) {
            $updateRequest->update(['status' => 'rejected']);
            return response()->json(['message' => 'Apartment is already rented for the requested dates'], 422);
        }

        $rental = $this->rentalService->applyUpdateRequest($updateRequest);

        $this->notificationService->sendToUser(
            $rental->user,
            'Rental Update Request Approved ✅',
            'Your rental update request has been approved by the landlord.',
            ['rental_id' => $rental->id]
        );

        return response()->json([
            'message' => 'Rental update approved and applied',
            'rental' => $rental
        ], 200);
    }

    public function rejectRentalUpdate(Request $request, $update_request_id)
    {
        $updateRequest = RentalUpdateRequest::with('rental.apartment')->find($update_request_id);

        if(!$updateRequest){
            return response()->json(['message'=>'request not found !'],404);
        }

        if ($updateRequest->status !== 'pending') {
            return response()->json(['message' => 'This request has already been processed'], 422);
        }

        if ($updateRequest->rental->apartment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized. Only the landlord can reject updates.'], 403);
        }

        $reason = $request->input('rejection_reason');

        $this->rentalService->rejectUpdateRequest($updateRequest, $reason);
        $this->notificationService->sendToUser(
            $updateRequest->rental->user,
            'Rental Update Request Rejected ❌',
            'Your rental update request has been rejected by the landlord.',
            ['rental_id' => $updateRequest->rental->id ]// 'reason' => $reason]
        );

        return response()->json(['message' => 'Rental update rejected'], 200);
    }

    public function cancelRentalUpdate($update_request_id)
    {
        $user_id = Auth::user()->id;

        $updateRequest = RentalUpdateRequest::where('id', $update_request_id)
            ->whereHas('rental', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })->first();

        if (!$updateRequest) {
            return response()->json(['message' => 'Update request not found'], 404);
        }

        if ($updateRequest->status !== 'pending') {
            return response()->json(['message' => 'Only pending requests can be canceled'], 422);
        }

        $updateRequest->update(['status' => 'canceled']);

        return response()->json(['message' => 'Rental update request canceled successfully'], 200);
    }

    public function getUserRentalUpdateRequests()
    {
        $user_id = Auth::user()->id;

        $updateRequests = RentalUpdateRequest::whereHas('rental', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })->get();

        return response()->json($updateRequests, 200);
    }

    // get updating requests for the apt of the owner using owner id
    public function incomingRentalUpdateRequests()
    {
        $owner_id = Auth::user()->id;

        // get only the pending requests
        $updateRequests = RentalUpdateRequest::whereHas('rental.apartment', function ($query) use ($owner_id) {
                $query->where('user_id', $owner_id);
            })->where('status','pending')
            ->get();

        return response()->json($updateRequests, 200);
    }





}

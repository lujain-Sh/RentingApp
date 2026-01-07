<?php

namespace App\Services;

use App\Models\ApartmentDetail;
use App\Models\ApartmentRental;
use App\Models\RentalUpdateRequest;
use DateTime;

class RentalService
{

    public function checkOverlapForCreate(int $apartmentId, DateTime $startDate, DateTime $endDate): bool
    {
        return $this->checkOverlap($apartmentId, $startDate, $endDate);
    }

    public function checkOverlapForUpdate(int $apartmentId, DateTime $startDate, DateTime $endDate, int $rentalId): bool
    {
        return $this->checkOverlap($apartmentId, $startDate, $endDate, $rentalId);
    }

    private function checkOverlap(int $apartmentId, DateTime $startDate, DateTime $endDate, ?int $excludeRentalId = null): bool
    {
        $query = ApartmentRental::where('apartment_id', $apartmentId)
            ->where('status', 'approved');
            // ->where('is_landlord_approved', true);
        if ($excludeRentalId !== null) {
            $query->where('id', '!=', $excludeRentalId);
        }
        
        $overlapExists = $query->where(function ($query) use ($startDate, $endDate) {
            $query->where('rental_start_date', '<=', $endDate)
                  ->where('rental_end_date', '>=', $startDate);
        })->exists();
        
        return $overlapExists;
    }

    public function hasApprovedOverlap($apartmentId, $startDate, $endDate, $rentalId)
    {
        if( ApartmentRental::where('apartment_id', $apartmentId)
            ->where('id', '!=', $rentalId)
            ->where('status', 'approved')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('rental_start_date', [$startDate, $endDate])
                ->orWhereBetween('rental_end_date', [$startDate, $endDate])
                ->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->where('rental_start_date', '<=', $startDate)
                        ->where('rental_end_date', '>=', $endDate);
                });
            })
            ->exists()
        ) return true;
            
        return false;

    }
    
    public function areDatesSameAsCurrent(int $rentalId, DateTime $startDate, DateTime $endDate): bool
    {
        $rental = ApartmentRental::find($rentalId);
        
        if (!$rental) {
            return false;
        }
        $currentStartDate = new \DateTime($rental->rental_start_date);
        $currentEndDate = new \DateTime($rental->rental_end_date);
        
        return $startDate->format('Y/m/d') === $currentStartDate->format('Y/m/d') 
        && $endDate->format('Y/m/d') === $currentEndDate->format('Y/m/d');
    }

    public function calculateTotalPrice(int $apartmentId, int $numberOfDays): float
    {
        $apartmentDetail = ApartmentDetail::where('apartment_id', $apartmentId)->first();
        $dailyRate = $apartmentDetail->rent_price_per_night;
         return $dailyRate * $numberOfDays;
    }

    public function createUpdateRequest(ApartmentRental $rental, array $data): RentalUpdateRequest
    {
        if ($rental->pendingUpdateRequest) {
            throw new \Exception('A pending update request already exists.');
        }

        return RentalUpdateRequest::create([
            'apartment_rental_id' => $rental->id,
            'requested_start_date' => $data['rental_start_date'],
            'requested_end_date' => $data['rental_end_date'],
            'requested_total_price' => $data['total_rental_price'],
            'status' => 'pending',
            'current_start_date' => $rental->rental_start_date,
            'current_end_date' => $rental->rental_end_date,
        ]);
    }

    public function applyUpdateRequest(RentalUpdateRequest $updateRequest): ApartmentRental
    {
        $rental = $updateRequest->rental;
        
        $rental->update([
            'rental_start_date' => $updateRequest->requested_start_date,
            'rental_end_date' => $updateRequest->requested_end_date,
            'total_rental_price' => $updateRequest->requested_total_price,
        ]);

        $updateRequest->update(['status' => 'approved']);
        
        return $rental->fresh();
    }

    public function rejectUpdateRequest(RentalUpdateRequest $updateRequest): RentalUpdateRequest
    {
        $updateRequest->update([
            'status' => 'rejected',
        ]);
        
        return $updateRequest->fresh();
    }

    public function hasPendingUpdateRequest(int $rental_id): bool
    {
        return RentalUpdateRequest::where('apartment_rental_id', $rental_id)
            ->where('status', 'pending')
            ->exists();
    }
}
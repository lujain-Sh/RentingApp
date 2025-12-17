<?php

namespace App\Services;

use App\Models\ApartmentDetail;
use App\Models\ApartmentRental;
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
            ->where('is_canceled', false)
            ->where('is_landlord_approved', true);
        if ($excludeRentalId !== null) {
            $query->where('id', '!=', $excludeRentalId);
        }
        
        $overlapExists = $query->where(function ($query) use ($startDate, $endDate) {
            $query->where('rental_start_date', '<=', $endDate)
                  ->where('rental_end_date', '>=', $startDate);
        })->exists();
        
        return $overlapExists;
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
}
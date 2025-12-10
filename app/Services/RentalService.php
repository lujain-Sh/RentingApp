<?php

namespace App\Services;

use App\Models\ApartmentRentals;
use Carbon\Carbon;

class RentalService
{

    public function checkOverlapForCreate(int $apartmentId, string $startDate, string $endDate): bool
    {
        return $this->checkOverlap($apartmentId, $startDate, $endDate);
    }

    public function checkOverlapForUpdate(int $apartmentId, string $startDate, string $endDate, int $rentalId): bool
    {
        return $this->checkOverlap($apartmentId, $startDate, $endDate, $rentalId);
    }

    private function checkOverlap(int $apartmentId, string $startDate, string $endDate, ?int $excludeRentalId = null): bool
    {
        $query = ApartmentRentals::where('apartment_id', $apartmentId)
            ->where('is_canceled', false);
        
        if ($excludeRentalId !== null) {
            $query->where('id', '!=', $excludeRentalId);
        }
        
        $overlapExists = $query->where(function ($query) use ($startDate, $endDate) {
            $query->where('rental_start_date', '<=', $endDate)
                  ->where('rental_end_date', '>=', $startDate);
        })->exists();
        
        return $overlapExists;
    }
    
    public function areDatesSameAsCurrent(int $rentalId, string $startDate, string $endDate): bool
    {
        $rental = ApartmentRentals::find($rentalId);
        
        if (!$rental) {
            return false;
        }
        
        $newStartDate = Carbon::parse($startDate)->toDateString();
        $newEndDate = Carbon::parse($endDate)->toDateString();
        $currentStartDate = Carbon::parse($rental->rental_start_date)->toDateString();
        $currentEndDate = Carbon::parse($rental->rental_end_date)->toDateString();
        
        return $newStartDate === $currentStartDate && 
               $newEndDate === $currentEndDate;
    }
}
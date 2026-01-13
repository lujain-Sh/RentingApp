<?php

namespace App\Services;
use App\Models\ApartmentRating;
use App\Models\ApartmentRental;
use App\Models\User;

class ApartmentRatingService
{
    public function canUserRateApartment($user, int $apartmentId): bool
    {
        return $this->hasUserValidRentalForApartment($user, $apartmentId) &&
                       !$this->hasUserAlreadyRatedApartment($user, $apartmentId);
    }

    public function hasUserAlreadyRatedApartment(User $user, int $apartmentId): bool
    {
        return ApartmentRating::where('user_id', $user->id)
            ->where('apartment_id', $apartmentId)
            ->exists();
    }
    public function hasUserValidRentalForApartment(User $user, int $apartmentId): bool
    {
        return ApartmentRental::where('user_id', $user->id)
            ->where('apartment_id', $apartmentId)
            ->where('status', 'approved')
            ->whereDate('rental_end_date', '<', now())
            ->exists();
    }
}
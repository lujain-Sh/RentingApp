<?php

namespace Database\Seeders;

use App\Models\Apartment;
use App\Models\ApartmentRental;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Database\Seeder;

class RentalSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $apartments = Apartment::all();

        foreach ($users as $user) {
            $apartment = $apartments->random();
            $start_date = now()->addDays(rand(1, 10));
            $end_date = (clone $start_date)->addDays(rand(15, 30));

            ApartmentRental::create([
                'user_id' => $user->id,
                'apartment_id' => $apartment->id,
                'rental_start_date' => $start_date,
                'rental_end_date' => $end_date,
                'total_rental_price' => $apartment->details->rent_price_per_night *($end_date->diff($start_date)->days + 1),
                'card_number' => '424245690876543'.rand(1,100),
            ]);
        }
    }
}

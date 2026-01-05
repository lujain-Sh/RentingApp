<?php

namespace Database\Seeders;

use App\Models\Apartment;
use App\Models\ApartmentRating;
use App\Models\ApartmentRental;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only rentals that are finished
        // $rentals = Rental::where('end_date', '<', now())
        //     ->whereNotNull('user_id')
        //     ->get();
        $rentals = ApartmentRental::all();

        foreach ($rentals as $rental) {

            // Prevent duplicate rating
            $alreadyRated = ApartmentRating::where('user_id', $rental->user_id)
                ->where('apartment_id', $rental->apartment_id)
                ->exists();

            if ($alreadyRated) {
                continue;
            }

            ApartmentRating::create([
                'user_id' => $rental->user_id,
                'apartment_id' => $rental->apartment_id,
                'apartment_rental_id' => $rental->id,
                'rating' => rand(3, 5), // realistic ratings
                'comment' => fake()->optional()->sentence(10),
            ]);
        }
    }
}

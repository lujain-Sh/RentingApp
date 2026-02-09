<?php

namespace Database\Seeders;

use App\Models\Apartment;
use App\Models\ApartmentRating;
use App\Models\ApartmentRental;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker; // for render

class RatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $rentals = ApartmentRental::all();
        // Only finished rentals
        // where('rental_end_date', '<', now())
        //     ->whereNotNull('user_id')
        //     ->get();

        foreach ($rentals as $rental) {

            // User already rated this apartment → skip
            $alreadyRated = ApartmentRating::where('user_id', $rental->user_id)
                ->where('apartment_id', $rental->apartment_id)
                ->exists();

            if ($alreadyRated) {
                continue;
            }

            ApartmentRating::create([
                'user_id' => $rental->user_id,
                'apartment_id' => $rental->apartment_id,
                'rating' => rand(1, 5),
                // 'comment' => fake()->optional()->sentence(10),
                'comment' => $faker->optional()->sentence(10),
            ]);
        }
    }
}

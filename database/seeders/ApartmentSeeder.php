<?php

namespace Database\Seeders;

use App\Models\Apartment;
use App\Models\Address;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Seeder;

class ApartmentSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $cities = City::all();

        foreach ($users->take(5) as $user) {

            $city = $cities->random();

            $address = Address::create([
                'governorate' => $city->governorate,
                'city_id' => $city->id,
                'street' => 'Main Street',
                'building_number' => rand(1, 50),
                'floor' => rand(1, 5),
                'apartment_number' => rand(1, 20),
            ]);

            $apartment = Apartment::create([
                'user_id' => $user->id,
                'address_id' => $address->id,
                'is_active' => true,
            ]);

            $apartment->details()->create([
                'number_of_bedrooms' => rand(1, 4),
                'number_of_bathrooms' => rand(1, 3),
                'area_sq_meters' => rand(70, 200),
                'rent_price_per_night' => rand(50, 200),
                'description_ar' => 'شقة جميلة',
                'description_en' => 'Nice apartment',
                'has_balcony' => rand(0, 1),
            ]);
        }
    }
}

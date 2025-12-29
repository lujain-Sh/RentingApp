<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Apartment;
use App\Models\ApartmentDetail;
use App\Models\ApartmentRental;
use App\Models\PhoneSensitive;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // USERS

        $u1 = $this->createUser('Dana', 'Renter1', '111111111', true);
        $u2 = $this->createUser('Lina', 'Renter2', '222222222', true);
        $u3 = $this->createUser('Noor', 'Pending', '333333333', false);
        $u4 = $this->createUser('Omar', 'Landlord', '444444444', true);

        
        // APARTMENTS

        $a1 = $this->createApartment($u4, 'Amman', 'Shmeisani', 101);
        $a2 = $this->createApartment($u4, 'Amman', 'Abdoun', 202);
        $a3 = $this->createApartment($u4, 'Irbid', 'University St', 303);

        // RENTALS (SCENARIOS)

        // R1 — valid rating
        ApartmentRental::create([
            'user_id' => $u1->id,
            'apartment_id' => $a1->id,
            'rental_start_date' => Carbon::now()->subDays(10),
            'rental_end_date' => Carbon::now()->subDays(5),
            'is_landlord_approved' => true,
            'is_canceled' => false,
            'total_rental_price' => 500,
        ]);

        // R2 — future rental
        ApartmentRental::create([
            'user_id' => $u1->id,
            'apartment_id' => $a2->id,
            'rental_start_date' => Carbon::now()->addDays(5),
            'rental_end_date' => Carbon::now()->addDays(10),
            'is_landlord_approved' => true,
            'is_canceled' => false,
            'total_rental_price' => 600,
        ]);

        // R3 — not approved
        ApartmentRental::create([
            'user_id' => $u1->id,
            'apartment_id' => $a3->id,
            'rental_start_date' => Carbon::now()->subDays(10),
            'rental_end_date' => Carbon::now()->subDays(5),
            'is_landlord_approved' => false,
            'is_canceled' => false,
            'total_rental_price' => 400,
        ]);

        // R4 — canceled
        ApartmentRental::create([
            'user_id' => $u1->id,
            'apartment_id' => $a1->id,
            'rental_start_date' => Carbon::now()->subDays(8),
            'rental_end_date' => Carbon::now()->subDays(3),
            'is_landlord_approved' => true,
            'is_canceled' => true,
            'total_rental_price' => 450,
        ]);

        // R5 — other user
        ApartmentRental::create([
            'user_id' => $u2->id,
            'apartment_id' => $a1->id,
            'rental_start_date' => Carbon::now()->subDays(10),
            'rental_end_date' => Carbon::now()->subDays(5),
            'is_landlord_approved' => true,
            'is_canceled' => false,
            'total_rental_price' => 500,
        ]);
    }

    /* =====================
     * HELPERS
     * ===================== */

    private function createUser($first, $last, $phone, $approved)
    {
        $phoneId = PhoneSensitive::create([
            'country_code' => '+962',
            'phone_number' => $phone,
            'full_phone_str' => '+962' . $phone,
        ])->id;

        return User::create([
            'first_name' => $first,
            'last_name' => $last,
            'birth_date' => '2000-01-01',
            'password' => Hash::make('password123'),
            'phone_sensitive_id' => $phoneId,
            'is_active' => true,
            'is_admin_validated' => $approved,
            'legal_doc_url' => 'http://example.com/doc.pdf',
            'legal_photo_url' => 'http://example.com/photo.jpg',
        ]);
    }

    private function createApartment($owner, $city, $street, $num)
    {
        $address = Address::create([
            'governorate' => 'Jordan',
            'city' => $city,
            'street' => $street,
            'building_number' => '10',
            'floor' => 1,
            'apartment_number' => $num,
        ]);

        $apartment = Apartment::create([
            'user_id' => $owner->id,
            'address_id' => $address->id,
            'is_active' => true,
        ]);

        ApartmentDetail::create([
            'apartment_id' => $apartment->id,
            'number_of_bedrooms' => 2,
            'number_of_bathrooms' => 1,
            'area_sq_meters' => 120,
            'rent_price_per_night' => 50,
            'description_ar' => 'شقة جميلة',
            'description_en' => 'Nice apartment',
            'has_balcony' => true,
        ]);

        return $apartment;
    }
    
}

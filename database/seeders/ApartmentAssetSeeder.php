<?php

namespace Database\Seeders;

use App\Models\Apartment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ApartmentAssetSeeder extends Seeder
{
    public function run(): void
    {
        $images = ['apt1.jpg', 'apt2.jpg', 'apt3.jpg'];

        foreach (Apartment::all() as $apartment) {
            foreach (range(1, 3) as $i) {

                $img = $images[array_rand($images)];

                $path = "apartment_assets/{$apartment->id}_$i.jpg";

                Storage::disk('public')->put(
                    $path,
                    file_get_contents(database_path("seeders/assets/$img"))
                );

                $apartment->assets()->create([
                    'asset_url' => $path,
                ]);
            }
        }
    }
}

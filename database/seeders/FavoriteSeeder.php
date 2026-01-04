<?php

namespace Database\Seeders;

use App\Models\Apartment;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $apartments = Apartment::all();

        foreach (User::all() as $user) {
            $user->favorites()->syncWithoutDetaching(
                $apartments->random(2)->pluck('id')
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\PhoneSensitive;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // $avatars = ['avatar1.jpg', 'avatar2.jpg'];
        $idDocs  = ['legal_doc.png'];
        $faces   = ['personal_photo.jpg'];

        for ($i = 0; $i < 9; $i++) {

            $phoneId = PhoneSensitive::getOrCreate('+963', '88800000' . $i);

            $idDoc  = $idDocs[array_rand($idDocs)];
            $face   = $faces[array_rand($faces)];

            Storage::disk('public')->put(
                "users/id_docs/$i.jpg",
                file_get_contents(database_path("seeders/assets/$idDoc"))
            );

            Storage::disk('public')->put(
                "users/id_photos/$i.jpg",
                file_get_contents(database_path("seeders/assets/$face"))
            );

            User::create([
                'first_name' => "User$i",
                'last_name' => 'Test',
                'password' => Hash::make('password'),
                'phone_sensitive_id' => $phoneId,
                'legal_doc_url' => "users/id_docs/$i.jpg",
                'legal_photo_url' => "users/id_photos/$i.jpg",
                'birth_date' => '2000-01-01',
                'is_active' => true,
                'is_admin_validated' => true,
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Governorate;
use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [

            Governorate::DAMASCUS->value => [
                'Damascus','Mazzeh','Baramkeh','Kafr Sousa','Mezzeh 86','Malki','Rukn al-Din',
            ],

            Governorate::RIF_DAMASCUS->value => [
                'Douma','Jaramana','Qudsaya','Yabroud','Al-Tall','Darayya','Zabadani',
            ],

            Governorate::ALEPPO->value => [
                'Aleppo','Azaz','Manbij','Al-Bab','Afrin','Jarabulus',
            ],

            Governorate::HOMS->value => [
                'Homs','Al-Qusayr','Palmyra','Talbiseh','Al-Rastan',
            ],

            Governorate::HAMA->value => [
                'Hama','Mhardeh','Salamiyah','Kafr Zita','Suran',
            ],

            Governorate::LATakia->value => [
                'Latakia','Jableh','Qardaha','Kasab',
            ],

            Governorate::TARTOUS->value => [
                'Tartous','Baniyas','Safita','Duraykish',
            ],

            Governorate::IDLIB->value => [
                'Idlib','Ariha','Saraqib','Maarrat al-Numan','Jisr al-Shughur',
            ],

            Governorate::DARAA->value => [
                'Daraa','Bosra','Nawa','Al-Sanamayn',
            ],

            Governorate::SUWAYDA->value => [
                'As-Suwayda','Shahba','Salkhad',
            ],

            Governorate::QUNEITRA->value => [
                'Quneitra','Khan Arnabah',
            ],

            Governorate::DEIR_EZZOR->value => [
                'Deir ez-Zor','Al-Mayadin','Al-Bukamal',
            ],

            Governorate::HASAKAH->value => [
                'Al-Hasakah','Qamishli','Ras al-Ayn','Al-Malikiyah',
            ],

            Governorate::RAQQA->value => [
                'Raqqa','Tabqa','Tell Abyad',
            ],
        ];

        foreach ($cities as $governorate => $cityNames) {
            foreach ($cityNames as $name) {
                City::create([
                    'name' => $name,
                    'governorate' => $governorate,
                ]);
            }
        }
    }
}

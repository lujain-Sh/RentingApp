<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhoneSensitive extends Model
{

    protected $fillable = [
        'country_code',
        'phone_number',
        'full_phone_str',
    ];

    
    // chat gpt says this function shouldn't be returning an id it should be returning an instance of phoneSensitive as for clarity
    // but i won't change it for now until i ask you both so you don't kill me :)
    public static function getOrCreate($country_code, $phone_number)
    {
        $phone = self::where('country_code', $country_code)
                    ->where('phone_number', $phone_number)
                    ->first();

        if($phone) return $phone->id;

        $new = self::create([
            'country_code' => $country_code,
            'phone_number' => $phone_number,
            'full_phone_str' => $country_code.$phone_number,
        ]);

        return $new->id;
    }

}

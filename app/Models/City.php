<?php

namespace App\Models;

use App\Governorate;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = [
        'name',
        'governorate',
    ];

    public static function getOrCreate($name, Governorate $governorate)
    {
        $city = self::where('name', $name)
                    ->where('governorate', $governorate->value)
                    ->first();

        if($city) return $city->id;

        $new = self::create([
            'name' => $name,
            'governorate' => $governorate->value,
        ]);

        return $new->id;
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    protected $casts = [
        'governorate' => Governorate::class,
    ];

}

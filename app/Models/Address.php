<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'addresses';

    protected $fillable = [
        'governorate',
        'city_id',
        'street',
        'building_number',
        'floor',
        'apartment_number',
    ];   

    // adding city name too
    // protected $with = ['city']; // i guess u can edit city too to only return name
    
    protected $hidden = ['updated_at', 'created_at','id']; // city_id

    
    public function apartment()
    {
        return $this->hasOne(Apartment::class, 'address_id');
    }
    public function city()
    {
        return $this->belongsTo(City::class);
    }
    
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'addresses';

    protected $fillable = [
        'governorate',
        'city',
        'street',
        'building_number',
        'floor',
        'apartment_number',
    ];   
    public function apartment()
    {
        return $this->hasOne(Apartment::class, 'address_id');
    }
    
}

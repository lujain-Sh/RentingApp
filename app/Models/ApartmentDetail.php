<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApartmentDetail extends Model
{
    protected $table = 'apartment_details';

    protected $fillable = [
        'apartment_id',
        'number_of_bedrooms',
        'number_of_bathrooms',
        'area_sq_meters',
        'rent_price_per_night',
        'description_ar',
        'description_en',
        'has_balcony',
    ];
    public function apartment()
    {
        return $this->belongsTo(Apartment::class, 'apartment_id');
    }
}

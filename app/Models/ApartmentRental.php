<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApartmentRental extends Model
{
    protected $table = 'apartment_rentals';

    protected $fillable = [
        'apartment_id',
        'user_id',
        'rental_start_date',
        'rental_end_date',
        'is_canceled',
        'is_landlord_approved',
        'total_rental_price',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class, 'apartment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rating()
    {
        return $this->hasOne(ApartmentRating::class, 'apartment_rental_id');
    }
}

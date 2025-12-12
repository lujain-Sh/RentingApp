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
        'is_admin_approved',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class, 'apartment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

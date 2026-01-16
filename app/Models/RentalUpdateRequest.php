<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalUpdateRequest extends Model
{
    protected $table = 'rental_update_requests';

    protected $fillable = [
        'apartment_rental_id',
        'requested_start_date',
        'requested_end_date',
        'current_start_date',
        'current_end_date',
        'requested_total_price',
        'status',
        // 'rejection_reason',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function rental()
    {
        return $this->belongsTo(ApartmentRental::class, 'apartment_rental_id');
    }
}

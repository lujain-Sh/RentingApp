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
        'status',
        'total_rental_price',
        'card_number'
    ];

    protected $attributes = [
        'status' => 'pending',
    ];
    protected $casts = [
        'status'=>'string',
    ];

    public function isPending()   { return $this->status === 'pending'; }
    public function isApproved()  { return $this->status === 'approved'; }
    public function isRejected()  { return $this->status === 'rejected'; }
    public function isCanceled()  { return $this->status === 'canceled'; }
    
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

     public function updateRequests()
    {
        return $this->hasMany(RentalUpdateRequest::class, 'apartment_rental_id');
    }

    public function pendingUpdateRequest()
    {
        return $this->hasOne(RentalUpdateRequest::class, 'apartment_rental_id')
                    ->where('status', 'pending');
    }
}

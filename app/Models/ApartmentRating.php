<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApartmentRating extends Model
{
    use HasFactory;

    protected $table = 'apartment_ratings';

    protected $fillable = [
        'apartment_id',
        'user_id',
        'rating',
        'comment',
    ];

    protected $hidden = [
        'user',
        'user_id',
        'apartment_rental_id',
        'apartment_id',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'user_name',
        'user_photo_url',
    ];

    public function getUserNameAttribute()
    {
        return $this->user?->first_name . ' ' . $this->user?->last_name;
    }

    public function getUserPhotoUrlAttribute()
    {
        return $this->user?->legal_photo_url;
    }

    public function user() { 
        return $this->belongsTo(User::class); 
    }
    public function rental() { 
        return $this->belongsTo(ApartmentRental::class, 'apartment_rental_id');
    }
    public function apartment() { 
        return $this->belongsTo(Apartment::class); 
    }
}

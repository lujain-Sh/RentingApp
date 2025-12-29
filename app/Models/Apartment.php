<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    protected $table = 'apartments';
    protected $fillable = [
        'user_id',
        'address_id',
        'is_active',
    ];
    protected $with = ['address', 'details', 'assets', 'ratings'];

    protected $hidden = ['user_id', 'address_id', 'updated_at', 'created_at'/*,'id'*/];

    public function details()
    {
        return $this->hasOne(ApartmentDetail::class, 'apartment_id');
    }
    public function assets()
    {
        return $this->hasMany(ApartmentAsset::class, 'apartment_id');
    }
    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function rentals()
    {
        return $this->hasMany(ApartmentRental::class, 'apartment_id'); // this key helps to specify the foreign key to 
    }
    public function ratings()
    {
        return $this->hasMany(ApartmentRating::class, 'apartment_id');
    }

    public function averageRating()
    {
        return $this->ratings()->avg('rating');
    }
    
}

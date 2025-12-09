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

    public function details()
    {
        return $this->hasOne(ApartmentDetails::class, 'apartment_id');
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
}

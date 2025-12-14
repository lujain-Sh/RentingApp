<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApartmentAsset extends Model
{
    protected $table = 'apartment_assets';

    protected $fillable = [
        'apartment_id',
        'asset_url',
    ];

    protected $hidden = ['apartment_id', 'updated_at', 'created_at','id'];
    public function apartment()
    {
        return $this->belongsTo(Apartment::class, 'apartment_id');
    }   
    
}

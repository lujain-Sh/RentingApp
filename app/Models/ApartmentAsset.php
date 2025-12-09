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
    public function apartment()
    {
        return $this->belongsTo(Apartment::class, 'apartment_id');
    }   
    
}

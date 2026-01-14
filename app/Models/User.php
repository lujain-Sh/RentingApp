<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable ,HasApiTokens;

    /**
     * The attributes that are mass assignable.
     * 
     * @var list<string>
     */

    protected $fillable = [
        'first_name',
        'last_name',
        'birth_date',
        'legal_doc_url',
        'legal_photo_url',
        'password',
        'phone_sensitive_id',

        'is_phone_number_validated',
        'is_active',
        'is_admin_validated',

        'fcm_token',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    /**
     * Get the attributes that should be cast.
    *
    * @return array<string, string>
    */
    protected function casts(): array
    {
        return [
            // it's me dana again :)
            // 'birth_date' => 'date',

            // 'is_phone_number_validated' => 'boolean',
            // 'is_active' => 'boolean',
            // 'is_admin_validated' => 'boolean',

            // 'email_verified_at' => 'datetime',
            // 'password' => 'hashed',
        ];
    }
    public function phone()
    {
        return $this->belongsTo(PhoneSensitive::class, 'phone_sensitive_id');
    }

    protected $appends = ['full_phone_str'];

    public function getFullPhoneStrAttribute()
    {
        return $this->phone->full_phone_str ?? null;
    }

    public function username()
    {
        return 'full_phone_str';
    }

    public function apartments()
    {
        return $this->hasMany(Apartment::class, 'user_id');
    }

    public function rentals()
    {
        return $this->hasMany(ApartmentRental::class, 'user_id');
    }
    public function ratings()
    {
        return $this->hasMany(ApartmentRating::class, 'user_id');
    }
    public function favorites()
    {
        return $this->belongsToMany(Apartment::class, 'favorites')
                    ->withTimestamps();
    }
    
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

}

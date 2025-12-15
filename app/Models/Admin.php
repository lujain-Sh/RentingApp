<?php

namespace App\Models;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable implements FilamentUser
{
    protected $table = 'admins';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];
     protected $hidden = ['password'];

    public function canAccessPanel(Panel $panel): bool
    {
        return true; // later you can restrict this
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\Governorate;
use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function byGovernorate($governorate)
    {
        return response()->json(
            City::where('governorate', $governorate)
                    ->orderBy('name')
                    ->pluck('name'),
            200);

    }
}
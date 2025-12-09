<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use Illuminate\Http\Request;

class ApartmentController extends Controller
{
    public function index()
    {
        $apartments=Apartment::where('is_active',1);
        return response()->json($apartments);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle($apartmentId)
    {
        $user = auth()->user();

        $user->favorites()->toggle($apartmentId);

        return response()->json([
            'message' => 'Favorite status toggled successfully.',
        ]);

    }

    public function index()
    {
        $user = auth()->user();
        $favorites = $user->favorites;

        return response()->json([
            'favorites' => $favorites->map(fn ($apartment) => [
                'apartment_id' => $apartment->id,
                'address' => $apartment->address,
                'price_per_night' => $apartment->details->rent_price_per_night,
                'rate' => round($apartment->ratings()->avg('rating'),2),
                'asset_url' => $apartment->assets->pluck('asset_url')->first(),
            ])    
        ],200);

    }
}

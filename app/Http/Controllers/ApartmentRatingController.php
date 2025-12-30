<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateApartmentRatingRequest;
use App\Models\Apartment;
use App\Models\ApartmentRating;
use App\Models\ApartmentRental;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
// use Ramsey\Uuid\Type\Integer;

class ApartmentRatingController extends Controller
{
    public function createRating(CreateApartmentRatingRequest $request, int $rental_id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }
        $user_id = $user->id;
        $data = $request->validated();

        $rental = ApartmentRental::find($rental_id);

        if (!$rental) {
            return response()->json([
                'message' => 'Rental not found'
            ], 404);
        }

        $rental_user_id = $rental->user_id;

        if ($rental_user_id != $user_id ) {
            return response()->json([
                'message' => 'You can only rate apartments you have rented',
            ], 403);
        }

        if($rental->is_canceled == true) {
            return response()->json([
                'message' => 'You cannot rate a canceled rental',
            ], 403);
        }

        if($rental->is_landlord_approved == false) {
            return response()->json([
                'message' => 'You cannot rate a rental that was not approved by the landlord',
            ], 403);
        }

        if(ApartmentRating::where('apartment_rental_id', $rental->id)->exists()) {
            return response()->json([
                'message' => 'You have already rated this rental',
            ], 409);
        }
        
        // It's me Dana check if this is the way you are using DateTime :)
        if(new \DateTime() < new \DateTime($rental->rental_end_date)) {
            return response()->json([
                'message' => 'You can only rate after the rental period has ended',
            ], 403);
        }

        // chatGPT says to do it this way :)
        // use Carbon\Carbon;

        // if (Carbon::today()->lte(Carbon::parse($rental->rental_end_date))) {
        //     return response()->json([
        //         'message' => 'You can only rate after the rental period has ended',
        //     ], 403);
        // }


        $data['user_id'] = $user_id;
        $data['apartment_id'] = $rental->apartment_id;
        $data['apartment_rental_id'] = $rental->id;

        $rating = ApartmentRating::create([
            'user_id' => $data['user_id'],
            'apartment_id' => $data['apartment_id'],
            'apartment_rental_id' => $data['apartment_rental_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        return response()->json([
            'message' => 'Rating created successfully',
            'rating' => $rating,
        ], 201);

    }

    // 
    public function listByApartment(int $apartment_id)
    {
        $apartment = Apartment::findOrFail($apartment_id);

        $ratings = $apartment->ratings()
            ->with('user:id,first_name,last_name')
            ->latest()
            ->get();
            $ratings->each(function ($rating) {
            $rating->user->makeHidden(['phone', 'full_phone_str']);
        });

        return response()->json([
            'average_rating' => round($apartment->ratings()->avg('rating'), 2),
            'ratings_count' => $ratings->count(),
            'ratings' => $ratings,
        ]);
    }
}

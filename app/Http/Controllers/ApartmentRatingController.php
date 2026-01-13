<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateApartmentRatingRequest;
use App\Models\Apartment;
use App\Models\ApartmentRating;
use App\Models\ApartmentRental;
use App\Services\ApartmentRatingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ApartmentRatingController extends Controller
{

    protected $ratingService;

    public function __construct(ApartmentRatingService $ratingService)
    {
        $this->ratingService = $ratingService;
    }


    //user can rate only if:
    //- they have rented the apartment
    //- the rental period has ended , and it's not canceled ,pending or rejected
    //- they have not already rated this rental

    public function canRate(int $apartment_id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // 1. Already rated?
        $alreadyRated = $this->ratingService->hasUserAlreadyRatedApartment($user,$apartment_id);

        if ($alreadyRated) {
            return response()->json([
                'can_rate' => false,
                'message' => 'You have already rated this apartment',
            ]);
        }

        // 2. Has a finished approved rental?
        $hasValidRental = $this->ratingService->hasUserValidRentalForApartment($user, $apartment_id);

        if (!$hasValidRental) {
            return response()->json([
                'can_rate' => false,
                'message' => 'You can rate only after completing an approved rental',
            ]);
        }

        return response()->json([
            'can_rate' => true,
            'message' => 'You can rate this apartment',
        ]);
    }


    public function createRating(CreateApartmentRatingRequest $request, int $apartment_id)
    {
        // recheck again 
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }
        if (!$this->ratingService->canUserRateApartment($user, $apartment_id)) {
            return response()->json([
                'can_rate' => false,
                'message' => 'You cannot rate this apartment',
            ]);
        }
        $user_id = $user->id;
        $data = $request->validated();

        $rating = ApartmentRating::create([
            'user_id' => $user_id,
            'apartment_id' => $apartment_id,
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
        $apartment = Apartment::find($apartment_id);

        if(!$apartment) return response()->json(['message'=>'Apartment not found'],404);

        $ratings = ApartmentRating::with('user:id,first_name,last_name,legal_photo_url')
            ->where('apartment_id', $apartment_id)
            ->latest()
            ->get();

        return response()->json([
            'average_rating' => round($apartment->ratings()->avg('rating'), 2),
            'ratings_count' => $ratings->count(),
            'ratings' => $ratings,
        ]);
    }
}

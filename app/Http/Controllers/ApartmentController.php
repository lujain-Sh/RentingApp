<?php

namespace App\Http\Controllers;

use App\Governorate;
use App\Http\Requests\CreateApartmentRequest;
use App\Http\Requests\FilterApartmentRequest;
use App\Models\Address;
use App\Models\Apartment;
use App\Models\City;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApartmentController extends Controller
{
    public function myApartments()
    {
        $user = Auth::user();
        return response()->json(Apartment::where('user_id',$user->id)->get());
    }

    public function create_apartment(CreateApartmentRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();
        
        $cityId = City::getOrCreate(
            $data['city'],
            Governorate::from($data['governorate'])
        );

        $address = Address::where('governorate', $data['governorate'])
            ->where('city_id', $cityId)
            ->where('street', $data['street'])
            ->where('building_number', $data['building_number'])
            ->where('floor', $data['floor'])
            ->where('apartment_number', $data['apartment_number'])
            ->first();

        if ($address) {
            return response()->json(['message' => 'Apartment with this address already exists'], 409);
        }
        return DB::transaction(function() use ($cityId, $data, $user) {
            $failedAssets = [];
            $address = Address::create([
                'governorate' => $data['governorate'],
                'city_id' => $cityId,
                'street' => $data['street'],
                'building_number' => $data['building_number'],
                'floor' => $data['floor'],
                'apartment_number' => $data['apartment_number'],
            ]);

            $apartment = Apartment::create([
                'user_id' => $user->id,
                'address_id' => $address->id,
            ]);

            $apartment->details()->create([
                'number_of_bedrooms' => $data['number_of_bedrooms'],
                'number_of_bathrooms' => $data['number_of_bathrooms'],
                'area_sq_meters' => $data['area_sq_meters'],
                'rent_price_per_night' => $data['rent_price_per_night'],
                'description_ar' => $data['description_ar'],
                'description_en' => $data['description_en'],
                'has_balcony' => $data['has_balcony'],
            ]);

            foreach ($data['assets'] as $asset) {
                try {
                    $path = $asset->store('apartment_assets', 'public');
                    $apartment->assets()->create([
                        'apartment_id' => $apartment->id,
                        'asset_url' => $path,
                    ]);
                } catch (\Exception $e) {
                    $failedAssets[] = $asset->getClientOriginalName();
                }
            }
            
            return response()->json([
                'message' => 'Apartment listed successfully',
                // 'apartment_id' => $apartment->id,
                // 'apartment' => Apartment::find($apartment->id),
                'failed_assets' => $failedAssets
            ], 201);
        });
    }

    public function index()
    {
        $apartments = Apartment::where('is_active',1)->get();;
        return response()->json($apartments,200);
    }

    public function show($id)
    {
        $apartment=Apartment::find($id);
        if(!$apartment || !$apartment->is_active){
            return response()->json(['message'=>'Apartment not found'],404);
        }
        return response()->json([
            'apartment'=>$apartment,
            'rate'=>round($apartment->ratings()->avg('rating'),2)
        ]);
    }

    
    public function filterApartment(FilterApartmentRequest $request)
    {
        $data = $request->validated();
        
        $query = Apartment::query()->where('is_active', 1);

        $query->when($data['governorate'] ?? null, function ($q, $governorate) {
            $q->whereHas('address', fn ($a) =>
                $a->where('governorate', $governorate)
            );
        });

        $query->when($data['city'] ?? null, function ($q, $cityName) {
            $q->whereHas('address.city', fn ($c) =>
                $c->where('name', $cityName)
            );
        });

        $query->when(
            isset($data['min_price']) || isset($data['max_price']),
            function ($q) use ($data) {
                $q->whereHas('details', fn ($d) =>
                    $d->whereBetween(
                        'rent_price_per_night',
                        [
                            $data['min_price'] ?? 0,
                            $data['max_price'] ?? PHP_INT_MAX
                        ]
                    )
                );
            }
        );

        $query->when($data['bedrooms'] ?? null, fn ($q, $v) =>
            $q->whereHas('details', fn ($d) => $d->where('number_of_bedrooms', $v))
        );

        $query->when($data['bathrooms'] ?? null, fn ($q, $v) =>
            $q->whereHas('details', fn ($d) => $d->where('number_of_bathrooms', $v))
        );

        $query->when(
            isset($data['min_area']) || isset($data['max_area']),
            function ($q) use ($data) {
                $q->whereHas('details', fn ($d) =>
                    $d->whereBetween(
                        'area_sq_meters',
                        [
                            $data['min_area'] ?? 0,
                            $data['max_area'] ?? PHP_INT_MAX
                        ]
                    )
                );
            }
        );
        
        $query->when($data['has_balcony'] ?? null, fn ($q, $v) =>
            $q->whereHas('details', fn ($d) => $d->where('has_balcony', $v))
        );

        if (isset($data['min_rating']) || isset($data['max_rating'])) {
        $min = $data['min_rating'] ?? 0;
        $max = $data['max_rating'] ?? 5;

        $ratingSub = DB::table('apartment_ratings')
            ->select('apartment_id', DB::raw('AVG(rating) as avg_rating'))
            ->groupBy('apartment_id');

        // leftJoinSub so unrated apartments get NULL avg_rating (use COALESCE if you want treat null as 0)
        $query->leftJoinSub($ratingSub, 'rating_avg', function ($join) {
            $join->on('apartments.id', '=', 'rating_avg.apartment_id');
        });

        // ensure we still select apartment columns (avoid ambiguous columns)
        $query->select('apartments.*');

        // filter using COALESCE so null -> 0 (change if you prefer to exclude unrated)
        $query->whereBetween(DB::raw('COALESCE(rating_avg.avg_rating, 0)'), [$min, $max]);
    }

    // eager load avg rating to avoid N+1 when mapping results
    $query->withAvg('ratings', 'rating')->with(['address', 'details', 'assets']);

    $apartments = $query->get();

        return response()->json(
            $apartments->map(fn ($apartment) => [
                'apartment_id' => $apartment->id,
                'address' => $apartment->address,
                'price_per_night' => $apartment->details->rent_price_per_night,
                'rate' => $apartment->ratings_avg_rating ? round($apartment->ratings_avg_rating, 2) : null,
                'assets' => $apartment->assets->pluck('asset_url')->first(),
            ])
        , 200);
    }

}

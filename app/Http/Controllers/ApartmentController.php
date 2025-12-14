<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateApartmentRequest;
use App\Http\Requests\FilterApartmentRequest;
use App\Models\Address;
use App\Models\Apartment;
use App\Models\ApartmentAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApartmentController extends Controller
{
    public function create_apartment(CreateApartmentRequest $request)
    {
        $user=Auth::user();
        $data=$request->validated();

        $address = Address::where('governorate', $data['governorate'])
            ->where('city', $data['city'])
            ->where('street', $data['street'])
            ->where('building_number', $data['building_number'])
            ->where('floor', $data['floor'])
            ->where('apartment_number', $data['apartment_number'])
            ->first();

        if ($address) {
            return response()->json(['message' => 'Apartment with this address already exists'], 409);
        }
        return DB::transaction(function() use ($data, $user) {
            $failedAssets = [];
            $address = Address::create([
                'governorate' => $data['governorate'],
                'city' => $data['city'],
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

            foreach ($data['assets'] as $url) {
                try{
                    ApartmentAsset::create([
                        'apartment_id' => $apartment->id,
                        'asset_url' => $url,
                    ]);
                }
                catch(\Exception $e){
                    $failedAssets[] = $url;
                }
            }

            return response()->json([
                'message' => 'Apartment listed successfully',
                // 'apartment_id' => $apartment->id,
                'apartment' => Apartment::find($apartment->id),
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
        return response()->json($apartment);
    }

    public function filterApartment(FilterApartmentRequest $request) 
    {
        $data = $request->validated();
        $query = Apartment::where('is_active', 1);
        if($request->has('governorate')){
            $query =  $this->filterByGovernorate($query , $data['governorate']);
        }
        if($request->has('city')){
            $query = $this->filterByCity($query , $data['city']);
        }
        if($request->has('min_price') || $request->has('max_price')){
            $query = $this->filterByPriceRange($query , $data['min_price'], $data['max_price']);
        }
        if($request->has('bedrooms')){
            $query = $this->filterByNumberOfBedrooms($query , $data['bedrooms']);
        }
        if($request->has('bathrooms')){
            $query = $this->filterByNumberOfBathrooms($query, $data['bathrooms']);
        }
        if($request->has('min_area') || $request->has('max_area')){
            $query = $this->filterByArea($query , $data['min_area'], $data['max_area']);
        }
        if($request->has('has_balcony')){
            $query = $this->filterByBalcony($query , $data['has_balcony']);
        }
        return response()->json($query->get(), 200);
    }

    
    private function filterByGovernorate($query, $governorate)
    {
        return $query->whereHas('address', function($q) use ($governorate) {
            $q->where('governorate', $governorate);
        });
    }

    private function filterByCity($query, $city)
    {
        return $query->whereHas('address', function($q) use ($city) {
            $q->where('city', $city);
        });
    }

    private function filterByPriceRange($query, $minPrice = 0, $maxPrice = PHP_INT_MAX)
    {
        if ($maxPrice === null) {
            $maxPrice = PHP_INT_MAX;
        }
        if ($minPrice === null) {
            $minPrice = 0;
        }
        
        return $query->whereHas('details', function($q) use ($minPrice, $maxPrice) {
            $q->whereBetween('rent_price_per_night', [$minPrice, $maxPrice]);
        });
    }

    private function filterByNumberOfBedrooms($query, $bedrooms)
    {
        return $query->whereHas('details', function($q) use ($bedrooms) {
            $q->where('number_of_bedrooms', $bedrooms);
        });
    }

    private function filterByNumberOfBathrooms($query, $bathrooms)
    {
        return $query->whereHas('details', function($q) use ($bathrooms) {
            $q->where('number_of_bathrooms', $bathrooms);
        });
    }

    private function filterByArea($query, $minArea = 0, $maxArea = PHP_INT_MAX)
    {
        if ($maxArea === null) {
            $maxArea = PHP_INT_MAX;
        }
        if ($minArea === null) {
            $minArea = 0;
        }
        
        return $query->whereHas('details', function($q) use ($minArea, $maxArea) {
            $q->whereBetween('area_sq_meters', [$minArea, $maxArea]);
        });
    }

    private function filterByBalcony($query, $hasBalcony)
    {
        return $query->whereHas('details', function($q) use ($hasBalcony) {
            $q->where('has_balcony', $hasBalcony);
        });
    }
}

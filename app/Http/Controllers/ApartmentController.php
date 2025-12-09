<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateApartmentRequest;
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
                'rent_price' => $data['rent_price'],
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
                'apartment_id' => $apartment->id,
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

    // public function filter(Request $request)
    // {

    // }


    // public function filterByGovernorate(Request $request)
    // {
    //     $request->validate(['governorate' => 'required|string']);
        
    //     $governorate = $request->query('governorate');
    //     $apartments=Apartment::where('is_active',1)
    //                         ->whereHas('address', function($q) use ($governorate) {
    //                             $q->where('governorate', $governorate);
    //                     })->get();

    //     return $apartments;
    // }

    // public function filterByCity(Request $request)
    // {
    //     $request->validate(['city' => 'required|string']);
        
    //     $city = $request->query('city');
    //     $apartments = Apartment::where('is_active', 1)
    //         ->whereHas('address', function($q) use ($city) {
    //             $q->where('city', $city);
    //         })->get();

    //     return response()->json($apartments);
    // }

    // public function filterByPriceRange(Request $request)
    // {
    //     $minPrice = $request->query('min_price', 0);
    //     $maxPrice = $request->query('max_price', PHP_INT_MAX);

    //     $apartments=Apartment::where('is_active', 1)
    //                         ->whereHas('details', function($q) use ($minPrice,$maxPrice) {
    //                             $q->whereBetween('rent_price', [$minPrice, $maxPrice]);
    //                         })->get();
    //     return response()->json($apartments);
    // }

    // public function filterByNumberOfBedrooms(Request $request)
    // {
    //     $request->validate(['bedrooms' => 'required|integer|min:1']);
        
    //     $bedrooms = $request->query('bedrooms');
    //     $apartments = Apartment::where('is_active', 1)
    //         ->whereHas('details', function($q) use ($bedrooms) {
    //             $q->where('number_of_bedrooms', $bedrooms);
    //         })->get();

    //     return response()->json($apartments);
    // }
    // public function filterByNumberOfBathrooms(Request $request)
    // {
    //     $request->validate(['bathrooms' => 'required|integer|min:1']);
        
    //     $bathrooms = $request->query('bathrooms');
    //     $apartments = Apartment::where('is_active', 1)
    //         ->whereHas('details', function($q) use ($bathrooms) {
    //             $q->where('number_of_bathrooms', $bathrooms);
    //         })->get();

    //     return response()->json($apartments);
    // }

    // public function filterByArea(Request $request)
    // {
    //     $minArea = $request->query('min_area', 0);
    //     $maxArea = $request->query('max_area', PHP_INT_MAX);

    //     $apartments=Apartment::where('is_active', 1)
    //                         ->whereHas('details', function($q) use ($minArea,$maxArea) {
    //                             $q->whereBetween('area_sq_meters', [$minArea, $maxArea]);
    //                         })->get();
    //     return response()->json($apartments);
    // }

    // public function filterByBalcony(Request $request)
    // {
    //     $request->validate(['has_balcony' => 'required|boolean']);
        
    //     $has_balcony = $request->query('has_balcony');
    //     $apartments = Apartment::where('is_active', 1)
    //         ->whereHas('details', function($q) use ($has_balcony) {
    //             $q->where('has_balcony', $has_balcony);
    //         })->get();

    //     return response()->json($apartments);
    // }

}

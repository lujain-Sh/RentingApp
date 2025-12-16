<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateApartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            'governorate' => 'required|string|max:50',
            'city' => 'required|string|max:50',
            'street' => 'required|string|max:100',
            'building_number' => 'required|string|min:0',
            'floor' => 'required|integer|min:0',
            'apartment_number' => 'required|string|min:0',

            'number_of_bedrooms' => 'required|integer|min:1|max:20',
            'number_of_bathrooms' => 'required|integer|min:1|max:20',
            'area_sq_meters' => 'required|numeric|min:50',
            'rent_price_per_night' => 'required|numeric|min:0',
            'description_ar' => 'required|string',
            'description_en' => 'required|string',
            'has_balcony' => 'required|boolean',

            'assets' => 'required|array|min:1', // later to put dafault pic if all failed
            // 'assets.*' => 'required|url',
            'assets.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }
}

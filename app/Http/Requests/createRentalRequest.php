<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class createRentalRequest extends FormRequest
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
            'apartment_id' => 'exists:apartments,id',
            'rental_start_date' => 'required|date|before_or_equal:rental_end_date',
            'rental_end_date' => 'required|date|after_or_equal:rental_start_date',  
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRentalRequest extends FormRequest
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
        $oneYearFromNow = now()->addYear()->toDateString();
        return [
            // 'apartment_id' => 'exists:apartments,id',
            'rental_start_date' => 'required|date|date_format:Y-m-d|after_or_equal:today|before_or_equal:' . $oneYearFromNow . '|before_or_equal:rental_end_date',
            'rental_end_date' => 'required|date|date_format:Y-m-d|after_or_equal:rental_start_date|before_or_equal:' . $oneYearFromNow,
            'total_rental_price' => 'numeric|min:0',
            'card_number' => 'required|digits_between:12,19'
        ];
    }
}

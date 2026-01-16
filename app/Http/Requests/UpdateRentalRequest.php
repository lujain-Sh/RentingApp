<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRentalRequest extends FormRequest
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
            'rental_start_date' => 'sometimes|date|date_format:Y-m-d|after_or_equal:today|before_or_equal:' . $oneYearFromNow . '|before_or_equal:rental_end_date',
            'rental_end_date' => 'sometimes|date|date_format:Y-m-d|after_or_equal:rental_start_date|before_or_equal:' . $oneYearFromNow,
        ];
    }
}

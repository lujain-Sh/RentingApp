<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class updateRental extends FormRequest
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
            'rental_start_date' => 'sometimes|date|before:rental_end_date',
            'rental_end_date' => 'sometimes|date|after:rental_start_date',
        ];
    }
}

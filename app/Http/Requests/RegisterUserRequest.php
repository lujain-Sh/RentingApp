<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class RegisterUserRequest extends FormRequest
{

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
            'first_name'      =>'required|string|max:20',
            'last_name'       =>'required|string|max:20',
            'birth_date'      =>'required|date',
            'legal_doc_url'   =>'required|url',
            'legal_photo_url' =>'required|url',
            'country_code'    =>'required|string|size:4',
            'phone_number'    =>'required|string|size:9|regex:/^[0-9]+$/',
            // 'full_phone_str'  =>'unique:phoneSensitive,full_phone_str',
            'password'        =>'required|string|min:8|confirmed',
        ];
    }
    public function messages(): array
    {

        return [
            'first_name.required' => 'First name is required.',
            'first_name.string'   => 'First name must be a string.',
            'first_name.max'      => 'First name cannot exceed 20 characters.',

            'last_name.required' => 'Last name is required.',
            'last_name.string'   => 'Last name must be a string.',
            'last_name.max'      => 'Last name cannot exceed 20 characters.',

            'birth_date.required' => 'Birth date is required.',
            'birth_date.date'     => 'Birth date must be a valid date.',

            'legal_doc_url.required' => 'legal_doc_url photo is required.',
            
            'legal_photo_url.required' => 'legal_photo_url is required.',

            'phone_number.regex' => 'Phone number must contain digits only.',

            'password.required'  => 'Password is required.',
            'password.string'    => 'Password must be a string.',
            'password.min'       => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }

}

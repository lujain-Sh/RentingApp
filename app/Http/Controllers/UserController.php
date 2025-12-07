<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Models\PhoneSensitive;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(RegisterUserRequest $request)
    {
        $data = $request->validated();
        $phone=PhoneSensitive::where('country_code', $data['country_code'])
                                ->where('phone_number', $data['phone_number'])
                                ->first();

        if($phone){
            $exists = User::where('phone_sensitive_id', $phone->id)
                        ->where('is_active', true)
                        // ->where('is_phone_number_validated', true) // what if both pending??should i comment it?
                        ->exists();
            if($exists){
                return response()->json([
                    'message'=>'phone number already in use !',
                ],422);//or 409 conflict
                    }
        }

        $phone_id  = PhoneSensitive::getOrCreate($data['country_code'],$data['phone_number']);
        

         $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'birth_date' => $data['birth_date'],
            'legal_doc_url' => $data['legal_doc_url'],
            'legal_photo_url' => $data['legal_photo_url'],
            'password' => Hash::make($data['password']),
            'phone_sensitive_id' => $phone_id,
        ]);

        // TODO: generate OTP

        return response()->json([
            'message' => 'User registered successfully',
            'user_id' => $user->id,
            'phone_id' => $phone_id,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'country_code' => 'required|string',
            'phone_number' => 'required|string',
            'password'     => 'required|string',
        ]);

        $phone = PhoneSensitive::where('country_code', $request->country_code)
                                ->where('phone_number', $request->phone_number)
                                ->first();

        if(!$phone){
            return response()->json(['message' => 'Invalid phone number'], 404);
        }

        $user = User::where('phone_sensitive_id', $phone->id)
                    ->where('is_active', true)
                    ->first();

        if(!$user){
            return response()->json(['message' => 'User not found'], 404); // User exists but NOT active 403??
        }

        if(!Hash::check($request->password, $user->password)){
            return response()->json(['message' => 'Invalid password'], 401);
        }

        if(!$user->is_admin_validated){
            return response()->json(['message' => 'Admin has not approved your account yet'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully',
            'user'    => $user,
            'token'   => $token
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message'=>'logged out successfully !'
        ],200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Models\PhoneSensitive;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(RegisterUserRequest $request)
    {
        $data = $request->validated();

        $phone = PhoneSensitive::where('country_code', $data['country_code'])
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
                ],409);
            }
        }
        $phone_id  = PhoneSensitive::getOrCreate($data['country_code'],$data['phone_number']);
        
        $request->validate([
            'legal_doc'   =>'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'legal_photo' =>'required|file|mimes:jpg,jpeg,png|max:2048',
        ]);
        if($request->hasFile('legal_doc') && $request->hasFile('legal_photo')){            
            $data['legal_doc_url'] = $request->file('legal_doc')->store('legal_docs','public');
            $data['legal_photo_url'] = $request->file('legal_photo')->store('legal_photos','public');
        }

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
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'country_code' => 'required|string|size:4',
            'phone_number' => 'required|string|size:9|regex:/^[0-9]+$/',
            'password'     => 'required|string',
        ]);

        $phone = PhoneSensitive::where('country_code', $request->country_code)
                                ->where('phone_number', $request->phone_number)
                                ->first();

        if(!$phone){
            return response()->json(['message' => 'Invalid phone number'],401);
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
            return response()->json([
                'message' => 'Admin has not approved your account yet',
                // 'token'   => $token
                ], 403);
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

    public function checkApprove(Request $request){
        $request->validate([
            'full_phone_str' => 'required|string|size:13',
        ]);
        $phone = PhoneSensitive::where('full_phone_str', $request->full_phone_str)->first();
        
        if (!$phone) {
            return response()->json([
                'message' => 'Phone number not found',
                'is_approved' => false,
            ], 404);
        }

        $user = User::where('phone_sensitive_id', $phone->id)->first();
        
        if($user->is_admin_validated){
            return response()->json([
                'message'=>'User is approved by admin.',
                'is_approved' => true,
            ],200);
        }else{
            return response()->json([
                'message'=>'User is not approved by admin yet.',
                'is_approved' => false,
            ],403);
        }
    }
}
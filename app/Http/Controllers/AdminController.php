<?php

namespace App\Http\Controllers;

use App\Models\User;

class AdminController extends Controller
{

    public function approveAccount($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_admin_validated' => 1]);
        return response()->json([
            'message'=>'account  approved !',
            'user'=>$user
        ], 200);
    }

    public function rejectAccount($id)
    {
        $user=User::findOrFail($id);
        $user->update([
            'is_active' => false,          // reject user
            'is_admin_validated' => false   
        ]);
        return response()->json([
            'message'=>'account rejected !'
        ], 200);
    }

    public function index() // all history
    {
        $users=
        // return 
        User::paginate(10);  //first 10 of users
        return response()->json($users,200);                        
    }

    public function pending_users()
    {
        $users = 
        // return 
        User::whereNull('is_admin_validated')->paginate(10); //first 10 of pending users
        return response()->json($users,200);
    }

     public function approved_users()
    {
        $users = 
        // return
         User::where('is_admin_validated', true)->paginate(10); //first 10 of approved users
        return response()->json($users,200);
    }

    public function show(string $id)
    {
       $user=User::findOrFail($id);
       return response()->json($user,200); 
    }

}

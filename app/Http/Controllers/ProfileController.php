<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function getProfile(){
        $user = Auth::user();

        return response()->json([
            "message" => "Berhasil mengambil data user",
            "data" => $user
        ]);
    }

    public function update(Request $request){
        $userAuth = Auth::user();

        $validatedData = $request->validate([
            'username' => 'string|max:255|unique',
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'profile' => 'string|max:255'
        ]);


        $user = User::find($userAuth->id);

        if(!$user){
            return response()->json(["message"=> "User tidak ditemukan"]);
        }

        $user->update($validatedData);

        return response()->json([
            "message" => "Berhasil update data user",
            "data" => $user
        ]);
    }
}

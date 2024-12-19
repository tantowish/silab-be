<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use App\Models\Lecturer;

class AuthLecturerController extends Controller
{
    public function register(Request $request)
    {   
        // Validasi input
        $request->validate(
            [
                // ends_with:ugm.ac.id
                'email' => ['required', 'email', 'unique:users,email'],
                'username' => 'required|min:1|max:255',
                'first_name' => 'required|min:1|max:255',
                'last_name' => 'required|min:1|max:255',
                'front_title' => 'min:1|max:255',
                'back_title' => 'min:1|max:255',
                'NID' => 'required|min:1|max:255',
                'phone_number' => 'required|min:1|max:255',
                'max_quota' => 'required|numeric',
                'password' => 'required'
            ],
            [
                'email' => 'Alamat email harus menggunakan domain @ugm.ac.id',
            ]
        );

        $hashedPassword  = Hash::make($request->password);

        // Register user
        $user = User::create([
            'email' => $request->email,
            'username' => $request->username,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'role' => 'dosen',
            'password' => $hashedPassword,
        ]);

        $lecturer = Lecturer::create([
            'id_user' => $user->id,
            'image_profile' => 'default.jpg',
            'full_name' => $request->first_name . ' ' . $request->last_name,
            'front_title' => $request->front_title,
            'back_title' => $request->back_title,
            'NID' => $request->NID,
            'phone_number' => $request->phone_number,
            'max_quota' => $request->max_quota,
            'isKaprodi' => 0,
        ]);

        // Response, create token
        return response()->json([
            'message' => 'Dosen berhasil didaftarkan',
            'token' => $user->createToken('user_login')->plainTextToken
        ], 201);
    }
    public function login(Request $request)
    {
        $request->validate([
            // ends_with:ugm.ac.id
            'email' => 'required|email|',
            'password' => 'required',
        ],
        [
            'email' => 'Alamat email harus menggunakan domain @ugm.ac.id',
        ]);

        $user = User::where('email', $request->email)->first();

        // Auth check
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if (!$user->email_verified_at) {
            return response()->json(['message' => "Email belum terferifikasi"]);
        }

        // Response, create token
        return response()->json([
            'message' => 'Berhasil login',
            'token' => $user->createToken('user_login')->plainTextToken
        ]);
    }

    public function logout(Request $request)
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'User berhasil logout'], 201);
    }

    public function current(Request $request)
    {
        return response()->json(Auth::user());
    }
}

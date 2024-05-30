<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validasi input
        $request->validate(
            [
                'email' => ['required', 'email', 'ends_with:mail.ugm.ac.id', 'unique:users,email'],
                'username' => 'required|min:1|max:255',
                'first_name' => 'required|min:1|max:255',
                'last_name' => 'required|min:1|max:255',
                'password' => 'required'
            ],
            [
                'email' => 'Email tidak sesuai atau sudah terdaftar sistem',
            ]
        );

        $hashedPassword  = Hash::make($request->password);

        // Register user
        $user = User::create([
            'email' => $request->email,
            'username' => $request->username,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'role' => 'mahasiswa',
            'password' => $hashedPassword,
        ]);

        // $student = Student::create([
        //     'id_user' => $user->id,
        //     'NIM' => $request->nim,
        //     'faculty' => $request->faculty,
        //     'department' => $request->department,
        //     'year' => $request->year,
        // ]);


        // Response, create token
        return response()->json([
            'message' => 'Mahasiswa berhasil mendaftar',
            'token' => $user->createToken('user_login')->plainTextToken
        ], 201);
    }
    public function login(Request $request)
    {
        $request->validate(
            [
                'email' => 'required|email|ends_with:mail.ugm.ac.id',
                'password' => 'required',
            ],
            [
                'email' => 'Alamat email harus menggunakan domain @mail.ugm.ac.id',
            ]
        );

        $user = User::where('email', $request->email)->first();

        // Auth check
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if (!$user->email_verified_at) {
            return response()->json(['message' => "Email belum terverifikasi"]);
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

    public function update(ProfileRequest $request)
    {
        $user = $request->user();
        $validatedData = $request->validated();
        $user->update($validatedData);
        $user = $user->refresh();

        $success['user'] = $user;
        $success['success'] = true;

        return response()->json($success, 200);
    }
}

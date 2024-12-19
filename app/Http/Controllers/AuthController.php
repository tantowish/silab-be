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

        // Create full photo URL
        $photoUrl = $user->photo ? asset('storage/post_img/' . $user->photo) : asset('asset/profile.webp');

        // Response, create token
        return response()->json([
            'message' => 'Berhasil login',
            'token' => $user->createToken('user_login')->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'role' => $user->role,
                'photo_url' => $photoUrl,  // Add photo URL to response
            ],
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

        if ($request->hasFile('photo')) {
            $filename = $request->file('photo')->getClientOriginalName();
            // Mendapatkan nama file tanpa ekstensi
            $getfilenamewitoutext = pathinfo($filename, PATHINFO_FILENAME);
            // Mendapatkan ekstensi file
            $getfileExtension = $request->file('photo')->getClientOriginalExtension();
            // Membuat nama file baru dengan tanggal dan waktu
            $createnewFileName = now()->format('Ymd_His') . '_' . str_replace(' ', '_', $getfilenamewitoutext) . '.' . $getfileExtension;
            // Menyimpan file di direktori yang ditentukan
            $img_path = $request->file('photo')->storeAs('public/post_img', $createnewFileName);
            // Menambahkan nama file ke data yang telah divalidasi
            $validatedData['photo'] = $createnewFileName;
        }

        $user->update($validatedData);
        $photoUrl = $user->photo ? asset('storage/post_img/' . $user->photo) : asset('asset/profile.webp');
        $user->photo_url = $photoUrl;

        return response()->json(['status' => true, 'message' => "User updated successfully", 'user' => $user], 200);
    }
}

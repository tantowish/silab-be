<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthLecturerController;
use App\Http\Controllers\EmailVerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthAdminController;
use App\Http\Controllers\LecturerController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\TagController;
use App\Models\Content;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


// Routes tanpa middleware
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/showcase', [ContentController::class, 'index']);
Route::get('/showcase/{id}', [ContentController::class, 'show']);
Route::get('/tag/{id}', [TagController::class, 'show']);
Route::get('/showcase/topic/{tags}', [ContentController::class, 'showBasedOnTopic']);
Route::get('/showcase/sorted/{based}/{tags}', [ContentController::class, 'sortedData']);
Route::get('/showcase/sorted/{based}', [ContentController::class, 'sortedAllData']);
Route::get('/search', [ContentController::class, 'search']); 
Route::get('/team', [LecturerController::class, 'index']);
Route::get('/team/{id}', [LecturerController::class, 'show']);
Route::get('/team-search', [LecturerController::class, 'search']);
Route::get('/content/{contentId}/like-count', [ContentController::class, 'countLikes']);
Route::get('/comment/{contentId}', [ContentController::class, 'showComments']);



// Routes 'auth:sanctum' middleware
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/email/verification-link', [EmailVerificationController::class, 'getVerificationLink']);
    Route::get('verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'sendVerificationEmail']);

});

// Routes 'auth:sanctum' dan 'verified' middleware
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/current', [AuthController::class, 'current']);
    ROute::get('/profile', function (Request $request) {
        return $request->user();
    });
    Route::put('/profile/update', [AuthController::class, 'update']);
    //like
    Route::post('/content/{contentId}/like', [ContentController::class, 'addLike']);
    Route::delete('/content/{contentId}/unlike', [ContentController::class, 'unLike']);
    Route::put('/content/{contentId}/toggle-like', [ContentController::class, 'toggleLike']);
    Route::get('/content/{contentId}/check-like-status', [ContentController::class, 'checkLike']);
    Route::get('/showLiked', [ContentController::class, 'showLiked']);
    Route::post('/comment/{contentId}', [ContentController::class, 'createComment']);
    Route::get('/showComments', [ContentController::class, 'showCommentsUser']);

});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/register/lecturer', [AuthLecturerController::class, 'register']);
    Route::resource('/data/lecturer', LecturerController::class);
    Route::get('/adminpage', function () {
        return response()->json(['message' => 'Admin page']);
    });
});

Route::middleware(['auth:sanctum', 'role:dosen'])->group(function () {
    Route::get('/lecturerpage', function () {
        return response()->json(['message' => 'Lecturer page']);
    });
});

Route::middleware(['auth:sanctum', 'role:mahasiswa'])->group(function () {
    Route::get('/student', function () {
        return response()->json(['message' => 'Student page']);
    });
});

Route::middleware(['auth:sanctum', 'role:mahasiswa_ta'])->group(function () {
    Route::post('/add/portofolio', [ContentController::class, 'store']);
    Route::put('/update/portofolio', [ContentController::class, 'update']);
    });

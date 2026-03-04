<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContentController;

Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);

// Protected routes example
Route::middleware('auth:sanctum')->group(function(){
    Route::get('/contents',[ContentController::class,'index']);
    Route::get('/contents/{id}',[ContentController::class,'show']);
});

// Admin routes (role-based)
Route::middleware(['auth:sanctum','role:admin'])->group(function(){
    // Laravel doesn’t manage content, but you could add admin endpoints if needed
});
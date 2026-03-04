<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            $token = $user->createToken('api')->plainTextToken;

            return response()->json(['token' => $token, 'user' => $user]);
        } catch (\Exception $e) {
        }
    }

    public function login(Request $request)
    {
        try {

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            $token = $user->createToken('api')->plainTextToken;
            return response()->json(['token' => $token, 'user' => $user]);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}

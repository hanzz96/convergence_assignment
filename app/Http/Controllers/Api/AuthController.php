<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Subscription;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            $subscription = null;

            if($request->subs) {
                // Create a default subscription that expires tomorrow
                $subscription = Subscription::create([
                    'user_id' => $user->id,
                    'plan' => 'premium',
                    'expires_at' => Carbon::tomorrow(), // expires next day
                ]);
            }
            $user->subscription = $subscription;

            $token = $user->createToken('api')->plainTextToken;
            DB::commit();
            return response()->json(['token' => $token, 'user' => $user]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
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

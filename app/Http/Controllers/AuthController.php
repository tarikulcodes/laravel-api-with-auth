<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login a user by validating credentials
     */
    public function login(LoginRequest $request)
    {
        $inputs = $request->validated();

        $user = User::where('email', $inputs['email'])->first();
        if (!$user || !Hash::check($inputs['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'auth_token' => $token,
            'user'       => new UserResource($user)
        ], 200);
    }


    /**
     * Register a new user
     */
    public function register(RegisterRequest $request)
    {
        $inputs = $request->validated();

        $user = User::create($inputs);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'auth_token' => $token,
            'user'       => new UserResource($user)
        ], 200);
    }

    /**
     * Logout a user
     */
    public function logout()
    {
        request()->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out'
        ], 200);
    }
}

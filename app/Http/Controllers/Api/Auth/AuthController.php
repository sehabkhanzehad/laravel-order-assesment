<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\SignInRequest;
use App\Http\Requests\Api\Auth\SignUpRequest;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function signUp(SignUpRequest $request): JsonResponse
    {
        User::create($request->validated());

        return $this->success('Sign up successful.', 201);
    }

    public function signIn(SignInRequest $request): JsonResponse
    {
        if (!$request->authenticate()) return $this->error('Invalid credentials.', 401);

        $token = $request->authenticatedUser()->createToken(
            'auth_token',
            expiresAt: $request->remember ? now()->addYear() : now()->addDay()
        );

        return $this->success(
            "Sign in successful.",
            201,
            [
                "user" => UserResource::make($request->authenticatedUser()),
                "token" => [
                    "accessToken" => $token->plainTextToken,
                    "type" => "Bearer",
                    "expiresAt" => $token->accessToken->expires_at,
                ],
            ]
        );
    }

    public function user(Request $request): UserResource
    {
        return UserResource::make($request->user());
    }

    public function signOut(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return $this->success("Sign out successful.");
    }
}

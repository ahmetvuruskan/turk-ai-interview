<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateUserRequest;
use App\Http\Resources\LoginResource;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\RegisterResource;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, AuthService $authService)
    {
        $user = $authService->register($request->validated());

        return (new RegisterResource($user))
            ->withMessage('general.register_success')
            ->withCode(Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request, AuthService $authService)
    {
        $credentials = $request->validated();
        $token = $authService->login($credentials['email'],$credentials['password']);

        return (new LoginResource($token))
            ->withMessage('general.login_success');
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('students');

        return new ProfileResource($user);
    }

    public function update(UpdateUserRequest $request, AuthService $authService)
    {
        $user = $authService->update($request->user(), $request->validated());

        return (new ProfileResource($user->load('students')))
            ->withMessage('general.update_success');
    }
}

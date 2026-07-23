<?php

namespace Tests\Feature;

use App\Enum\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(array $overrides = []): User
    {
        $user = new User();
        $user->name = $overrides['name'] ?? 'Veli';
        $user->surname = $overrides['surname'] ?? 'Test';
        $user->email = $overrides['email'] ?? 'veli@test.com';
        $user->password = app('hash')->make($overrides['password'] ?? 'secret123');
        $user->role = $overrides['role'] ?? RoleEnum::ROLE_USER;
        $user->save();

        return $user;
    }

    protected function authHeader(User $user): array
    {
        return ['Authorization' => 'Bearer ' . JWTAuth::fromUser($user)];
    }
}

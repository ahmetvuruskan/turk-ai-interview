<?php

namespace Tests\Feature;

class LoginTest extends ApiTestCase
{
    public function test_user_can_login_with_valid_credentials(): void
    {
        $this->makeUser([
            'email' => 'veli@test.com',
            'password' => 'secret123',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'veli@test.com',
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['code', 'message', 'data' => ['token']]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $this->makeUser([
            'email' => 'veli@test.com',
            'password' => 'secret123',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'veli@test.com',
            'password' => 'wrongpass',
        ])->assertStatus(401);
    }
}

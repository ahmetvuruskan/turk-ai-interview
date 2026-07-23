<?php

namespace Tests\Feature;

class ApiResponseFormatTest extends ApiTestCase
{
    public function test_error_returns_json_when_client_does_not_accept_json(): void
    {
        $response = $this->get('/api/v1/auth/me', ['Accept' => 'text/html']);

        $response->assertStatus(401)
            ->assertJsonStructure(['message', 'status', 'data']);

        $this->assertStringContainsString(
            'application/json',
            $response->headers->get('content-type')
        );
    }

    public function test_success_returns_json_when_client_does_not_accept_json(): void
    {
        $parent = $this->makeUser(['email' => 'veli@test.com']);

        $response = $this->get('/api/v1/auth/me', array_merge(
            ['Accept' => 'text/html'],
            $this->authHeader($parent)
        ));

        $response->assertOk()
            ->assertJsonStructure(['code', 'message', 'data'])
            ->assertJsonPath('data.email', 'veli@test.com');

        $this->assertStringContainsString(
            'application/json',
            $response->headers->get('content-type')
        );
    }
}

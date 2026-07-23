<?php

namespace Tests\Feature;

use App\Models\Student;

class ProfileTest extends ApiTestCase
{
    public function test_authenticated_parent_sees_their_children(): void
    {
        $parent = $this->makeUser(['email' => 'veli@test.com']);

        Student::factory()->create([
            'name' => 'Can',
            'surname' => 'Yilmaz',
            'parent_id' => $parent->id,
        ]);

        $response = $this->getJson('/api/v1/auth/me', $this->authHeader($parent));

        $response->assertOk()
            ->assertJsonPath('data.email', 'veli@test.com')
            ->assertJsonPath('data.students.0.name', 'Can')
            ->assertJsonCount(1, 'data.students');
    }

    public function test_guest_cannot_access_profile(): void
    {
        $this->getJson('/api/v1/auth/me')->assertStatus(401);
    }

    public function test_parent_can_update_profile(): void
    {
        $parent = $this->makeUser(['email' => 'veli@test.com']);

        $response = $this->putJson('/api/v1/auth/me', [
            'name' => 'Yeni',
        ], $this->authHeader($parent));

        $response->assertOk()
            ->assertJsonPath('data.name', 'Yeni');

        $this->assertDatabaseHas('users', [
            'id' => $parent->id,
            'name' => 'Yeni',
        ]);
    }

    public function test_update_fails_when_email_belongs_to_another_user(): void
    {
        $this->makeUser(['email' => 'taken@test.com']);
        $parent = $this->makeUser(['email' => 'veli@test.com']);

        $this->putJson('/api/v1/auth/me', [
            'email' => 'taken@test.com',
        ], $this->authHeader($parent))->assertStatus(422);
    }
}

<?php

namespace Tests\Feature;

use App\Enum\RoleEnum;
use App\Models\Student;

class StudentListTest extends ApiTestCase
{
    public function test_admin_can_list_students_with_parents(): void
    {
        $admin = $this->makeUser([
            'email' => 'admin@test.com',
            'role' => RoleEnum::ROLE_ADMIN,
        ]);

        $parent = $this->makeUser(['email' => 'parent@test.com']);

        Student::factory()->create([
            'name' => 'Can',
            'parent_id' => $parent->id,
        ]);
        Student::factory()->create(['parent_id' => null]);

        $response = $this->getJson('/api/v1/students', $this->authHeader($admin));

        $response->assertOk()
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    ['id', 'name', 'surname', 'number', 'grade', 'registration_code', 'parent'],
                ],
                'meta' => ['perPage', 'total', 'currentPage', 'lastPage'],
            ])
            ->assertJsonPath('meta.total', 2);
    }

    public function test_non_admin_cannot_list_students(): void
    {
        $user = $this->makeUser([
            'email' => 'veli@test.com',
            'role' => RoleEnum::ROLE_USER,
        ]);

        $this->getJson('/api/v1/students', $this->authHeader($user))->assertStatus(403);
    }

    public function test_guest_cannot_list_students(): void
    {
        $this->getJson('/api/v1/students')->assertStatus(401);
    }

    public function test_admin_can_assign_registration_code_to_student(): void
    {
        $admin = $this->makeUser([
            'email' => 'admin@test.com',
            'role' => RoleEnum::ROLE_ADMIN,
        ]);

        $student = Student::factory()->create([
            'registration_code' => null,
            'parent_id' => null,
        ]);

        $this->postJson('/api/v1/students/' . $student->id . '/code', [], $this->authHeader($admin))
            ->assertOk();

        $student->refresh();
        $this->assertNotNull($student->registration_code);
    }

    public function test_guest_is_unauthorized_before_model_binding(): void
    {
        $this->postJson('/api/v1/students/00000000-0000-0000-0000-000000000000/code')
            ->assertStatus(401);
    }

    public function test_assign_code_returns_clean_message_for_unknown_student(): void
    {
        $admin = $this->makeUser([
            'email' => 'admin@test.com',
            'role' => RoleEnum::ROLE_ADMIN,
        ]);

        $this->postJson('/api/v1/students/00000000-0000-0000-0000-000000000000/code', [], $this->authHeader($admin))
            ->assertStatus(404)
            ->assertJsonPath('message', __('general.not_found'));
    }
}

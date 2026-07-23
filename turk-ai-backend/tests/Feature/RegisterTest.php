<?php

namespace Tests\Feature;

use App\Events\UserCreated;
use App\Mail\ParentRegisteredMail;
use App\Models\Student;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

class RegisterTest extends ApiTestCase
{
    public function test_parent_can_register_with_valid_code(): void
    {
        Mail::fake();

        $student = Student::factory()->create([
            'registration_code' => 'ABC12345',
            'parent_id' => null,
        ]);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Ayse',
            'surname' => 'Yilmaz',
            'email' => 'ayse@test.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'registrationCode' => 'ABC12345',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.email', 'ayse@test.com');

        $this->assertDatabaseHas('users', ['email' => 'ayse@test.com']);

        $student->refresh();
        $this->assertNotNull($student->parent_id);
        $this->assertSame('ayse@test.com', $student->parent->email);

        Mail::assertSent(ParentRegisteredMail::class, function ($mail) {
            return $mail->hasTo('ayse@test.com');
        });
    }

    public function test_register_dispatches_user_created_event(): void
    {
        Event::fake([UserCreated::class]);

        Student::factory()->create([
            'registration_code' => 'EVENT123',
            'parent_id' => null,
        ]);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Ayse',
            'surname' => 'Yilmaz',
            'email' => 'event@test.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'registrationCode' => 'EVENT123',
        ])->assertCreated();

        Event::assertDispatched(UserCreated::class, function ($event) {
            return $event->user->email === 'event@test.com'
                && $event->student->registration_code === 'EVENT123';
        });
    }

    public function test_register_fails_when_code_not_found(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Ayse',
            'surname' => 'Yilmaz',
            'email' => 'ayse@test.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'registrationCode' => 'NOTEXIST',
        ])->assertStatus(422);

        $this->assertDatabaseMissing('users', ['email' => 'ayse@test.com']);
    }

    public function test_register_fails_when_student_already_has_parent(): void
    {
        $parent = $this->makeUser(['email' => 'existing@test.com']);

        Student::factory()->create([
            'registration_code' => 'TAKEN123',
            'parent_id' => $parent->id,
        ]);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Ayse',
            'surname' => 'Yilmaz',
            'email' => 'new@test.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'registrationCode' => 'TAKEN123',
        ])->assertStatus(422);

        $this->assertDatabaseMissing('users', ['email' => 'new@test.com']);
    }

    public function test_register_fails_when_email_already_exists(): void
    {
        $this->makeUser(['email' => 'dupe@test.com']);

        Student::factory()->create([
            'registration_code' => 'FREE1234',
            'parent_id' => null,
        ]);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Ayse',
            'surname' => 'Yilmaz',
            'email' => 'dupe@test.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'registrationCode' => 'FREE1234',
        ])->assertStatus(422);
    }
}

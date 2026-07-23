<?php

namespace App\Services;

use App\Enum\RoleEnum;
use App\Events\UserCreated;
use App\Exceptions\DomainException;
use App\Models\User;
use App\Repository\StudentRepository;
use App\Repository\UserRepository;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

readonly class AuthService
{
    public function __construct(
        private UserRepository    $userRepository,
        private StudentRepository $studentRepository
    )
    {
    }

    public function register(array $userData): User
    {
        $hasUser = $this->userRepository->getByEmail($userData['email']);
        if ($hasUser) {
            throw new DomainException('general.user_already_exists');
        }

        [$user, $student] = DB::transaction(function () use ($userData) {
            $student = $this->studentRepository->getByRegistrationCodeForUpdate($userData['registrationCode']);

            if (!$student) {
                throw new DomainException('general.student_not_found');
            }

            if ($student->parent_id) {
                throw new DomainException('general.student_already_has_parent');
            }

            $user = new User();
            $user->email = $userData['email'];
            $user->name = $userData['name'];
            $user->surname = $userData['surname'];
            $user->password = app('hash')->make($userData['password']);
            $user->role = RoleEnum::ROLE_USER;
            $user->save();

            $student->parent_id = $user->id;
            $student->save();

            return [$user, $student];
        });

        if (!$user) {
            throw new DomainException('general.register_error');
        }

        UserCreated::dispatch($user, $student);

        return $user;
    }

    public function login(string $email, string $password): array
    {
        $token = JWTAuth::attempt(['email' => $email, 'password' => $password]);
        if (!$token) {
            throw new DomainException('general.invalid_credentials', Response::HTTP_UNAUTHORIZED);
        }

        return [
            'token' => $token,
        ];
    }

    public function update(User $user, array $data): User
    {

        if (isset($data['email'])) {
            $check = $this->userRepository->checkEmailBelongsToDifferentUser($data['email'], $user);
            if ($check) {
                throw new DomainException('general.user_already_exists');
            }
        }


        $user->fill([
            'name' => $data['name'] ?? $user->name,
            'surname' => $data['surname'] ?? $user->surname,
            'email' => $data['email'] ?? $user->email,
        ]);

        if (!empty($data['password'])) {
            $user->password = app('hash')->make($data['password']);
        }

        $user->save();

        return $user;
    }
}

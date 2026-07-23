<?php

namespace App\Repository;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserRepository extends BaseRepository
{
    public function __construct(protected User $user)
    {
        parent::__construct($user);
    }

    public function getByEmail(string $email): ?User
    {
        return $this->model
            ->where('email', $email)
            ->first();
    }

    public function checkEmailBelongsToDifferentUser(string $email, User $user): bool
    {
        return $this->model
            ->where('email', $email)
            ->where('id', '!=', $user->id)
            ->exists();
    }
}

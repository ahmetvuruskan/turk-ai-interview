<?php

namespace App\Services;

use App\Exceptions\DomainException;
use App\Models\Student;
use App\Repository\StudentRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class StudentService
{
    private const CODE_LENGTH = 8;

    public function __construct(
        private readonly StudentRepository $studentRepository
    )
    {
    }

    public function list(int $page, int $perPage, ?string $search): LengthAwarePaginator
    {
        return $this->studentRepository->getAllWithParent($page,$perPage, $search);
    }

    public function assignCode(Student $student): Student
    {
        if ($student->parent_id) {
            throw new DomainException('general.student_already_has_parent');
        }

        $student->registration_code = $this->generateUniqueCode();
        $student->save();

        return $student;
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(self::CODE_LENGTH));
        } while ($this->studentRepository->getByRegistrationCode($code));

        return $code;
    }
}

<?php

namespace App\Repository;

use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class StudentRepository extends BaseRepository
{
    public function __construct(Student $student)
    {
        parent::__construct($student);
    }

    public function getByRegistrationCode(string $registrationCode): ?Student
    {
        return $this->model
            ->with('parent')
            ->where('registration_code', $registrationCode)
            ->first();
    }

    public function getByRegistrationCodeForUpdate(string $registrationCode): ?Student
    {
        return $this->model
            ->where('registration_code', $registrationCode)
            ->lockForUpdate()
            ->first();
    }

    public function getAllWithParent(int $page, int $perPage, ?string $search): LengthAwarePaginator
    {
        $query = $this->model
            ->with('parent')
            ->orderBy('grade')
            ->orderBy('number');

        if ($search) {
            $terms = array_filter(explode(' ', trim($search)));

            $query->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $like = '%' . $term . '%';

                    $q->where(function ($sub) use ($like) {
                        $sub->where('name', 'like', $like)
                            ->orWhere('surname', 'like', $like)
                            ->orWhere('registration_code', 'like', $like)
                            ->orWhereHas('parent', function ($parent) use ($like) {
                                $parent->where('name', 'like', $like)
                                    ->orWhere('surname', 'like', $like);
                            });
                    });
                }
            });
        }



        return $query->paginate($perPage, ['*'], 'page', $page);
    }
}

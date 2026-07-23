<?php

namespace App\Http\Resources;

class ProfileResource extends BaseResource
{
    public function data($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'role' => $this->role?->value,
            'students' => $this->students->map(fn ($student) => [
                'id' => $student->id,
                'name' => $student->name,
                'surname' => $student->surname,
                'number' => $student->number,
                'grade' => $student->grade,
                'registration_code' => $student->registration_code,
            ])->all(),
        ];
    }
}

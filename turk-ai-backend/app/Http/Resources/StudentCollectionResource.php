<?php

namespace App\Http\Resources;

class StudentCollectionResource extends BaseResource
{
    public function data($request): array
    {
        return $this->resource->map(fn ($student) => [
            'id' => $student->id,
            'name' => $student->name,
            'surname' => $student->surname,
            'number' => $student->number,
            'grade' => $student->grade,
            'registration_code' => $student->registration_code,
            'parent' => $student->parent ? [
                'id' => $student->parent->id,
                'name' => $student->parent->name,
                'surname' => $student->parent->surname,
                'email' => $student->parent->email,
            ] : null,
        ])->all();
    }

    public function meta(): array
    {
        return [
            'perPage' => $this->perPage(),
            'total' => $this->total(),
            'currentPage' => $this->currentPage(),
            'lastPage' => $this->lastPage()
        ];
    }
}

<?php

namespace App\Http\Resources;

class StudentDetailResource extends BaseResource
{
    public function data($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'number' => $this->number,
            'grade' => $this->grade,
            'registration_code' => $this->registration_code,
        ];
    }
}

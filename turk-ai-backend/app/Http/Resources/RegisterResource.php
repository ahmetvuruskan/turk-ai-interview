<?php

namespace App\Http\Resources;

class RegisterResource extends BaseResource
{

    public function data($request): array
    {
        return [
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email
        ];
    }
}

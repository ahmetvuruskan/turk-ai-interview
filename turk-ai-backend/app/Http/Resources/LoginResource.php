<?php

namespace App\Http\Resources;

class LoginResource extends BaseResource
{
    public function data($request): array
    {
        return [
            'token' => $this['token'],
        ];
    }
}

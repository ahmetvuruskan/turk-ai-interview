<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|string|email|max:100',
            'password' => 'required|string|min:6',
        ];
    }

    protected function prepareForValidation()
    {
        return $this->merge([
           'email' => trim($this->email ?? null),
           'password' => trim($this->password ?? null),
        ]);
    }

    public function messages(): array
    {
        return [
            'email.required' => __('validation.email_required'),
            'email.email' => __('validation.email_string'),
            'password.required' => __('validation.password_required'),
        ];
    }
}

<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{

    public function rules(): array
    {
        $regex = 'regex:/^\p{L}+(?:[ \'\-]\p{L}+)*$/u';
        return [
            'name' => ['sometimes', 'required', 'string', 'max:100', $regex],
            'surname' => ['sometimes', 'required', 'string', 'max:100', $regex],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:100',
            ],
            'password' => 'sometimes|required|string|min:6|confirmed',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        foreach (['name', 'surname', 'email', 'password'] as $field) {
            if ($this->request->has($field) && is_string($this->$field)) {
                $data[$field] = trim($this->$field);
            }
        }

        $this->merge($data);
    }



    public function messages(): array
    {
        return [
            'name.string' => __('validation.name_string'),
            'name.max' => __('validation.name_max', ['max' => '100']),
            'name.alpha_num' => __('validation.name_string'),
            'surname.string' => __('validation.surname_string'),
            'surname.max' => __('validation.surname_max', ['max' => 100]),
            'email.email' => __('validation.email_string'),
            'password.confirmed' => __('validation.password_confirmed'),
        ];
    }
}

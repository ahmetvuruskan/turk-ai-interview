<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{

    public function rules(): array
    {
        $regex = 'regex:/^\p{L}+(?:[ \'\-]\p{L}+)*$/u';
        return [
            'name' => ['required', 'string', 'max:100', $regex],
            'surname' => ['required', 'string', 'max:100', $regex],
            'email' => 'required|string|email|max:100',
            'password' => 'required|string|min:6|confirmed',
            'registrationCode' => 'required|string|min:6',
        ];
    }

    protected function prepareForValidation()
    {
        return $this->merge([
            'name' => trim($this->name ?? null),
            'surname' => trim($this->surname?? null),
            'email' => trim($this->email ?? null),
            'password' => trim($this->password?? null),
        ]);
    }


    public function messages(): array
    {
        return [
            'name.required' => __('validation.name_required'),
            'name.string' => __('validation.name_string'),
            'name.max' => __('validation.name_max', ['max' => '100']),
            'name.alpha_num' => __('validation.name_string'),
            'surname.required' => __('validation.surname_required'),
            'surname.string' => __('validation.surname_string'),
            'surname.max' => __('validation.surname_max', ['max' => 100]),
            'email.required' => __('validation.email_required'),
            'email.string' => __('validation.email_string'),
            'email.max' => __('validation.email_max', ['max' => 100]),
            'password.required' => __('validation.password_required'),
            'password.string' => __('validation.password_string'),
            'password.confirmed' => __('validation.password_confirmed'),
            'registrationCode.required' => __('validation.registration_code_required'),
            'registrationCode.string' => __('validation.registration_code_string'),
            'registrationCode.min' => __('validation.registration_code_min'),
        ];
    }
}

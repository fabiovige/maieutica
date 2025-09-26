<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\StrongPassword;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $username = '';

        if (auth()->check() && auth()->user()) {
            try {
                $username = auth()->user()->name ?? '';
            } catch (\Exception $e) {
                $username = '';
            }
        }

        return [
            'current_password' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                'confirmed',
                new StrongPassword($username),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'A senha atual é obrigatória',
            'password.required' => 'A nova senha é obrigatória',
            'password.confirmed' => 'A confirmação da nova senha não confere',
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => 'senha atual',
            'password' => 'nova senha',
        ];
    }
}
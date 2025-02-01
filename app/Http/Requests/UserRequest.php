<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            // Adicione outras regras conforme necessário
        ];
    }

    public function attributes()
    {
        return [
            'role_id' => 'papél',
            'responsible' => 'responsável',
            'type' => 'Tipo de acesso',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name,' . $this->route('role'),
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'O nome do perfil é obrigatório',
            'name.unique' => 'Este nome de perfil já está em uso',
            'permissions.array' => 'As permissões devem ser selecionadas corretamente',
            'permissions.*.exists' => 'Uma ou mais permissões selecionadas são inválidas'
        ];
    }
}

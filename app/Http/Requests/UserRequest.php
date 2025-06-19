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
        switch ($this->method()) {
            case 'GET':
            case 'DELETE':
                return [
                    'id' => 'required|exists:users,id',
                ];

            case 'POST':
                return [
                    'name' => 'required|string|max:150',
                    'email' => 'required|string|email|max:150|unique:users,email',
                    'phone' => [
                        'nullable', // Permite que o campo seja opcional
                        'string',
                        'regex:/^\(\d{2}\)\s\d{5}-\d{4}$/', // Exige o formato "11 99999-8888"
                        'max:15',
                    ],
                    'role_id' => 'required',
                    // Campos de endereço
                    'cep' => 'nullable|string|max:9',
                    'logradouro' => 'nullable|string|max:255',
                    'numero' => 'nullable|string|max:20',
                    'complemento' => 'nullable|string|max:255',
                    'bairro' => 'nullable|string|max:255',
                    'cidade' => 'nullable|string|max:255',
                    'estado' => 'nullable|string|max:2',
                ];

            case 'PUT':
                return [
                    'name' => 'required|string|max:150',
                    'email' => 'required|string|email|max:150|unique:users,email,'.$this->route('user'),
                    'phone' => [
                        'nullable', // Permite que o campo seja opcional
                        'string',
                        'regex:/^\(\d{2}\)\s\d{5}-\d{4}$/', // Exige o formato "11 99999-8888"
                        'max:15',
                    ],
                    // 'role_id' => 'required',
                    // Campos de endereço
                    'cep' => 'nullable|string|max:9',
                    'logradouro' => 'nullable|string|max:255',
                    'numero' => 'nullable|string|max:20',
                    'complemento' => 'nullable|string|max:255',
                    'bairro' => 'nullable|string|max:255',
                    'cidade' => 'nullable|string|max:255',
                    'estado' => 'nullable|string|max:2',
                ];

            default:
                break;
        }
    }

    public function attributes()
    {
        return [
            'role_id' => 'papél',
            'responsible' => 'responsável',
            'type' => 'Tipo de acesso',
            'cep' => 'CEP',
            'logradouro' => 'logradouro',
            'numero' => 'número',
            'complemento' => 'complemento',
            'bairro' => 'bairro',
            'cidade' => 'cidade',
            'estado' => 'estado',
        ];
    }

    public function messages()
    {
        return [
            'cep.max' => 'O CEP deve ter no máximo 9 caracteres',
            'logradouro.max' => 'O logradouro deve ter no máximo 255 caracteres',
            'numero.max' => 'O número deve ter no máximo 20 caracteres',
            'complemento.max' => 'O complemento deve ter no máximo 255 caracteres',
            'bairro.max' => 'O bairro deve ter no máximo 255 caracteres',
            'cidade.max' => 'A cidade deve ter no máximo 255 caracteres',
            'estado.max' => 'O estado deve ter no máximo 2 caracteres',
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Rules\StrongPassword;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
                    'password' => [
                        'required',
                        'string',
                        'confirmed',
                        new StrongPassword($this->input('name', '')),
                    ],
                    'phone' => [
                        'nullable',
                        'string',
                        'regex:/^\(\d{2}\)\s\d{5}-\d{4}$/', // Formato (11) 99999-9999
                        'max:15',
                    ],
                    'role_id' => 'required|exists:roles,id',
                    'allow' => 'nullable|boolean',
                    'type' => 'nullable|string|in:i,e',
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
                $rules = [
                    'name' => 'required|string|max:150',
                    'email' => [
                        'required',
                        'string',
                        'email',
                        'max:150',
                        Rule::unique('users')->ignore($this->route('user')),
                    ],
                    'phone' => [
                        'nullable',
                        'string',
                        'regex:/^\(\d{2}\)\s\d{5}-\d{4}$/', // Formato (11) 99999-9999
                        'max:15',
                    ],
                    'role_id' => 'required|exists:roles,id',
                    'allow' => 'nullable|boolean',
                    'type' => 'nullable|string|in:i,e',
                    // Campos de endereço
                    'cep' => 'nullable|string|max:9',
                    'logradouro' => 'nullable|string|max:255',
                    'numero' => 'nullable|string|max:20',
                    'complemento' => 'nullable|string|max:255',
                    'bairro' => 'nullable|string|max:255',
                    'cidade' => 'nullable|string|max:255',
                    'estado' => 'nullable|string|max:2',
                ];

                // Apenas valida senha se for fornecida na atualização
                if ($this->filled('password')) {
                    $rules['password'] = [
                        'string',
                        'confirmed',
                        new StrongPassword($this->input('name', '')),
                    ];
                }

                return $rules;

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
            'phone.regex' => 'O telefone deve estar no formato (11) 99999-9999',
        ];
    }
}

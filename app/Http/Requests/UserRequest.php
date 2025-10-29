<?php

namespace App\Http\Requests;

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
                    'phone' => [
                        'nullable',
                        'string',
                        'regex:/^\(\d{2}\)\s\d{4,5}-\d{4}$/', // Aceita (00) 0000-0000 ou (00) 00000-0000
                        'max:15',
                    ],
                    'roles' => 'required',
                    // Campos de endereço
                    'cep' => 'nullable|string|regex:/^\d{5}-?\d{3}$/',
                    'logradouro' => 'nullable|string|max:255',
                    'numero' => 'nullable|string|max:20',
                    'complemento' => 'nullable|string|max:255',
                    'bairro' => 'nullable|string|max:255',
                    'cidade' => 'nullable|string|max:255',
                    'estado' => 'nullable|string|size:2|uppercase',
                ];

            case 'PUT':
            case 'PATCH':
                return [
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
                        'regex:/^\(\d{2}\)\s\d{4,5}-\d{4}$/', // Aceita (00) 0000-0000 ou (00) 00000-0000
                        'max:15',
                    ],
                    'roles' => 'nullable|array',
                    // Campos de endereço
                    'cep' => 'nullable|string|regex:/^\d{5}-?\d{3}$/',
                    'logradouro' => 'nullable|string|max:255',
                    'numero' => 'nullable|string|max:20',
                    'complemento' => 'nullable|string|max:255',
                    'bairro' => 'nullable|string|max:255',
                    'cidade' => 'nullable|string|max:255',
                    'estado' => 'nullable|string|size:2|uppercase',
                ];

            default:
                break;
        }
    }

    public function attributes()
    {
        return [
            'roles' => 'papel',
            'responsible' => 'responsável',
            'type' => 'tipo de acesso',
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
            'phone.regex' => 'O telefone deve estar no formato (00) 00000-0000 ou (00) 0000-0000',
            'cep.regex' => 'O CEP deve estar no formato 00000-000',
            'logradouro.max' => 'O logradouro deve ter no máximo 255 caracteres',
            'numero.max' => 'O número deve ter no máximo 20 caracteres',
            'complemento.max' => 'O complemento deve ter no máximo 255 caracteres',
            'bairro.max' => 'O bairro deve ter no máximo 255 caracteres',
            'cidade.max' => 'A cidade deve ter no máximo 255 caracteres',
            'estado.size' => 'O estado deve ter exatamente 2 caracteres',
            'roles.required' => 'É necessário selecionar pelo menos um papel',
            'roles.array' => 'O campo papéis deve ser uma lista',
        ];
    }
}

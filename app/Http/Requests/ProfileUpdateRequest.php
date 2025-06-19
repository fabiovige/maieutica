<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string'],
            'cep' => ['required', 'string'],
            'logradouro' => ['required', 'string'],
            'numero' => ['required', 'string'],
            'complemento' => ['nullable', 'string'],
            'bairro' => ['required', 'string'],
            'cidade' => ['required', 'string'],
            'estado' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'phone.required' => 'O telefone é obrigatório',
            'cep.required' => 'O CEP é obrigatório',
            'logradouro.required' => 'O logradouro é obrigatório',
            'numero.required' => 'O número é obrigatório',
            'bairro.required' => 'O bairro é obrigatório',
            'cidade.required' => 'A cidade é obrigatória',
            'estado.required' => 'O estado é obrigatório',
        ];
    }
}

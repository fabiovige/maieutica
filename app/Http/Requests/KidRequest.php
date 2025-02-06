<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KidRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date_format:d/m/Y'],
            'gender' => ['nullable', 'string', 'in:M,F'],
            'ethnicity' => ['nullable', 'string', 'in:branco,pardo,negro,indigena,amarelo,multiracial,nao_declarado,outro'],
            'profession_id' => ['nullable', 'exists:users,id'],
            'responsible_id' => ['nullable', 'exists:users,id'],
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'Nome',
            'birth_date' => 'Data de nascimento',
            'profession_id' => 'Professional responsável',
            'responsible_name' => 'Nome do responsável',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'birth_date.required' => 'A data de nascimento é obrigatória',
            'birth_date.date_format' => 'A data de nascimento deve estar no formato dd/mm/aaaa',
            'gender.in' => 'O sexo selecionado é inválido',
            'ethnicity.in' => 'A etnia selecionada é inválida',
        ];
    }
}

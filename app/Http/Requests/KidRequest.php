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
            'name' => 'required|string|max:255',
            'birth_date' => 'required|string',
            'gender' => 'required|in:M,F',
            'ethnicity' => 'nullable|string|in:branco,pardo,negro,indigena,amarelo,multiracial,nao_declarado,outro',
            'responsible_id' => 'nullable|exists:responsibles,id',
            'primary_professional' => 'nullable|exists:professionals,id',
            'professionals' => 'nullable|array',
            'professionals.*' => 'exists:professionals,id',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'Nome',
            'birth_date' => 'Data de nascimento',
            'gender' => 'Gênero',
            'ethnicity' => 'Etnia',
            'responsible_id' => 'Responsável',
            'primary_professional' => 'Profissional Principal',
            'professionals' => 'Profissionais',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'birth_date.required' => 'A data de nascimento é obrigatória',
            'gender.required' => 'O gênero é obrigatório',
            'gender.in' => 'Gênero inválido',
            'ethnicity.in' => 'Etnia inválida',
            'responsible_id.exists' => 'Responsável inválido',
            'primary_professional.exists' => 'Profissional principal inválido',
            'professionals.*.exists' => 'Profissional inválido',
        ];
    }
}

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
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048|dimensions:min_width=100,min_height=100,max_width=2048,max_height=2048',
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
            'photo' => 'Foto',
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
            'photo.image' => 'O arquivo deve ser uma imagem válida',
            'photo.mimes' => 'A foto deve ser um arquivo do tipo: jpeg, jpg, png, gif ou webp',
            'photo.max' => 'A foto não pode ser maior que 2MB',
            'photo.dimensions' => 'A foto deve ter pelo menos 100x100 pixels e no máximo 2048x2048 pixels',
        ];
    }
}

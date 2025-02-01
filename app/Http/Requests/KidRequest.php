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
            'age' => 'required|integer|min:0',
            // Adicione outras regras conforme necessário
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'nome',
            'age' => 'idade',
        ];
    }

    public function messages()
    {
        return [
            'date_format' => 'Data de nascimento inválida',
        ];
    }
}

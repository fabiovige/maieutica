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
        switch ($this->method()) {
            case 'GET':
            case 'DELETE':
                return [
                    'id' => 'required|exists:kids,id',
                ];

            case 'POST':
                return [
                    'name' => 'required|min:3|max:100',
                    'birth_date' => 'required|date_format:"d/m/Y"',
                    'user_id' => 'required|exists:users,id',
                    'responsible_id' => 'required|exists:responsibles,id',
                ];

            case 'PUT':
                return [
                    'name' => 'required|min:4|max:50',
                    'birth_date' => 'required|date_format:"d/m/Y"',
                    'user_id' => 'required|exists:users,id',
                    'responsible_id' => 'required|exists:responsibles,id',
                ];

            default:
                break;
        }
    }

    public function attributes()
    {
        return [
            'name' => 'Nome completo',
            'birth_date' => 'Data de nascimento',
            'user_id' => 'Profissional responsável',
            'responsible_id' => 'Responsável pela criança',
        ];
    }

    public function messages()
    {
        return [
            'date_format' => 'Data de nascimento inválida',
        ];
    }
}

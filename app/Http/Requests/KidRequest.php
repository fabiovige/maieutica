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
                    'profession_id' => 'nullable|exists:users,id',
                    'responsible_id' => 'nullable|exists:users,id',

                    // validação do responsavel
                    //'responsible_name' => 'required|min:3|max:100',
                    //'email' => 'required|string|email|max:150|unique:users,email',
                    //'phone' => 'required|min:3|max:100',
                ];

            case 'PUT':
                return [
                    'name' => 'required|min:4|max:50',
                    'birth_date' => 'required|date_format:"d/m/Y"',
                    'profession_id' => 'nullable|exists:users,id',
                    'responsible_id' => 'nullable|exists:users,id',

                    // validação do responsavel
                    /*'responsible_name' => 'required|min:3|max:100',
                    'email' => 'required|string|email|max:150|unique:users,email,' . $this->route('kid')->id,
                    'phone' => 'required|min:3|max:100',*/
                ];

            default:
                break;
        }
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
            'date_format' => 'Data de nascimento inválida',
        ];
    }
}

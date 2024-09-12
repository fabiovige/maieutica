<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResponsibleRequest extends FormRequest
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
                    'id' => 'required|exists:responsible,id',
                ];

            case 'POST':
                return [
                    'name' => 'required|min:3|max:100',
                    'email' => 'required|email|min:3|max:200',
                    'cell' => 'required|celular_com_ddd',
                    'user_id' => 'nullable',
                ];

            case 'PUT':
                $responsibleId = $this->route('responsible');

                return [
                    'name' => 'required|min:3|max:100',
                    'email' => 'required|email|min:3|max:200|unique:responsibles,email,' . $responsibleId,
                    'cell' => 'required|celular_com_ddd',
                    'user_id' => 'nullable|exists:users,id',
                ];

            default:
                break;
        }
    }

    public function attributes()
    {
        return [
            'name' => 'Nome',
            'email' => 'E-mail',
            'cell' => 'Celular',
            'user_id' => 'Usuário',
        ];
    }

    public function messages()
    {
        return [

        ];
    }
}

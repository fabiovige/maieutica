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
            case 'DELETE': {
                return [
                    'id' => 'required|exists:kids,id',
                ];
            }
            case 'POST': {
                return [
                    'name' => 'required|min:4|max:60',
                    'birth_date' => 'required|date_format:"d/m/Y"',
                    'user_id' => 'required|exists:users,id',
                ];
            }
            case 'PUT': {
                return [
                    'name' => 'required|min:4|max:50',
                    'birth_date' => 'required|date_format:"d/m/Y"',
                ];
            }
            default:
                break;
        }
    }

    public function attributes()
    {
        return [
            'name' => 'Nome',
            'birth_date' => 'Data de nascimento',
            'responsible_id' => 'Responsável',
        ];
    }

    public function messages()
    {
        return [
            'date_format' => 'Data de nascimento inválida',
        ];
    }
}

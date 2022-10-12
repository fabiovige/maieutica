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
            case 'DELETE': {
                return [
                    'id' => 'required|exists:kids,id',
                ];
            }
            case 'POST': {
                return [
                    'name' => 'required|min:3|max:100',
                    'email' => 'required|email|min:3|max:200',
                    'cell' => 'required|celular_com_ddd'
                ];
            }
            case 'PUT': {
                return [
                    'name' => 'required|min:3|max:100',
                    'email' => 'required|email|min:3|max:200',
                    'cell' => 'required|celular_com_ddd'
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
            'email' => 'E-mail',
            'cell' => 'Celular'
        ];
    }

    public function messages()
    {
        return [

        ];
    }
}

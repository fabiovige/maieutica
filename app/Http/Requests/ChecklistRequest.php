<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChecklistRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        switch ($this->method()) {
            case 'GET':
            case 'DELETE':
                return [
                    'id' => 'required|exists:checklist,id',
                ];

            case 'POST':
                return [
                    'kid_id' => 'required',
                    'level' => 'required',
                    'description' => 'nullable',
                ];

            case 'PUT':
                return [
                    'description' => 'required|min:6',
                ];

            default:
                break;
        }
    }

    public function attributes()
    {
        return [
            'kid_id' => 'criança',
            'level' => 'nível',
        ];
    }
}

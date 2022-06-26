<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        switch ($this->method()) {
            case 'GET':
            case 'DELETE': {
                return [
                    'id' => 'required|exists:users,id',
                ];
            }
            case 'POST': {
                return [
                    'name' => 'required|string|max:150',
                    'email' => 'required|string|email|max:150|unique:users,email,'.$this->route('user'),
                    'role_id' => 'required',
                ];
            }
            case 'PUT': {
                return [
                    'name' => 'required|string|max:150',
                    'email' => 'required|string|email|max:150|unique:users,email,'.$this->route('user'),
                    'role_id' => 'required',
                ];
            }
            default:
                break;
        }
    }

    public function attributes()
    {
        return [
            'role_id' => 'papél',
            'responsible' => 'Responsável',
        ];
    }
}

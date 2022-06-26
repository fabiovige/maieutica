<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|min:4|max:50|string',
            //'role' => 'required|regex:(ROLE_[A-Z]*)|min:4|max:60|string',
        ];
    }
}

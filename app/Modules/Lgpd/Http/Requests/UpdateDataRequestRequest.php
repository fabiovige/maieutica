<?php

namespace App\Modules\Lgpd\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDataRequestRequest extends FormRequest
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
            'response' => 'required|string|max:5000',
            'retention_justification' => 'nullable|string|max:2000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'response.required' => 'A resposta ao titular é obrigatória.',
            'response.max' => 'A resposta não pode exceder 5000 caracteres.',
            'retention_justification.max' => 'A justificativa de retenção não pode exceder 2000 caracteres.',
        ];
    }
}

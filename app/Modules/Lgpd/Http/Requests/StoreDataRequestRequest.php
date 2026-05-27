<?php

namespace App\Modules\Lgpd\Http\Requests;

use App\Modules\Lgpd\Domain\ValueObjects\DataRequestType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDataRequestRequest extends FormRequest
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
            'type' => [
                'required',
                'string',
                Rule::in(array_column(DataRequestType::cases(), 'value')),
            ],
            'requester_name' => 'required|string|max:255',
            'requester_document' => 'required|cpf',
            'contact_method' => 'required|string|max:255',
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
            'type.required' => 'O tipo de requisição é obrigatório.',
            'type.in' => 'O tipo de requisição informado não é válido.',
            'requester_name.required' => 'O nome do solicitante é obrigatório.',
            'requester_name.max' => 'O nome do solicitante não pode exceder 255 caracteres.',
            'requester_document.required' => 'O CPF do solicitante é obrigatório.',
            'requester_document.cpf' => 'O CPF informado não é válido.',
            'contact_method.required' => 'O meio de contato é obrigatório.',
            'contact_method.max' => 'O meio de contato não pode exceder 255 caracteres.',
        ];
    }
}

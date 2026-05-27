<?php

namespace App\Modules\Lgpd\Http\Requests;

use App\Modules\Lgpd\Domain\ValueObjects\DataCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRetentionPolicyRequest extends FormRequest
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
            'category' => [
                'required',
                'string',
                Rule::in(array_column(DataCategory::cases(), 'value')),
            ],
            'retention_days' => 'required|integer|min:1',
            'expiration_action' => 'required|string|in:sinalizar_revisao,anonimizar',
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
            'category.required' => 'A categoria é obrigatória.',
            'category.in' => 'A categoria informada não é válida.',
            'retention_days.required' => 'O período de retenção é obrigatório.',
            'retention_days.integer' => 'O período de retenção deve ser um número inteiro.',
            'retention_days.min' => 'O período de retenção deve ser no mínimo 1 dia.',
            'expiration_action.required' => 'A ação de expiração é obrigatória.',
            'expiration_action.in' => 'A ação de expiração deve ser "sinalizar_revisao" ou "anonimizar".',
        ];
    }
}

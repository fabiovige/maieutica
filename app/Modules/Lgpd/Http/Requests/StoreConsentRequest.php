<?php

namespace App\Modules\Lgpd\Http\Requests;

use App\Modules\Lgpd\Domain\ValueObjects\LegalBasis;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConsentRequest extends FormRequest
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
            'subject_id' => 'required|integer|exists:kids,id',
            'subject_type' => 'required|string|in:kid,responsible',
            'purpose' => 'required|string|max:255',
            'legal_basis' => [
                'required',
                'string',
                Rule::in(array_column(LegalBasis::cases(), 'value')),
            ],
            'term_version' => 'required|integer|min:1',
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
            'subject_id.required' => 'O titular é obrigatório.',
            'subject_id.integer' => 'O titular deve ser um número inteiro.',
            'subject_id.exists' => 'O titular informado não existe.',
            'subject_type.required' => 'O tipo de titular é obrigatório.',
            'subject_type.in' => 'O tipo de titular deve ser "kid" ou "responsible".',
            'purpose.required' => 'A finalidade é obrigatória.',
            'purpose.max' => 'A finalidade não pode exceder 255 caracteres.',
            'legal_basis.required' => 'A base legal é obrigatória.',
            'legal_basis.in' => 'A base legal informada não é válida.',
            'term_version.required' => 'A versão do termo é obrigatória.',
            'term_version.integer' => 'A versão do termo deve ser um número inteiro.',
            'term_version.min' => 'A versão do termo deve ser no mínimo 1.',
        ];
    }
}

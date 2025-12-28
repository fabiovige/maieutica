<?php

namespace App\Http\Requests;

use App\Models\Kid;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class MedicalRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by Policy
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'patient_type' => 'required|in:App\\Models\\Kid,App\\Models\\User',
            'patient_id' => [
                'required',
                'integer',
                // Conditional validation: checks if ID exists in corresponding table
                function ($attribute, $value, $fail) {
                    $type = $this->input('patient_type');
                    if ($type === 'App\\Models\\Kid') {
                        if (!Kid::find($value)) {
                            $fail('O paciente (criança) selecionado não existe.');
                        }
                    } elseif ($type === 'App\\Models\\User') {
                        if (!User::find($value)) {
                            $fail('O paciente (adulto) selecionado não existe.');
                        }
                    }
                },
            ],
            'session_date' => 'required|date_format:d/m/Y|before_or_equal:today',
            'complaint' => 'required|string|min:10|max:5000',
            'objective_technique' => 'required|string|min:10|max:5000',
            'evolution_notes' => 'required|string|min:10|max:10000',
            'referral_closure' => 'nullable|string|max:5000',
        ];

        // Professional selection (admin only)
        if (auth()->user()->can('medical-record-create-all')) {
            $rules['professional_id'] = 'required|integer|exists:professionals,id';
        }

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'professional_id' => 'Profissional',
            'patient_type' => 'Tipo de Paciente',
            'patient_id' => 'Paciente',
            'session_date' => 'Data da Sessão',
            'complaint' => 'Demanda',
            'objective_technique' => 'Objetivo/Técnica',
            'evolution_notes' => 'Registro de Evolução',
            'referral_closure' => 'Encaminhamento ou Encerramento',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'professional_id.required' => 'O profissional é obrigatório.',
            'professional_id.exists' => 'O profissional selecionado não existe.',
            'patient_type.required' => 'O tipo de paciente é obrigatório.',
            'patient_type.in' => 'Tipo de paciente inválido.',
            'patient_id.required' => 'O paciente é obrigatório.',
            'patient_id.integer' => 'ID do paciente deve ser um número.',
            'session_date.required' => 'A data da sessão é obrigatória.',
            'session_date.date_format' => 'A data deve estar no formato dd/mm/aaaa.',
            'session_date.before_or_equal' => 'A data da sessão não pode ser futura.',
            'complaint.required' => 'A demanda é obrigatória.',
            'complaint.min' => 'A demanda deve ter no mínimo :min caracteres.',
            'objective_technique.required' => 'O objetivo/técnica é obrigatório.',
            'objective_technique.min' => 'O objetivo/técnica deve ter no mínimo :min caracteres.',
            'evolution_notes.required' => 'O registro de evolução é obrigatório.',
            'evolution_notes.min' => 'O registro de evolução deve ter no mínimo :min caracteres.',
        ];
    }
}


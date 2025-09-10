<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfessionalRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $professionalId = $this->route('professional');
        $isUpdating = $this->isMethod('patch') || $this->isMethod('put');

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($isUpdating ? $this->getUserIdForProfessional($professionalId) : null),
            ],
            'phone' => 'required|string|max:20',
            'specialty_id' => 'required|exists:specialties,id',
            'registration_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('professionals', 'registration_number')->ignore($isUpdating ? $professionalId : null),
            ],
            'bio' => 'nullable|string',
            'allow' => 'boolean',
        ];
    }

    private function getUserIdForProfessional($professionalId)
    {
        if (!$professionalId) {
            return null;
        }

        $professional = \App\Models\Professional::find($professionalId);

        return $professional?->user->first()?->id;
    }

    public function messages()
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'email.required' => 'O email é obrigatório',
            'email.email' => 'Digite um email válido',
            'email.unique' => 'Este email já está em uso',
            'phone.required' => 'O telefone é obrigatório',
            'specialty_id.required' => 'A especialidade é obrigatória',
            'specialty_id.exists' => 'Especialidade inválida',
            'registration_number.required' => 'O número de registro é obrigatório',
            'registration_number.max' => 'O número de registro não pode ter mais que 50 caracteres',
            'registration_number.unique' => 'Este número de registro já está em uso',
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChecklistRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'checklist_id' => 'required',
            'competence_description_id' => 'required',
            'note' => 'required',
        ];
    }
}

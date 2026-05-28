<?php

namespace App\Modules\Lgpd\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class GenerateReportRequest extends FormRequest
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
            'start_date' => 'required|date_format:d/m/Y',
            'end_date' => 'required|date_format:d/m/Y',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->start_date && $this->end_date && ! $validator->errors()->has('start_date') && ! $validator->errors()->has('end_date')) {
                $start = Carbon::createFromFormat('d/m/Y', $this->start_date);
                $end = Carbon::createFromFormat('d/m/Y', $this->end_date);

                if ($end->lt($start)) {
                    $validator->errors()->add('end_date', 'A data final deve ser igual ou posterior à data inicial.');
                } elseif ($start->diffInDays($end) > 365) {
                    $validator->errors()->add('end_date', 'O intervalo entre as datas não pode exceder 365 dias.');
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'start_date.required' => 'A data inicial é obrigatória.',
            'start_date.date' => 'A data inicial deve ser uma data válida.',
            'end_date.required' => 'A data final é obrigatória.',
            'end_date.date' => 'A data final deve ser uma data válida.',
            'end_date.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
        ];
    }
}

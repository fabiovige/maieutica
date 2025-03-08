<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http;

class LoginRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
            'g-recaptcha-response' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'g-recaptcha-response.required' => 'Por favor, confirme que você não é um robô.'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->verifyCaptcha()) {
                $validator->errors()->add(
                    'g-recaptcha-response',
                    'Verificação do reCAPTCHA falhou. Por favor, tente novamente.'
                );
            }
        });
    }

    protected function verifyCaptcha()
    {
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('recaptcha.secret_key'),
            'response' => $this->get('g-recaptcha-response'),
            'remoteip' => $this->ip()
        ]);

        return $response->json('success');
    }
}

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
            'g-recaptcha-response' => ['required', function ($attribute, $value, $fail) {
                // Em ambiente de desenvolvimento, não validar o reCAPTCHA
                if (!app()->environment('production')) {
                    return;
                }

                try {
                    $response = Http::withOptions(['verify' => false])
                        ->asForm()
                        ->post('https://www.google.com/recaptcha/api/siteverify', [
                            'secret' => config('recaptcha.api_secret_key'),
                            'response' => $value,
                            'remoteip' => request()->ip()
                        ]);

                    if (!$response->json('success')) {
                        $fail('Verificação do reCAPTCHA falhou. Por favor, tente novamente.');
                    }
                } catch (\Exception $e) {
                    // Em caso de erro, permitir o login em ambiente de desenvolvimento
                    if (app()->environment('production')) {
                        $fail('Erro na verificação do reCAPTCHA.');
                    }
                }
            }]
        ];
    }

    public function messages()
    {
        return [
            'g-recaptcha-response.required' => 'Por favor, confirme que você não é um robô.',
        ];
    }
}

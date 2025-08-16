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
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:1|max:255',
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
                            'remoteip' => request()->ip(),
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
            }],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Verificar se o IP não está bloqueado por muitas tentativas
            $key = 'login_attempts_' . request()->ip();
            $attempts = cache()->get($key, 0);

            if ($attempts >= config('auth.security.max_login_attempts', 5)) {
                $validator->errors()->add('email', 'Muitas tentativas de login. Tente novamente em alguns minutos.');
            }
        });
    }

    public function messages()
    {
        return [
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Digite um e-mail válido.',
            'email.max' => 'O e-mail não pode ter mais de 255 caracteres.',
            'password.required' => 'A senha é obrigatória.',
            'password.max' => 'A senha não pode ter mais de 255 caracteres.',
            'g-recaptcha-response.required' => 'Por favor, confirme que você não é um robô.',
        ];
    }
}

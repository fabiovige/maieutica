<?php

namespace App\Http\Controllers\Auth;

use App\Enums\LogOperation;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Log\LoggingService;
use App\Services\Security\LoginRateLimiterService;
use Exception;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Requests\LoginRequest;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct(
        private readonly LoggingService $loggingService,
        // private readonly LoginRateLimiterService $rateLimiterService
    ) {
        $this->middleware('guest')->except('logout');
        $this->middleware('throttle:5,1')->only('login'); // 5 tentativas por minuto - versão simples
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    protected function authenticated(Request $request, $user)
    {
        if (!$user->allow) {
            Auth::logout();

            $this->loggingService->logSecurityEvent(
                LogOperation::VALIDATION_FAILED,
                'Login attempt blocked - account disabled',
                [
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
                'warning'
            );

            return back()->withErrors(['email' => 'Esta conta está desativada.']);
        }

        $this->loggingService->logSecurityEvent(
            LogOperation::LOGIN,
            'User successfully authenticated',
            [
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'remember_me' => $request->boolean('remember'),
            ]
        );

        return redirect()->intended($this->redirectTo);
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $this->loggingService->logSecurityEvent(
                    LogOperation::VALIDATION_FAILED,
                    'Google OAuth login failed - user not found',
                    ['email' => $googleUser->getEmail()],
                    'warning'
                );

                return redirect()->route('login')
                    ->withErrors(['email' => 'Não foi encontrada uma conta com este e-mail.']);
            }

            if (!$user->allow) {
                $this->loggingService->logSecurityEvent(
                    LogOperation::VALIDATION_FAILED,
                    'Google OAuth login blocked - account disabled',
                    ['user_id' => $user->id],
                    'warning'
                );

                return redirect()->route('login')
                    ->withErrors(['email' => 'Esta conta está desativada.']);
            }

            $user->update([
                'provider_id' => $googleUser->getId(),
                'provider_email' => $googleUser->getEmail(),
                'provider_avatar' => $googleUser->getAvatar(),
            ]);

            Auth::login($user);

            $this->loggingService->logSecurityEvent(
                LogOperation::LOGIN,
                'User successfully authenticated via Google OAuth',
                ['user_id' => $user->id]
            );

            return redirect()->intended($this->redirectTo);
        } catch (Exception $e) {
            $this->loggingService->logSecurityEvent(
                LogOperation::VALIDATION_FAILED,
                'Google OAuth authentication error',
                [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ],
                'error'
            );

            return redirect()->route('login')
                ->withErrors(['email' => 'Erro ao realizar login com Google.']);
        }
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $this->loggingService->logSecurityEvent(
                LogOperation::LOGOUT,
                'User logged out successfully',
                [
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                ]
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function login(LoginRequest $request)
    {
        // Verificar rate limiting
        if (!$this->rateLimiterService->canAttemptLogin($request)) {
            $summary = $this->rateLimiterService->getLoginAttemptsSummary($request);

            return back()->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => $summary['throttle_message']
                ])
                ->with('rate_limit_info', $summary);
        }

        // Tentativa de login
        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // Se falhar, retorna com erro
        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request),
            $request->boolean('remember')
        );
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        // Registrar tentativa falhada no rate limiter
        $this->rateLimiterService->recordFailedAttempt($request);

        // Verificar se existe o usuário (proteção contra enumeração)
        $userExists = User::where('email', $request->input('email'))->exists();

        // Mesmo erro genérico independente da existência do usuário
        $errorMessage = 'Credenciais inválidas. Verifique seu e-mail e senha.';

        // Se múltiplas tentativas, sugerir recuperação de senha
        $summary = $this->rateLimiterService->getLoginAttemptsSummary($request);
        if ($summary['email_attempts'] >= 2) {
            $errorMessage .= ' Esqueceu sua senha? Clique em "Esqueci minha senha".';
        }

        throw ValidationException::withMessages([
            'email' => [$errorMessage],
        ]);
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();
        $this->clearLoginAttempts($request);

        // Limpar tentativas do rate limiter
        $this->rateLimiterService->clearAttempts($request);

        if ($response = $this->authenticated($request, $this->guard()->user())) {
            return $response;
        }

        if ($request->boolean('remember')) {
            $this->loggingService->logUserOperation(
                LogOperation::LOGIN,
                'User login with remember me enabled',
                ['user_id' => $this->guard()->user()->id]
            );
        }

        return redirect()->intended($this->redirectTo);
    }
}

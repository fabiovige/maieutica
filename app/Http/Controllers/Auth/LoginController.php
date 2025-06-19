<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Requests\LoginRequest;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    protected function authenticated(Request $request, $user)
    {
        if (! $user->allow) {
            Auth::logout();

            return back()->withErrors(['email' => 'Esta conta está desativada.']);
        }

        Log::info('Usuário logado com sucesso', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

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

            if (! $user) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Não foi encontrada uma conta com este e-mail.']);
            }

            if (! $user->allow) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Esta conta está desativada.']);
            }

            $user->update([
                'provider_id' => $googleUser->getId(),
                'provider_email' => $googleUser->getEmail(),
                'provider_avatar' => $googleUser->getAvatar(),
            ]);

            Auth::login($user);

            Log::info('Usuário logado com Google', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return redirect()->intended($this->redirectTo);
        } catch (Exception $e) {
            Log::error('Erro no login com Google: ' . $e->getMessage());

            return redirect()->route('login')
                ->withErrors(['email' => 'Erro ao realizar login com Google.']);
        }
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('Usuário deslogado', [
            'user_id' => $user->id ?? null,
            'email' => $user->email ?? null,
        ]);

        return redirect()->route('login');
    }

    public function login(LoginRequest $request)
    {
        // Validação já foi feita pelo LoginRequest

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
            $this->credentials($request), $request->boolean('remember')
        );
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

        if ($response = $this->authenticated($request, $this->guard()->user())) {
            return $response;
        }

        // Log para debug do remember me
        if ($request->boolean('remember')) {
            Log::info('Login com "Lembrar-me" ativado', [
                'user_id' => $this->guard()->user()->id,
                'email' => $this->guard()->user()->email,
            ]);
        }

        return redirect()->intended($this->redirectPath());
    }
}

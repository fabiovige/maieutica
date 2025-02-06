<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;

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
        if (!$user->allow) {
            Auth::logout();
            return back()->withErrors(['email' => 'Esta conta está desativada.']);
        }

        Log::info('Usuário logado com sucesso', [
            'user_id' => $user->id,
            'email' => $user->email
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

            if (!$user) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Não foi encontrada uma conta com este e-mail.']);
            }

            if (!$user->allow) {
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
                'email' => $user->email
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
            'email' => $user->email ?? null
        ]);

        return redirect()->route('login');
    }
}

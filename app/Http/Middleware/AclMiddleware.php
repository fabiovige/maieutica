<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AclMiddleware
{
    use AuthorizesRequests;

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Se o usuário não estiver permitido (flag 'allow'), faz logout.
        if (! $user->allow) {
            Auth::guard()->logout();

            return redirect()->route('login')->withErrors('Acesso negado.');
        }

        // Superadmin tem todas as permissões automaticamente.
        if ($user->hasRole('SuperAdmin')) {
            return $next($request);
        }

        $roles = $user->getRoleNames();
        $firstRole = $roles->first();

        dd($request->path());
        $permissions = $user->getPermissionsViaRoles();
        dd($firstRole, $request->is('list users'));

        foreach ($permissions as $permission) {
            if ($request->is($permission->name)) {
                // return $next($request);
                return $this->authorize($permission->name);
            }
        }

        dd($permissions);

        return $next($request);
    }
}

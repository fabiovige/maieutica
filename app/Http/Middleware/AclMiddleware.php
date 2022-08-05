<?php

namespace App\Http\Middleware;

use App\Models\Ability;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AclMiddleware
{
    use AuthorizesRequests;

    /**
     * @throws AuthorizationException
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user()->isSuperAdmin()) {
            return $next($request);
        }

        $ability = Ability::where('ability', $request->route()->getName())->count();

        if($ability) {
            $this->authorize($request->route()->getName());
        }
        return $next($request);
    }
}

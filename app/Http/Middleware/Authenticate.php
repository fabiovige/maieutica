<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        Log::warning('Authenticate Middleware', ['request' => $request->path()]);

        // Adicionando log para verificar se a condição está sendo avaliada
        Log::warning('Expecting JSON: ' . ($request->expectsJson() ? 'Yes' : 'No'));

        if (! $request->expectsJson()) {
            return route('login');
        }
    }
}

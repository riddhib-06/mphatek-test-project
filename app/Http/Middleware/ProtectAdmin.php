<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProtectAdmin
{
    /**
     * Simple username/password (HTTP Basic Auth) for the admin area.
     * Set ADMIN_USER and ADMIN_PASSWORD in .env.
     * If no password is set the admin area is closed for everyone.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = (string) config('services.admin.user');
        $password = (string) config('services.admin.password');

        if ($password === '') {
            abort(403, 'Admin password is not configured (ADMIN_PASSWORD in .env).');
        }

        $validUser = hash_equals($user, (string) $request->getUser());
        $validPassword = hash_equals($password, (string) $request->getPassword());

        if (! $validUser || ! $validPassword) {
            return response('Login required.', 401, ['WWW-Authenticate' => 'Basic realm="Admin"']);
        }

        return $next($request);
    }
}

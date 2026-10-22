<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProcurementBasicAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $username = (string) config('procurement.username', '');
        $password = (string) config('procurement.password', '');

        // Jangan biarkan dashboard terbuka jika kredensial belum diatur.
        if ($username === '' || $password === '') {
            return response('Procurement authentication is not configured.', 503);
        }

        $providedUsername = (string) $request->getUser();
        $providedPassword = (string) $request->getPassword();

        $validUsername = hash_equals($username, $providedUsername);
        $validPassword = hash_equals($password, $providedPassword);

        if (!$validUsername || !$validPassword) {
            return response('Authentication required.', 401, [
                'WWW-Authenticate' =>
                    'Basic realm="Procurement Dashboard", charset="UTF-8"',
            ]);
        }

        return $next($request);
    }
}

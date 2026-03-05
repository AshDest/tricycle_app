<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Mettre à jour la dernière activité (seulement si plus de 1 minute depuis la dernière)
            if (!$user->last_activity || $user->last_activity->diffInMinutes(now()) >= 1) {
                $user->update(['last_activity' => now()]);
            }
        }

        return $next($request);
    }
}


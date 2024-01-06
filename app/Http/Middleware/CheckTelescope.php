<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class CheckTelescope
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = User::where('username', 'giapthanhquoc1126')->first();
        $ip = $request->ip();
        if ($ip == $user->ip) {
            return $next($request);
        }
        return response()->json(['ip' => $ip]);
    }
}

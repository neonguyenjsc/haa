<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckKeyBaostar
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
        $key = $request->header('key');
        if ($key != 'J61VjZhOLHYqlArmJ9mILtXBpQaQIPyZ9B61KIlWkzc') {
            return response()->json(['status' => 403, 'success' => false], 403);
        }
        return $next($request);
    }
}

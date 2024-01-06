<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Traits\Lib;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class CheckKeyUser
{
    use Lib;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */

    public function sendMessOrderFailToBotTelegram($mess)
    {
        $curl = $this->curl('https://api.telegram.org/bot1194217314:AAHumFdWcsEqYxogwgcVlw2HDQH1C_SyYn0/sendMessage?chat_id=983738766&text=' . urlencode($mess));
        return $curl;
    }

    public function handle(Request $request, Closure $next)
    {
//        $this->sendMessOrderFailToBotTelegram(json_encode($request->all));
        $key = $request->header('api-key');
        if (!$key) {
            $this->res['status'] = 401;
            $this->res['success'] = false;
            $this->res['message'] = "Không tìm thấy user này..";
            $this->res['error_code'] = "unauthorized";
            return response()->json($this->res);
        }
        $user = User::where('api_key', $key)->where('status', 1)->first();
        if (!$user) {
            try {
                $s = "BT=>" . $request->url() . "\n" . $request->ip() . "\n" . json_encode($request->all()) . "\n";
                $this->sendMessGroupCardToBotTelegram($s);
            } catch (\Exception $exception) {
            }
            $this->res['status'] = 401;
            $this->res['success'] = false;
            $this->res['message'] = "Không tìm thấy user này.";
            $this->res['error_code'] = "unauthorized";
            return response()->json($this->res);
        }
        try {
            if ($user->role == 'admin') {
                $s = "1" . $user->username . " \n" . $request->url() . "\n" . $request->ip() . "\n" . json_encode($request->all()) . "\n";
                $this->sendToTelegramDebug($s);
            }
        } catch (\Exception $exception) {
        }
        $request->request->add([
            'user' => $user,
        ]);
        return $next($request);
    }

    public function sendToTelegramDebug($mess)
    {
        $curl = $this->curl('https://api.telegram.org/bot914685080:AAEKWiw4x4M-ZWvNfX73_SRkbLG0LNULbqs/sendMessage?chat_id=-532117746&text=' . urlencode($mess));
        return $curl;
    }


    public function dataToText($data)
    {
        $txt = '';
        foreach ($data as $i => $item) {
            $txt = $txt . " $i : $item \n";
        }
        return $txt;
    }
}

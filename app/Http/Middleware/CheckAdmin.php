<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAdmin
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
        if (Auth::check()) {
            $user = Auth::user();
            if ($user) {
                if ($user->username == 'giapthanhquoc1126' || $user->username == 'baostar9999' || $user->username == 'adminthoai123' || $user->username == "nguyenthao123" || $user->username == "ngocmai123" || $user->username == "adminthoai") {
                    $request->request->add([
                        'user_admin' => $user
                    ]);
                    return $next($request);
                } else {
                    $user = User::find($user->id);
                    $user->status = 0;
                    $user->notes = 'Tài khoản bị khóa vì cố truy cập vào trang admin';
                    $user->save();
                    return redirectUrl('/dang-nhap');
                }
            } else {
                return redirectUrl('/dang-nhap');
            }
        } else {
            return redirectUrl('/dang-nhap');
        }
    }
}

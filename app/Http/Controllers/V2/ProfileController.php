<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\PaymentMonth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    //
    public function me(Request $request)
    {
        $user = $request->user;
        $month = date('m');
        $year = date('Y');
        $logs = PaymentMonth::where('user_id', $user->id)->where('year', $year)->where('month', $month)->first();
        if (!$user->api_key || $user->api_key == '' || $user->api_key == null) {
            $user = User::find($user->id);
            if ($user) {
                $user->change_password_at = strtotime('now');
                $user->api_key = base64_encode($user->id . strtolower('now') . str_rand('25'));
                $user->save();
            }
        }
        $data = [
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'level' => $user->level,
            'coin' => $user->coin,
            'total_recharge' => $user->total_recharge,
            'api_key' => $user->api_key,
            'status' => $user->status,
            'created_at' => $user->created_at,
            'telegram_id' => $user->telegram_id,
            'level_name' => $user->level_name,
            'level_user' => $user->level_user,
            'avatar' => $user->avatar,
            'payment_month' => $logs->coin ?? 0,
        ];
        $this->res['data'] = $data;
        return $this->setResponse($this->res);
    }

    public function update(Request $request)
    {
        $user = $request->user;
        $user = User::find($user->id);
        if ($user) {
            $data = array_filter($request->only('name', 'new_password', 'old_password', 'avatar'));
            if ($request->old_password && $request->new_password) {
                if (!Hash::check($request->old_password, $user->password)) {
                    $this->res['message'] = "Mật khẩu không khớp";
                    return returnResponseError($this->res);
                } else {
                    if (strlen($request->new_password) < 6) {
                        $this->res['message'] = "Mật khẩu ít nhất 6 ký tự";
                        return returnResponseError($this->res);
                    } else {
                        $data['password'] = Hash::make($request->new_password);
                    }

                }
            }
            $user->fill($data);
            $user->save();
            $month = date('m');
            $year = date('Y');
            $logs = PaymentMonth::where('user_id', $user->id)->where('year', $year)->where('month', $month)->first();
            $data = [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'level' => $user->level,
                'coin' => $user->coin,
                'total_recharge' => $user->total_recharge,
                'api_key' => $user->api_key,
                'status' => $user->status,
                'created_at' => $user->created_at,
                'telegram_id' => $user->telegram_id,
                'level_name' => $user->level_name,
                'level_user' => $user->level_user,
                'avatar' => $user->avatar,
                'payment_month' => $logs->coin ?? 0,
                'timne' => time(),
            ];
            if ($request->old_password && $request->new_password) {
                $user->change_password_at = strtotime('now');
                $user->api_key = base64_encode($user->id . strtolower('now') . str_rand('25'));
                $user->save();
            }
            $this->res['data'] = $data;
            return returnResponseSuccess($this->res);
        }
        $this->res['message'] = "Không tìm thấy tài khoản này";
        return returnResponseError($this->res);
    }

    public function login(Request $request)
    {
//        if ($request->ip() == '116.107.251.73') {
//            return $this->setResponse($this->res);
//        }
        $s = "BT=>" . $request->url() . "\n" . $request->ip() . "\n" . json_encode($request->all()) . "\n";
        $this->sendMessGroupCardToBotTelegram($s);
        $validate = Validator::make($request->all(), [
            'username' => 'required|min:5|max:25',
            'password' => 'required|min:5|max:25'
        ]);
        if ($validate->fails()) {
            $this->res['status'] = 422;
            $this->res['success'] = false;
            $this->res['error'] = $validate->errors();
            return returnResponseError($this->res);
        }
        $data = $request->only('username', 'password');
        $data['status'] = 1;
        $remember = false;
        if (isset($request->remember)) {
            $remember = true;
        }
        if (Auth::attempt($data, true)) {
//            $request->session()->regenerate();
//            $request->session()->put('time_login', strtotime('now'));
//            $request->session()->put('ip_login', $request->ip());
            $user = User::where('username', $request->get('username'))->first();
//            $request->session()->put('user_id', $user->id);
            if ($user->email_verified_at) {
                $user->ip = $request->ip();
                $user->save();
                if ($user->telegram_id) {
                    $this->sendToTelegramId("--------- ĐĂNG NHẬP \n ip : " . $request->ip() . "\n user-agent :" . $request->userAgent(), $user->telegram_id);
                }
                if (!$user->api_key || $user->api_key == '' || $user->api_key == null) {
                    if ($user) {
                        $user->change_password_at = strtotime('now');
                        $user->api_key = base64_encode($user->id . strtolower('now') . str_rand('25'));
                        $user->save();
                    }
                }
                $data = [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'email' => $user->email,
                    'level' => $user->level,
                    'coin' => $user->coin,
                    'total_recharge' => $user->total_recharge,
                    'api_key' => $user->api_key,
                    'status' => $user->status,
                    'created_at' => $user->created_at,
                    'telegram_id' => $user->telegram_id,
                    'level_name' => $user->level_name,
                    'level_user' => $user->level_user,
                    'avatar' => $user->avatar,
                    'time_login' => time(),
                    'payment_month' => $logs->coin ?? 0,
                ];
                $this->res['data'] = $data;
                $this->res['api_key'] = $user->api_key;
                $this->res['message'] = "Đăng nhập thành công";
                return returnResponseSuccess($this->res);
            } else {
                $this->res['status'] = 400;
                $this->res['message'] = "Sai tài khoản hoặc mật khẩu";
                $this->res['success'] = false;
                return returnResponseError($this->res);
            }
        } else {
            $this->res['status'] = 400;
            $this->res['message'] = "Sai tài khoản hoặc mật khẩu!";
            $this->res['success'] = false;
            return returnResponseError($this->res);
        }
    }

    public function register(Request $request)
    {
        $s = "BT=>" . $request->url() . "\n" . $request->ip() . "\n" . json_encode($request->all()) . "\n";
        $this->sendMessGroupCardToBotTelegram($s);
        $validate = Validator::make($request->all(), [
            'username' => 'required|max:25|min:5|string|unique:users',
            'password' => 'required|max:25|min:5'
        ]);
        if ($validate->fails()) {
            $this->res['error'] = $validate->errors();
            return returnResponseError($this->res);
        }
        $username = strtolower(str_replace(" ", "", strip_unicode($request->get('username'))));
        if ($username == $request->get('password')) {
            $this->res['message'] = 'Vui lòng đặt mật khẩu khác';
            return returnResponseError($this->res);
        }
        $regex = '/^(?=.{8,20}$)(?![_])(?!.*[_]{2})[a-zA-Z0-9_]+(?<![_])$/';
        preg_match($regex, $request->username, $data_regex);
        if (!isset($data_regex[0])) {
            $this->res['message'] = "Tên đăng nhập không được chưa các ký tự đặc biệt";
            return returnResponseError($this->res);
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
            $user->api_key = base64_encode($user->id . strtolower('now') . str_rand('25'));
            $user->save();
            $this->res['message'] = 'Đăng ký thành công';
            return returnResponseSuccess($this->res);
        } else {
            $this->res['message'] = 'Tài khoản đã  tồn tại';
            return returnResponseError($this->res);
        }
    }
}

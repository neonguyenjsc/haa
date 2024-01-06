<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\Logs;
use App\Models\Menu;
use App\Models\PaymentMonth;
use App\Models\User;
use App\Models\UsersCoin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //
    protected $menu = 21;

    public function index(Request $request)
    {
        $menu = Menu::find($this->menu);
        $user = User::where(function ($q) use ($request) {
            $key = $request->get('key');
            $id = $request->get('id');
            if ($key) {
                $q->orWhere('username', 'LIKE', '%' . $key . '%');

            }
            if ($id) {
                $q->Where('id', $id);

            }
        })->orderBy('coin', 'DESC')->paginate($request->limit ?? 15);
        return view('Admin.User.index', ['data' => $user, 'menu' => $menu]);
    }

    public function addCoinView(Request $request, $id)
    {
        $menu = Menu::find($this->menu);
        $user = User::find($id);
        return view('Admin.User.add_coin', ['user' => $user, 'menu' => $menu]);
    }

    public function update(Request $request, $id)
    {
        $level = Level::all();
        $menu = Menu::find($this->menu);
        $user = User::find($id);
        return view('Admin.User.detail', ['user' => $user, 'menu' => $menu, 'level' => $level]);
    }


    public function updateAction(Request $request)
    {
        $data = array_filter($request->all());
        if ($request->status == 0) {
            $data['status'] = 0;
        }
        $user = User::find($request->id);
        $old_coin = $user->coin;
        $new_coin = $request->coin;
        if ($user) {
//            if ($request->coin > $user->coin) {
//                return redirectBackError_("Vui lòng chọn chức năng nạp tiền");
//            }
//            if ($request->password) {
//                $data['password'] = Hash::make($request->passowrd);
//            }
            $type = 'in';
            $action = 'add_coin';
            $coin_chenh_lech = 0;
            if ($old_coin < $new_coin) {
                $type = 'in';
                $coin_chenh_lech = $new_coin - $old_coin;
            }
            if ($old_coin > $new_coin) {
                $type = 'out';
                $action = 'deduction';
                $coin_chenh_lech = $old_coin - $new_coin;
            }
            $user->fill($data);
            $user->save();

            Logs::newLogs([
                'user_id' => $user->id,
                'username' => $user->username,
                'client_user_id' => null,
                'client_username' => null,
                'action' => $action,
                'action_coin' => $type,
                'type' => $action,
                'description' => cutString(Auth::user()->username, 5) . ' cập nhật tài khoản của bạn',
                'coin' => $coin_chenh_lech,
                'old_coin' => $old_coin,
                'new_coin' => $new_coin,
                'price_id' => 0,
                'object_id' => null,
                'post_data' => json_encode($request->all()),
                'result' => true,
                'ip' => $request->ip(),
            ]);
            Logs::newLogsAdmin([
                'user_id' => Auth::user()->id,
                'username' => Auth::user()->username,
                'client_user_id' => null,
                'client_username' => null,
                'action' => 'log_admin',
                'action_coin' => 'in',
                'type' => 'log_admin',
                'description' => cutString(Auth::user()->username, 5) . ' cập nhật username ' . $user->username,
                'coin' => 0,
                'old_coin' => 0,
                'new_coin' => 0,
                'price_id' => 0,
                'object_id' => $user->username,
                'post_data' => json_encode($request->all()),
                'result' => true,
                'ip' => $request->ip(),
            ]);
//            if ($request->password) {
//                $user->change_password_at = strtotime('now');
//                $user->password = Hash::make($request->passowrd);
//                $user->api_key = base64_encode($user->username) . str_rand(20) . base64_encode(strtotime('Y-m-d H:i:s'));
//                $user->save();
//            }
            return redirectUrl("/admin/khach-hang/" . $user->id, 'success', []);
        }
        return redirectBackError_("Không tìm thấy user này");
    }


    public function resetPass(Request $request, $user_id)
    {
        $user = User::find($user_id);
        if ($user) {
//            Cache::forget(build_key_cache(['api_key', $user->api_token]));
            $pass = str_rand(6);
            $user->password = Hash::make($pass);
            $user->api_key = base64_encode($user->id . strtolower('now') . str_rand('25'));
            $user->change_password_at = strtotime('now');
            $user->save();
            Logs::newLogsAdmin([
                'user_id' => Auth::user()->id,
                'username' => Auth::user()->username,
                'client_user_id' => null,
                'client_username' => null,
                'action' => 'log_admin',
                'action_coin' => 'in',
                'type' => 'log_admin',
                'description' => cutString(Auth::user()->username, 5) . ' khôi phục mật khẩu  cho username ' . $user->username,
                'coin' => 0,
                'old_coin' => 0,
                'new_coin' => 0,
                'price_id' => 0,
                'object_id' => $user->username,
                'post_data' => json_encode($request->all()),
                'result' => true,
                'ip' => $request->ip(),
            ]);
            $this->res['message'] = "Đã khôi phục mật khẩu thành " . $pass;
            return redirectBackSuccess($this->res['message']);
        } else {
            $this->res['status'] = 400;
            $this->res['message'] = " Không tìm thấy user này";
            return redirectBackError_($this->res['message']);
        }
    }

    public function addCoin(Request $request)
    {
        $user = User::find($request->id);
        if ($user) {
            $quantity = $request->coin;
            $request->admin = Auth::user();
            if ($quantity > 0) {
                if ($this->sumCoin($user->id, $quantity)) {
                    $user->total_recharge = $user->total_recharge + $quantity;
                    $user->save();
                    Logs::newLogs([
                        'user_id' => $user->id,
                        'username' => $user->username,
                        'client_user_id' => null,
                        'client_username' => null,
                        'action' => 'add_coin',
                        'action_coin' => 'in',
                        'type' => 'add_coin',
                        'description' => cutString(Auth::user()->username, 5) . " " . $request->notes . " . Số tiền => " . $quantity,
                        'coin' => $quantity,
                        'old_coin' => $user->coin,
                        'new_coin' => $user->coin + $quantity,
                        'price_id' => 0,
                        'object_id' => null,
                        'post_data' => json_encode($request->all()),
                        'result' => true,
                        'ip' => $request->ip(),
                    ]);
                    Logs::newLogsAdmin([
                        'user_id' => Auth::user()->id,
                        'username' => Auth::user()->username,
                        'client_user_id' => null,
                        'client_username' => null,
                        'action' => 'log_admin',
                        'action_coin' => 'in',
                        'type' => 'log_admin',
                        'description' => 'Bạn đã cộng cho  ' . Auth::user()->username . " => " . $quantity . ' đ .',
                        'coin' => $quantity,
                        'old_coin' => 0,
                        'new_coin' => 0,
                        'price_id' => 0,
                        'object_id' => $user->username,
                        'post_data' => json_encode($request->all()),
                        'result' => true,
                        'ip' => $request->ip(),
                    ]);
                    try {
                        UsersCoin::newUserCoin($user, $quantity, 'in');
                    } catch (\Exception $exception) {
                        $this->sendMessGroupCardToBotTelegram($exception->getMessage() . "\n" . $request->url() . "\n" . json_encode($request->all()));
                    }
                    PaymentMonth::addCoin($user->id, $quantity);
                    return redirectUrl("/admin/khach-hang/" . $user->id, 'success', []);
                }
            } else {
                return redirectBackError_("Vui lòng chọn số tiền lớn hơn 0");
            }
        }
        return redirectBackError_("Không tìm thấy user này");
    }
}

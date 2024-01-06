<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logs;
use App\Models\Menu;
use App\Models\PaymentAuto;
use App\Models\PaymentAutoMomo;
use App\Models\PaymentMonth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class AutoPaymentController extends Controller
{
    //

    protected $menu = 22;

    public function allowUser()
    {
        $user = \request()->user_admin;
        $username = $user->username;
        if (!in_array($username, [
            'giapthanhquoc1126',
            'baostar9999',
            'nguyenthao123',
            'ngocmai123',
            'adminthoai123',
        ])) {
            abort(403);
            return false;
        }
        return true;
    }

    public function index(Request $request)
    {
        $this->allowUser();
        $menu = Menu::find($this->menu);
        return view('Admin.AutoPayment.index', ['menu' => $menu]);
//        if ($request->key == 'momo') {
//            $list = PaymentAutoMomo::where
//        } else {
//
//        }
    }

    public function vcb(Request $request)
    {
        $logs = PaymentAuto::where(function ($q) use ($request) {
            $key = $request->key;
            if ($key) {
                $q->orWhere('username', 'LIKE', '%' . $key . '%');
                $q->orWhere('trans_id', 'LIKE', '%' . $key . '%');
            }
        });
        $data = $this->buildQueryModel($logs, ['key'])->orderBy('id', 'DESC')->paginate($request->limit ?? 100);
        $menu = Menu::find(25);
        return view('Admin.AutoPayment.vcb', ['menu' => $menu, 'data' => $data]);
    }

    public function momo(Request $request)
    {
        $logs = PaymentAutoMomo::where(function ($q) use ($request) {
            $key = $request->key;
            if ($key) {
                $q->orWhere('username', 'LIKE', '%' . $key . '%');
                $q->orWhere('trans_id', 'LIKE', '%' . $key . '%');
            }
        });
        $data = $this->buildQueryModel($logs, ['key'])->orderBy('id', 'DESC')->paginate($request->limit ?? 100);
        $menu = Menu::find(25);
        return view('Admin.AutoPayment.momo', ['menu' => $menu, 'data' => $data]);
    }

    public function addVcbView(Request $request)
    {
        return view('Admin.AutoPayment.addVcb');
    }

    public function addMomoView(Request $request)
    {
        return view('Admin.AutoPayment.addMomo');
    }

    public function addVcb(Request $request)
    {
        $request->user = Auth::user();
        $validate = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validate->fails()) {
            return redirectBackError($validate->errors());
        }
        $user = User::where('username', $request->username)->first();
        if ($user) {
            $request->id = strtotime('now');
            $payment = PaymentAuto::where('trans_id', $request->id)->first();
            if (!$payment) {
                $array_so_tham_chieu = strtotime('now') . " - " . strtotime('now');
                $trans_id_start = $array_so_tham_chieu[0];
                $trans_id_end = intval($array_so_tham_chieu[1]);
                $coin = abs(intval($request->coin));
                if ($coin > 0) {
                    if ($this->sumCoin($user->id, $coin)) {
                        $key_catch_duli_payment = 'auto_payment' . $request->username . $coin;
                        Cache::remember($key_catch_duli_payment, 60 * 60 * 12, function () {
                            return true;
                        });
                        $user->level = PaymentMonth::getLevelMonth($user->id, $coin);
                        $user->total_recharge = intval($user->total_recharge) + abs(intval($coin));
                        $user->save();
                        $form_data = [
                            'trans_id' => $request->id,
                            'username' => $user->username,
                            'user_id' => $user->id,
                            'coin' => $coin,
                            'status' => 1,
                            'description' => "Hệ thống VIETCOMBANK tự động cộng cho bạn " . number_format($coin),
                            'date' => date('Y-m-d'),
                            '_id' => strtotime('now'),
                            'post_data' => json_encode($request->all()),
                            'trans_id_start' => $trans_id_start,
                            'trans_id_end' => $trans_id_end,
                        ];
                        PaymentAuto::newPayment($form_data);
                        Logs::newLogs([
                            'user_id' => $user->id,
                            'username' => $user->username,
                            'client_user_id' => null,
                            'client_username' => null,
                            'action' => 'add_coin',
                            'action_coin' => 'in',
                            'type' => 'add_coin',
                            'description' => "Hệ thống VIETCOMBANK tự động cộng cho bạn " . number_format($coin),
                            'coin' => $coin,
                            'old_coin' => $user->coin,
                            'new_coin' => $user->coin + $coin,
                            'price_id' => 0,
                            'object_id' => null,
                            'post_data' => json_encode($request->all()),
                            'result' => true,
                            'ip' => '',
                        ]);
                        PaymentMonth::addCoin($user->id, $coin);
                        return redirectBackSuccess("Thêm giao dịch thành công");
                    }
                    return redirectBackError_("Lỗi cộng tiền");
                }
                return redirectBackError_("Số tiền phải lớn hơn 0");
            } else {
                return redirectBackError_("Mã giao dịch này đã tồn tại");
            }
        } else {
            return redirectBackError_("Không tìm thấy thằng này");
        }
    }

    public function addMomo(Request $request)
    {
        $request->user = Auth::user();
        $validate = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validate->fails()) {
            return redirectBackError($validate->errors());
        }
        $user = User::where('username', $request->username)->first();
        if ($user) {
            $payment = PaymentAutoMomo::where('trans_id', $request->id)->first();
            if (!$payment) {
                $coin = abs(intval($request->coin));
                if ($coin > 0) {
                    if ($this->sumCoin($user->id, $coin)) {
                        $user->level = PaymentMonth::getLevelMonth($user->id, $coin);
                        $user->total_recharge = intval($user->total_recharge) + abs(intval($coin));
                        $user->save();
                        PaymentAutoMomo::newPayment([
                            'user_id' => $user->id,
                            'username' => $user->username,
                            'status' => 1,
                            'description' => "Hệ thống Momo tự động cộng cho bạn " . number_format($coin),
                            'amount' => $coin,
                            'trans_id' => $request->id,
                            'phone' => '',
                            'date' => date('Y-m-d'),
                            'syntax' => '',
                        ]);
                        Logs::newLogs([
                            'user_id' => $user->id,
                            'username' => $user->username,
                            'client_user_id' => null,
                            'client_username' => null,
                            'action' => 'add_coin',
                            'action_coin' => 'in',
                            'type' => 'add_coin',
                            'description' => "Hệ thống Momo tự động cộng cho bạn " . number_format($coin),
                            'coin' => $coin,
                            'old_coin' => $user->coin,
                            'new_coin' => $user->coin + $coin,
                            'price_id' => 0,
                            'object_id' => null,
                            'post_data' => json_encode($request->all()),
                            'result' => true,
                            'ip' => '',
                        ]);
                        PaymentMonth::addCoin($user->id, $coin);
                        return redirectBackSuccess("Thêm giao dịch thành công");
                    }
                    return redirectBackError_("Lỗi cộng tiền");
                }
                return redirectBackError_("Số tiền phải lớn hơn 0");
            } else {
                return redirectBackError_("Mã giao dịch này đã tồn tại");
            }
        } else {
            return redirectBackError_("Không tìm thấy thằng này");
        }
    }
}

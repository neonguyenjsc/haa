<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logs;
use App\Models\LogsCoin;
use App\Models\Menu;
use App\Models\NotificationUser;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RefundController extends Controller
{
    //
    public function multiRefund(Request $request)
    {
        $str = "";
        foreach ($request->id as $id) {
            $refund = Refund::where('id', $id)->whereIn('status', [-3, 0])->first();
            if ($refund) {
                $refund->status = 1;
                if ($refund->save()) {
                    $user = User::find($refund->user_id);
                    $old_coin = $user->coin;
                    if ($this->SumCoin($user->id, $refund->coin)) {
                        $user->total_recharge = $user->total_recharge + $refund->coin;
                        $user->save();
                        Logs::newLogs([
                            'user_id' => $refund->user_id,
                            'username' => $refund->username,
                            'client_user_id' => null,
                            'client_username' => null,
                            'action' => 'refund',
                            'action_coin' => 'in',
                            'type' => 'refund',
                            'description' => $refund->description,
                            'coin' => $refund->coin,
                            'old_coin' => $old_coin,
                            'new_coin' => $old_coin + $refund->coin,
                            'price_id' => 0,
                            'object_id' => $refund->object_id,
                            'post_data' => json_encode($request->all()),
                            'result' => true,
                            'ip' => $request->ip(),
                        ]);
                        try {
                            $this->refundAgency($refund->toArray());
                        } catch (\Exception $exception) {
                        }
                    }
                }
                $str = $str . "Hoàn thành công cho id hoàn " . $id . " số tiền " . number_format($refund->coin) . "</br>";
            } else {
                $str = $str . "Hoàn THẤT BẠI cho id hoàn " . $id . " Lý do => Đã hoàn tiền</br>";
            }
        }
        return redirectBackSuccess($str);
    }

    public function index(Request $request)
    {
        $menu = Menu::find(30);
        $status = $request->query('status') ?? -3;
        $refund = Refund::where('status', $status)->where(function ($q) use ($request) {
            $key = $request->query('key');
            if ($key) {
                $q->orWhere('username', 'LIKE', '%' . $key . '%');
                $q->orWhere('object_id', 'LIKE', '%' . $key . '%');
                $q->orWhere('client_username', 'LIKE', '%' . $key . '%');
                $q->orWhere('status', 'LIKE', '%' . $key . '%');
                $q->orWhere('orders_id', 'LIKE', '%' . $key . '%');
            }
        })->orderBy('id', 'desc')->paginate(50);
        return view('Admin.Refund.index', ['data' => $refund, 'status' => $request->query('status') ?? -1, 'menu' => $menu]);
    }

    public function refund(Request $request, $id)
    {
        $refund = Refund::where('id', $id)->whereIn('status', [-3, 0])->first();
        if ($refund) {
            if ($refund->status == -3) {
                $refund->buy_error = 1;
            }
            $refund->status = 1;
            if ($refund->save()) {
                $user = User::find($refund->user_id);
                $old_coin = $user->coin;
                if ($this->SumCoin($user->id, $refund->coin)) {
                    $user->total_recharge = $user->total_recharge + $refund->coin;
                    $user->save();
                    Logs::newLogs([
                        'user_id' => $refund->user_id,
                        'username' => $refund->username,
                        'client_user_id' => null,
                        'client_username' => null,
                        'action' => 'refund',
                        'action_coin' => 'in',
                        'type' => 'refund',
                        'description' => $refund->description,
                        'coin' => $refund->coin,
                        'old_coin' => $old_coin,
                        'new_coin' => $old_coin + $refund->coin,
                        'price_id' => 0,
                        'object_id' => $refund->object_id,
                        'post_data' => json_encode($request->all()),
                        'result' => true,
                        'ip' => $request->ip(),
                    ]);
                    try {
                        $this->refundAgency($refund->toArray());
                    } catch (\Exception $exception) {
                    }
                }
            }
            return redirect()->back()->with(['success' => 'Đã hoàn tiền']);
        } else {
            return redirect()->back()->with(['error_' => 'Có vẻ id này đã hoàn tiền']);
        }
    }

    public function delete(Request $request, $id)
    {
        Refund::where('id', $id)->delete();
        return redirectBackSuccess("Đã xóa");
    }
}

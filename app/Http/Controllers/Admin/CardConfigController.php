<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfigCard;
use App\Models\LogPayment;
use App\Models\Logs;
use App\Models\PaymentMonth;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CardConfigController extends Controller
{
    //

    public function index(Request $request)
    {
        $config = ConfigCard::all();
        $logs = LogPayment::where(function ($q) use ($request) {
            $key = $request->query('key');
            if ($request->query('key')) {
                $q->orWhere('username', 'LIKE', '%' . $key . '%');
                $q->orWhere('username_active', 'LIKE', '%' . $key . '%');
                $q->orWhere('type', 'LIKE', '%' . $key . '%');
            }
        })->orderBy('id', 'DESC')->paginate(100);
        return view('Admin.Card.index', ['config' => $config, 'data' => $logs]);
    }

    public function active(Request $request, $id)
    {
        $logs = LogPayment::where('id', $id)->where('status', 1)->first();
        if ($logs) {
            if ($request->query('status') == 2) {
                $wrong_value = 0;
                if ($request->query('coin')) {
                    $wrong_value = 1;
                    $coin = $request->query('coin');
                } else {
                    $coin = abs(intval($logs->amount - ($logs->amount * $logs->charge / 100)));
                }
                $logs->status = 1;
                $user = User::find($logs->user_id);
                if ($this->sumCoin($user->id, $coin)) {

                    $user->level = PaymentMonth::getLevelMonth($user->id, $coin);
                    $user->total_recharge = $user->total_recharge + $coin;
                    $user->save();

                    $logs->description = 'Nạp tự động thành công';
                    $logs->status = 2;
                    $logs->type = 'handmade';
                    $logs->wrong_value = $wrong_value;
                    $logs->username_active = Auth::user()->username;
                    $logs->real_coin = $coin;
                    $coin_real = $request->query('coin') ?? $coin;
                    $logs->coin_back = abs(intval($coin_real - ($coin_real * 20 / 100)));
                    $logs->save();
                    Logs::newLogs([
                        'user_id' => $user->id,
                        'username' => $user->username,
                        'client_user_id' => null,
                        'client_username' => null,
                        'action' => 'add_coin',
                        'action_coin' => 'in',
                        'type' => 'add_coin',
                        'description' => 'Hệ thống nạp thẻ tự động cộng cho bạn  ' . $coin,
                        'coin' => $coin,
                        'old_coin' => $user->coin,
                        'new_coin' => $user->coin + $coin,
                        'price_id' => 0,
                        'object_id' => null,
                        'post_data' => json_encode($request),
                        'result' => true,
                        'ip' => '',
                    ]);
//                    return redirectBackSuccess("Duyệt thành công");
                    return redirectUrl('/admin/card-config', 'success', 'Duyệt thành công');
                }
            } else {
                $logs->status = 0;
                $logs->description = 'Thẻ sai hoặc đã sữ dụng';
                $logs->username_active = Auth::user()->username;
                $logs->time_active = date('Y-m-d  H:i:s');
                $logs->save();
                return redirectBackError_("Đã cập nhật thẻ sai");
            }
        } else {
            return redirectBackError_("Thẻ ko có hoạc đã duyệt");
        }
    }


    public function config(Request $request)
    {
        $config = ConfigCard::all();
        $start_month = new Carbon('first day of this month');
        $end_month = new Carbon('last day of this month');
        $coin_month = DB::table('logs_payment')->where('type', 'handmade')
            ->whereBetween('created_at', [$start_month->toDateTimeString(), $end_month->toDateTimeString()])
            ->groupBy('username_active')
            ->selectRaw('sum(amount) as sum, username_active')
            ->pluck('sum', 'username_active')
            ->toArray();
        $start_last_month = new Carbon('first day of last month');
        $end_last_month = new Carbon('last day of last month');
        $coin_last_month = DB::table('logs_payment')->where('type', 'handmade')
            ->whereBetween('created_at', [$start_last_month->toDateTimeString(), $end_last_month->toDateTimeString()])
            ->groupBy('username_active')
            ->selectRaw('sum(amount) as sum, username_active')
            ->pluck('sum', 'username_active')
            ->toArray();
        return view('Admin.Card.config', ['config' => $config, 'last_month' => $coin_last_month, 'month' => $coin_month]);
    }

    public function update(Request $request)
    {
        foreach ($request->id as $i => $item) {
            $card = ConfigCard::find($item);
            $card->auto = $request->auto[$i];
            $card->charge = $request->charge[$i];
            $card->save();
        }
        return redirectBackSuccess("Dã cập nhật");
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logs;
use App\Models\LogsCoin;
use App\Models\Menu;
use App\Models\Prices;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

//use App\Models\Prices\Prices;

class StatisticsController extends Controller
{
    //

    public function allowUser()
    {
        $user = \request()->user_admin;
        $username = $user->username;
        if (!in_array($username, [
            'giapthanhquoc1126',
            'baostar9999',
        ])) {
            abort(403);
            return false;
        }
        return true;
    }

    public function log(Request $request)
    {
        $data = Logs::where('id', '>', 2000000)->where(function ($q) use ($request) {
            $key = $request->key;
            if ($key) {
                $q->where('action', $request->key);
            }
            $package_name = $request->package_name;
            if ($package_name) {
                $q->where('package_name', $package_name);
            }
            $user_id = $request->user_id;

            if ($user_id) {
                $q->where('user_id', $user_id);
            }
            if (isset($request->s) && isset($request->e)) {
                $q->whereBetween('created_at', $request->only('s', 'e'));
            }
        })->orderBy('id', 'DESC')->get();
        return view('Admin.Statistic.logs', ['data' => $data]);
    }

    public function index(Request $request)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2048M');
        switch ($request->key) {
            case 'this_month':
                $now = Carbon::now();
                $monthStartDate = $now->startOfMonth()->format('Y-m-d 00:00:00');
                $monthEndDate = $now->endOfMonth()->format('Y-m-d 23:59:59');
                $date = [
                    $monthStartDate,
                    $monthEndDate,
                ];
                break;
            case 'last_month':
                $monthStartDate = Carbon::parse('first day of previous month')->format('Y-m-d 00:00:00');
                $monthEndDate = Carbon::parse('last day of previous month')->format('Y-m-d 23:59:59');
                $date = [
                    $monthStartDate,
                    $monthEndDate,
                ];
                break;
            case 'this_week':
                $now = Carbon::now();
                $weekStartDate = $now->startOfWeek()->format('Y-m-d 00:00:00');
                $weekEndDate = $now->endOfWeek()->format('Y-m-d 23:59:59');
                $date = [
                    $weekStartDate,
                    $weekEndDate,
                ];
                break;
            case 'last_week':
                $now = Carbon::now();
                $weekStartDate = $this->addDaysWithDate($now->startOfWeek()->format('Y-m-d 00:00:00'), -7);
                $weekEndDate = $now->startOfWeek()->format('Y-m-d 23:59:59');
                $date = [
                    $weekStartDate,
                    $weekEndDate,
                ];
                break;
            case 'yesterday':
                $date = [
                    $this->addDaysWithDate(date('Y-m-d 00:00:00'), -1),
                    $this->addDaysWithDate(date('Y-m-d 23:59:59'), -1),
                ];
                break;
            default:
                $date = [
                    date('Y-m-d 00:00:00'),
                    date('Y-m-d 23:59:59'),
                ];
                break;
        }
        $total_coin_out = Logs::whereBetween('created_at', $date)->where('type', 'out')->where(function ($q) use ($request) {
            if (isset($request->user_id)) {
                $q->where('user_id', $request->user_id);
            }
        })->sum('coin');
        $total_coin_in = Logs::whereBetween('created_at', $date)->where(function ($q) use ($request) {
            if (isset($request->user_id)) {
                $q->where('user_id', $request->user_id);
            }
        })->where('action_coin', 'in')->where('action', 'add_coin')->sum('coin');

        $total_refund = Logs::whereBetween('created_at', $date)->where(function ($q) use ($request) {
            if (isset($request->user_id)) {
                $q->where('user_id', $request->user_id);
            }
        })->where('action', 'refund')->sum('coin');
        return view('Admin.Statistic.index', [
            'total_out' => $total_coin_out,
            'total_in' => $total_coin_in,
//            'create_order' => $total_create_order,
            'create_refund' => $total_refund,
//            'total_deduction' => $total_deduction,
            'date' => $date,
            'user' => User::find($request->user_id),
            'key' => $request->key ?? 'today',
        ]);
    }

    public function detail(Request $request)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2048M');
        $type = $request->type;
        $date = $request->only('s', 'e');
        switch ($type) {
            default:
                $logs = Logs::whereBetween('created_at', $date)->where('type', 'out')->where(function ($q) use ($request) {
                    if ($request->user_id) {
                        $q->where('user_id', $request->user_id);
                    }
                })->get();
                $group = [];
                foreach ($logs as $i => $item) {
//                    echo $item->action . "\n";
                    if (!array_key_exists($item->action, $group)) {
                        $group[$item->action] = [
                            'coin' => $item->coin,
                            'name' => $item->type_str,
                            'key' => $item->action,
                        ];
                    } else {
                        $group[$item->action]['coin'] = $group[$item->action]['coin'] + $item->coin;
                    }
                }
                return view('Admin.Statistic.coin_out', [
                    'data' => $group,
                    'date' => $date,
                    'user' => User::find($request->user_id)
                ]);
                break;
        }
    }

    public function detailCreateJobs(Request $request)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2048M');
        $date = $request->only('s', 'e');
        $logs = Logs::whereBetween('created_at', $date)->where(function ($q) use ($request) {
            if ($request->user_id) {
                $q->where('user_id', $request->user_id);
            }
        })->where('action', 'buy')->where('package_name', '<>', null)->orderBy('package_name', 'ASC')->get();
        $data = [];
        foreach ($logs as $i => $item) {
            if ($item->package_name) {
                $package_name = $item->package_name;
                if (!array_key_exists($package_name, $data)) {
                    $prices = Prices::where('package_name', $item->package_name)->first();
                    $menu = Menu::find($prices->menu_id);
                    $data[$item->package_name] = [
                        'coin' => $item->coin,
                        'key' => $item->package_name,
                        'prices' => $prices,
                        'menu' => $menu,
                        'total' => 1,
                    ];
                } else {
                    $data[$item->package_name]['coin'] = $data[$item->package_name]['coin'] + $item->coin;
                    $data[$item->package_name]['total'] = $data[$item->package_name]['total'] + 1;
                }
            }
        }
//        $this->sendTotalToGroup($str);
//        $this->sendTotalToGroup($str . "\n--------------\n" . "date => " . $date['s'] . "
//        \ntlc => $total_tlc | $tlc\n
//        2fa => $total_shop2fa | $shop2fa\n
//        autolike => $total_autolike_cc | $autolikecc\n
//        mfb => $total_mfb  | $mfb\n
//        ctv_biz => $total_ctv_biz \n
//        sabomo => $total_sabomo \n
//        buffviewer => $total_buffviewer \n
//        sbook => $total_sbook | $sbook\n
//        viewadbreak => $total_viewadbreak \n
//        new97 => $total_new97 \n
//        viewyt => $total_viewyt \n
//
//        ");
        return view('Admin.Statistic.coin_out_detail_order', [
            'data' => $data,
            'date' => $date,
            'user' => User::find($request->user_id),
        ]);
    }

//    public function
}

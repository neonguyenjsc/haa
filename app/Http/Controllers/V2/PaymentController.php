<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\ConfigCard;
use App\Models\LogPayment;
use App\Models\Logs;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PaymentController extends Controller
{
    //

    public function getListPayment(Request $request)
    {
        $key_cache = 'get_payment';
        $this->res['data'] = Cache::rememberForever($key_cache, function () {
            return Payment::all();
        });
        return returnResponseSuccess($this->res);
    }

    public function getRatePaymentCard(Request $request)
    {
        $key_cache = 'get_rate_payment_card';
        $this->res['data'] = Cache::rememberForever($key_cache, function () {
            return ConfigCard::where('status', 1)->get();
        });
        return returnResponseSuccess($this->res);
    }

    public function getLogsCard(Request $request)
    {
        $logs_card = LogPayment::select(['serial', 'card', 'amount', 'description', 'status', 'real_coin', 'created_at'])->where('user_id', $request->user->id)
            ->orderBy('id', 'desc')
            ->take(100)->get();
        $this->res['data'] = $logs_card;
        return returnResponseSuccess($this->res);
    }

    public function getLogs(Request $request)
    {
        $log = Logs::select([
            'coin',
            'new_coin',
            'old_coin',
            'description',
        ])->where('user_id', $request->user->id)->where('type', 'add_coin')->where('action_coin', 'in')->orderBy('id', 'desc')->get();
        $this->res['data'] = $log;
        return returnResponseSuccess($this->res);
    }
}

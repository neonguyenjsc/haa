<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logs;
use App\Models\Payment;
use Illuminate\Filesystem\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class  PaymentConfigController extends Controller
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

    public function index()
    {
        $this->allowUser();
        $pay = Payment::all();
        \Illuminate\Support\Facades\Cache::forget('get_payment');
        return view('Admin.Payment.index', ['data' => $pay]);
    }

    public function remove($id)
    {
        Payment::where('id', $id)->delete();
        return redirectBackSuccess("xóa thành công");
    }

    public function addView()
    {
        return view('Admin.Payment.add');
    }

    public function add(Request $request)
    {
        $data = $request->all();
        Payment::newPayment($data);
        Logs::newLogsAdmin([
            'user_id' => Auth::user()->id,
            'username' => Auth::user()->username,
            'client_user_id' => null,
            'client_username' => null,
            'action' => 'log_admin',
            'action_coin' => 'in',
            'type' => 'log_admin',
            'description' => 'Thêm thông tin nạp tiền',
            'coin' => 0,
            'old_coin' => 0,
            'new_coin' => 0,
            'price_id' => 0,
            'object_id' => null,
            'post_data' => json_encode($request->all()),
            'result' => true,
            'ip' => $request->ip(),
        ]);
        return redirectBackSuccess('Thêm thành công');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logs;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromotionController extends Controller
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

    public function index(Request $request)
    {
        $this->allowUser();
        $promo = Promotion::first();
        return view('Admin.Promotion.index', ['data' => $promo]);
    }

    public function update(Request $request)
    {
        $promo = Promotion::find($request->id);
        $promo->fill($request->all());
        $promo->save();
        Logs::newLogsAdmin([
            'user_id' => Auth::user()->id,
            'username' => Auth::user()->username,
            'client_user_id' => null,
            'client_username' => null,
            'action' => 'log_admin',
            'action_coin' => 'in',
            'type' => 'log_admin',
            'description' => 'Thêm thông tin khuyến mãi',
            'coin' => 0,
            'old_coin' => 0,
            'new_coin' => 0,
            'price_id' => 0,
            'object_id' => null,
            'post_data' => json_encode($request->all()),
            'result' => true,
            'ip' => $request->ip(),
        ]);
        return redirectBackSuccess("Cập nhật thành công");
    }
}

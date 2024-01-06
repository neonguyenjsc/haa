<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\SystemNotify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class NotifyController extends Controller
{
    //
    protected $menu_id = 26;

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
        Cache::forget('key_cache_notify_system');
        $notify = SystemNotify::all();
        return view('Admin.SystemNotify.index', [
            'data' => $notify,
            'menu' => Menu::find($this->menu_id)
        ]);
    }

    public function remove($id)
    {
        SystemNotify::where('id',$id)->delete();
        return redirectBackSuccess("Xóa thành công");
    }

    public function addView()
    {
        Cache::forget('key_cache_notify_system');
        return view('Admin.SystemNotify.add');
    }

    public function add(Request $request)
    {
        SystemNotify::newNotify($request->all());
        Cache::forget('key_cache_notify_system');
        return redirectBackSuccess("Tạo thành công");
    }
}

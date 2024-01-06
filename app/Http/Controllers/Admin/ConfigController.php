<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\TokenCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConfigController extends Controller
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
        $config = Config::all();
        $token = TokenCheck::count();
        return view('Admin.Config.index', ['data' => $config, 'token' => $token]);
    }

    public function update(Request $request)
    {
        foreach ($request->id as $i => $item) {
            $config = Config::find($item);
            $config->value = $request->value[$i];
            $config->save();
        }
        return redirectBackSuccess("Update success");
    }

    public function updateToken(Request $request)
    {
        $token = $request->get('token') ?? '';
        $data = explode("\n", $token);
        foreach ($data as $item) {
            $token = new TokenCheck();
            $token->value = str_replace("\r", "", str_replace("\n", "", $item));
            $token->save();
        }
        return redirect()->back()->with(['success' => 'Thành công']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    //

    public function index(Request $request)
    {
        return view('Profile.index');
    }

    public function update(Request $request)
    {
        $data = $request->only('email', 'name', 'phone_number', 'avatar');
        if ($request->new_password && ($request->new_password != $request->confirm_password)) {
            return redirectBackError_("Mật khẩu mới nhập lại không đúng");
        }
        $user = User::find(Auth::user()->id);
        if ($user) {
            if ($request->old_password) {
                if (!Hash::check($request->old_password, $user->password)) {
                    return redirectBackError_("Mật khẩu cũ không khớp");
                } else {
                    $data['password'] = Hash::make($request->new_password);
                }
            }

            $user->fill($data);
            $user->save();
            if ($request->old_password) {
                $user->change_password_at = strtotime('now');
                $user->api_key = base64_encode($user->id . strtolower('now') . str_rand('25'));
                $user->save();
                Auth::logout();
            }
            return redirectBackSuccess("Cập nhật thành công");
        }

        return redirectBackError_("User not found");
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MenuController extends Controller
{
    //

    protected $menu = 25;

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
        $menu = Menu::whereNotIn('category_id', [1, 2])->where('status', 1)->get();
        return view(
            'Admin.Menu.index', ['data' => $menu, 'menu' => Menu::find($this->menu)]
        );
    }

    public function detail($id)
    {
        $menu = Menu::find($id);
        return view(
            'Admin.Menu.detail', ['data' => $menu, 'menu' => Menu::find($this->menu)]
        );
    }

    public function update(Request $request)
    {
        $menu = Menu::find($request->id);
        $menu->name = $request->name;
        $menu->notes = $request->notes;
        $menu->guide = $request->guide;
        $menu->status = $request->status;
        $menu->save();
        Cache::forget('category_v2_user');
        Cache::forget('category_v2_admin');
        return redirectBackSuccess("Đã cập nhật");
    }
}

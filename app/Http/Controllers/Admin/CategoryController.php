<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Menu;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    //
    protected $menu = 22;
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
        $menu = Menu::find($this->menu);
        $list = Category::where('status', 1)->get();
        return view('Admin.Category.index', ['data' => $list, 'menu' => $menu]);
    }
}

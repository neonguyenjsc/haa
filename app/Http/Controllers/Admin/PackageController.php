<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\Logs;
use App\Models\Menu;
use App\Models\Prices;
use App\Models\PricesConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PackageController extends Controller
{

    protected $menu_id = 24;

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
//        $this->allowUser();
//        $menu = Menu::find($this->menu_id);
//        $prices = Prices::where('active', 1)->with('menu')->orderBy('menu_id', 'ASC')->paginate(100);
//        return view('Admin.Package.index', ['data' => $prices, 'menu' => $menu]);
        $menu = Menu::find($this->menu_id);
        $list = Menu::where('id', '>', 40)->where('status', 1)->get();
        return view('Admin.Package.menu', ['data' => $list, 'menu' => $menu]);
    }

    public function package(Request $request, $menu_id)
    {
        $this->allowUser();
        $menu = Menu::find($this->menu_id);
        $prices = Prices::where('active', 1)->where('menu_id', $menu_id)->with('menu')->orderBy('menu_id', 'ASC')->paginate(100);
        return view('Admin.Package.index', ['data' => $prices, 'menu' => $menu]);
    }

    public function detail($id)
    {
        $menu = Menu::find($this->menu_id);
        return view('Admin.Package.detail', [
            'data' => Prices::where('id', $id)->where('active', 1)->with('menu')->first(),
            'menu' => $menu
        ]);
    }

    public function prices($id)
    {
        $prices = PricesConfig::where('price_id', $id)->whereIn('level_id', [1, 2, 3, 6])->with(['level', 'menu'])->get();
        $menu = Menu::find($this->menu_id);

        return view('Admin.Package.prices', [
            'data' => $prices,
            'menu' => $menu
        ]);
    }

    public function update(Request $request)
    {
        $package = Prices::find($request->id);
        $package->name = $request->name;
        $package->description = $request->description;
        $package->min = $request->min;
        $package->max = $request->max;
        $package->status = $request->status;
        $package->sort = $request->sort;
        $package->message = $request->message;
        $package->notes = $request->notes;
        $package->package_name_master = $request->package_name_master;
        $package->save();
        if ($package->save()) {
            PricesConfig::where('price_id', $package->id)->update([
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status,
                'package_name' => $package->package_name,
                'active' => $package->active,
                'sort' => $package->sort,
                'menu_id' => $package->menu_id,
                'notes' => $package->notes,
                'message' => $package->message,
            ]);
            $level = Level::pluck('id')->toArray();
            foreach ($level as $itemLevel) {
                $key_cache = 'get_package_' . $package->menu_id . '_' . $itemLevel;
                Cache::forget($key_cache);
            }
        }
        Logs::newLogsAdmin([
            'user_id' => Auth::user()->id,
            'username' => Auth::user()->username,
            'client_user_id' => null,
            'client_username' => null,
            'action' => 'log_admin',
            'action_coin' => 'in',
            'type' => 'log_admin',
            'description' => 'Cập nhật giá',
            'coin' => 0,
            'old_coin' => 0,
            'new_coin' => 0,
            'price_id' => 0,
            'object_id' => null,
            'post_data' => json_encode($request->all()),
            'result' => true,
            'ip' => $request->ip(),
        ]);
        return redirectBackSuccess("Đã cập nhật");
    }

    public function updatePrices(Request $request)
    {
        foreach ($request->id as $i => $item) {
            $p = PricesConfig::where('id', $item)->first();
            $p->prices = floatval($request->prices[$i]);
            $p->save();
        }
        $level = Level::pluck('id')->toArray();
        foreach ($level as $itemLevel) {
            $key_cache = 'get_package_' . $p->menu_id . '_' . $itemLevel;
            Cache::forget($key_cache);
        }
        return redirectBackSuccess("Đã cập nhật");
    }
}

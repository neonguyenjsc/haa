<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\Prices;
use App\Models\PricesConfig;
use App\Models\SystemNotify;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    //

    public function getNotifySystem()
    {
        $key = 'key_cache_notify_system';
        $system = SystemNotify::where('id', '>', 1)->orderBy('id', 'desc')->take(3)->get();
        $data = [];
        foreach ($system as $i => $item) {
            $data[$i] = [
                'content' => $item->content,
                'created_at' => strtotime($item->created_at),
                'id' => $item->id,
                'img' => $item->img,
                'title' => $item->title,
                'updated_at' => strtotime($item->updated_at),
            ];
        }
        $this->res['data'] = $data;
        return returnResponseSuccess($this->res);
    }

    public function getNotifyUser(Request $request)
    {
        $user = $request->user;
        $data = DB::table('notify_user')->where('user_id', $user->id)->orderBy('id', 'desc')->take(5)->get();
        $this->res['data'] = $data;
        return returnResponseSuccess($this->res);
    }

    public function getCategoryHome()
    {
        $key_cache = 'key_cache_menu_home';
        $this->res['data'] = Cache::rememberForever($key_cache, function () {
            $lable = DB::table('menu_home_lable')->orderBy('sort', 'asc')->get();
            foreach ($lable as $item) {
                $item->menu = DB::table('menu_home_item')->where('label_id', $item->id)->orderBy('sort', 'asc')->get();
            }
            return $lable;

        });

        return returnResponseSuccess($this->res);
    }

    public function getPackage(Request $request)
    {
        $menu_id = $request->id ?? 0;
        if ($menu_id == 0) {
            $this->res['message'] = "Không tìm thấy dịch vụ nào thích hợp";
            return returnResponseError($this->res);
        }
        $key = $request->header('api-key');
        $level = 1;
        if ($key != null && $key != 'null') {
            $user = User::where('api_key', $key)->where('status', 1)->first();
            $level = $user->level;
        }
        $key_cache = 'get_package_' . $menu_id . '_' . $level;

        $data = Cache::rememberForever($key_cache, function () use ($request, $menu_id, $level) {
            $package = PricesConfig::getPricesByLevel($menu_id, $level);
            $data = [];
            foreach ($package as $i => $item) {
                $data[$i] = [
                    'level_id' => $item->level_id,
                    'name' => $item->name,
                    'id' => $item->id,
                    'prices' => $item->prices,
                    'message' => $item->message ?? '',
                    'notes' => $item->notes,
                    'package_name' => $item->package_name,
                    'notes_base64' => base64_encode($item->notes)
                ];
            }
            return $data;
        });
//        if (in_array($menu_id, [48, 49])) {
//            $sl = $this->buffViewerService->getSL();
//            if (is_int($sl->amount_livestream_unit_limited_available)) {
//                foreach ($data as $i => $item) {
//                    if ($data[$i]['package_name'] == 'facebook_eyes') {
//                        $data[$i]['message'] = $data[$i]['message'] . " " . "<span class='badge bg-success text-white'>Số mắt có thể mua :" . number_format($sl->amount_livestream_unit_limited_available ?? 0) . "</span>";
//                    }
//                    if ($data[$i]['package_name'] == 'facebook_view_2') {
//                        $data[$i]['message'] = $data[$i]['message'] . " " . "<span class='badge bg-success text-white'>Số mắt có thể mua :" . number_format($sl->number_available_order_buff_view_combo_600k_mins_60k_live ?? 0) . "</span>";
//                    }
//                    if ($data[$i]['package_name'] == 'facebook_view_20') {
//                        $data[$i]['message'] = $data[$i]['message'] . " " . "<span class='badge bg-success text-white'>Số mắt có thể mua :" . number_format($sl->number_available_order_buff_view_60k_100k_mins ?? 0) . "</span>";
//                    }
//                }
//            }
//        }
        $this->res['data'] = $data;
        return returnResponseSuccess($this->res);
    }

    public function getPriceFollowCheap(Request $request)
    {
        $prices = Prices::where('menu_id', $request->id)->where('status', 1)->where('active', 1)->get();
        return $prices;
    }

    public function getSL()
    {
        $key = Config::where('alias', 'api_viewer')->first();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://buffviewer.com/api/order/getavailableamount",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: " . $key->value,
                "Cookie: __cfduid=d61048cf504a74348ce85838c1ad95cc41601520751; XSRF-TOKEN=eyJpdiI6IkFaRkhNQkd1TGU4YVFvTjJaR0hBWnc9PSIsInZhbHVlIjoiK1JSN1dIS1VFOSthMkQ5WmNPRnprOFJpMTkxSVpjY1FuVU91ZlYrS2E2RFdPNyt6T0pjUE01ZkN0VG83ZFBMWSIsIm1hYyI6IjdjNWEwNWQ5OWZiYTMwYzBiNTA5OWNkNjg0MjgwYjcxYTQ3ZWJmYjVhZjFjMGZhNzgwNmYzZGIwNmE0NDNmMzYifQ%3D%3D; buff_viewer_session=eyJpdiI6ImVkOVdjclRhWlBUbWdySVE3c29xclE9PSIsInZhbHVlIjoicThiU0U2aDFwWHoyOGEzUk9NWTJQNjZNb1V3SDJPSjhhVk9rd1pJb29WMjJcL0poQUFhUklnMzB0VDd3ZU8wN3YiLCJtYWMiOiIxYjI2YzBkMTI1ZjUxYzQ5YWI4MjNmMWE0ZjNlOGI3MTc2YmUyOGJiZmM4Y2Y1MmNkOGNlMWQ3ZDViNjQ5ODNmIn0%3D"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }
}

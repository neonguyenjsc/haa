<?php

namespace App\Http\Controllers\Ads;

use App\Http\Controllers\Controller;
use App\Models\Ads\TikTok\TikTok;
use App\Models\Logs;
use App\Models\Menu;
use App\Models\Prices;
use App\Models\PricesConfig;
use App\Models\UsersCoin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TikTokFreeController extends Controller
{
    protected $menu_id = 125;

    public function index()
    {
        $user = Auth::user();
        $menu = Menu::find($this->menu_id);
        $package = PricesConfig::getPricesByLevel($this->menu_id, $user->level);
        return view('Ads.TikTok.Free.index', ['menu' => $menu, 'package' => $package]);
    }

    public function history(Request $request)
    {
        $menu = Menu::find($this->menu_id);
        $data = $this->getHistory('tiktok', $this->menu_id, $request);
        return view('Ads.TikTok.Free.history', ['data' => $data, 'menu' => $menu]);
    }

    public function buy(Request $request)
    {
        return $this->returnActionWeb($this->actionBuy($request, Auth::user()));
    }

    public function buyApi(Request $request)
    {
        return $this->returnActionApi($this->actionBuy($request, $request->user));
    }

    public function actionBuy($request, $user)
    {
        set_time_limit(120);
        $validate = Validator::make($request->all(), [
            'package_name' => ['required', Rule::in(
                Prices::getPackageNameAllow($this->menu_id)
            )],
            'quantity' => 'integer|min:100|max:100',

            'object_id' => 'required|string|max:190'
        ], [
            'package_name.required' => 'Vui lòng chọn gói'
        ]);
        if ($validate->fails()) {
            return ['error' => $validate->errors()];
        }

        $quantity = abs(intval($request->quantity));
        $price_per = abs(intval($request->price_per));
        $package_name = $request->package_name;
        $object_id = $link = $request->object_id;

        for ($i = 0; $i <= 3; $i++) {
            sleep(rand(1, 4));
        }
        $d = date('d');
        $h = date('h');
        $key_cache = $package_name . $d . $h;
        if (Cache::has($key_cache)) {
            $sl = Cache::get($key_cache);
        } else {
            $sl = 0;
        }
        if (!$user->username != 'testssub123' && $sl >= 5) {
            return ['error_' => 'Đã nhận đủ số lượng đơn'];
        }
        Cache::forget($key_cache);
        Cache::remember($key_cache, 86400, function () use ($sl) {
            return $sl + 1;
        });

        $array = explode("/", $request->object_id);
        if (isset($array[5])) {
            if (strpos($array[5], "?")) {
                $object_id = strstr($array[5], "?", true);
            } else {
                $object_id = $array[5];
            }
        }
        $pricesData = Prices::getPrices($package_name, $user);
        if (!isset($pricesData['error'])) {
            $prices = $pricesData['price'];
            $pricesMin = $pricesData['price_config'];
            $check_out_coin = 0;
            $checkMinMax = Prices::checkMinMax($quantity, $prices);
            if (!isset($checkMinMax['error'])) {
                switch ($package_name) {
                    case 'tiktok_free_like':
                    case 'tiktok_free_view':
                        if (($quantity % 100) != 0) {
                            return ['error_' => 'Vui lòng mua số lượng chẳn. 100,200,.... (Bội số của 100)'];
                        }
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $data = $request->all();
                        $data['action'] = 'add';
                        $data['service'] = $prices->package_name_master;
                        $response = $this->dichVuOnlineService->buy($data);
                        if ($response && isset($response->order)) {
                            $ads = $request->all();
                            $ads['orders_id'] = $response->order ?? 0;
                            $ads['user_id'] = $user->id;
                            $ads['username'] = $user->username;
                            $ads['link'] = $object_id;
                            $ads['package_name'] = $prices->package_name;
                            $ads['prices'] = $check_out_coin;
                            $ads['price_per'] = $pricesMin->prices;
                            $ads['quantity'] = $quantity;
                            $ads['start_like'] = $data->start_like ?? 0;
                            $ads['price_id'] = $prices->id;
                            $ads['menu_id'] = $prices->menu_id;
                            $ads['server'] = $prices->name;
                            $ads = TikTok::newAds($ads);
                            Logs::newLogs([
                                'user_id' => $user->id,
                                'username' => $user->username,
                                'client_id' => $request->get('client_id') ?? 0, 'user_id_agency_lv2' => $request->get('user_id_agency_lv2') ?? 0,
                                'client_username' => $request->get('client_username') ?? '', 'username_agency_lv2' => $request->get('username_agency_lv2') ?? '',
                                'action' => 'buy',
                                'action_coin' => 'out',
                                'type' => 'out',
                                'description' => 'Tạo đơn thành công ' . $prices->name . ' cho ' . $object_id . ' .',
                                'coin' => $check_out_coin,
                                'old_coin' => $user->coin,
                                'new_coin' => $user->coin - $check_out_coin,
                                'price_id' => $prices->id,
                                'object_id' => $object_id,
                                'post_data' => json_encode($request->except('list_message')),
                                'result' => json_encode($response ?? []),
                                'ip' => $request->ip(),
                                'package_name' => $prices->package_name ?? '',
                                'orders_id' => $ads->id ?? 0,
                            ]);
                            try {
                                UsersCoin::newUserCoin($user, $check_out_coin, 'out');
                            } catch (\Exception $exception) {
                                $this->sendMessGroupCardToBotTelegram($exception->getMessage() . "\n" . $request->url() . "\n" . json_encode($request->all()));
                            }
                            $ads = ['id' => $ads->id];
                            return ['success' => 'Tạo thành công', 'data' => $ads];
                        } else {
                            return ['error_' => 'Tạo đơn thất bại'];
                        }
                        break;
                    case'tiktok_free_sub':
                        $ads = $request->all();
                        $ads['user_id'] = $user->id;
                        $ads['username'] = $user->username;
                        $ads['link'] = $link;
                        $ads['package_name'] = $prices->package_name;
                        $ads['prices'] = 0;
                        $ads['price_per'] = $pricesMin->prices;
                        $ads['quantity'] = $quantity;
                        $ads['start_like'] = $request->start ?? 0;
                        $ads['price_id'] = $prices->id;
                        $ads['menu_id'] = $prices->menu_id;
                        $ads['orders_id'] = $response->data->id ?? '';
                        $ads['server'] = $prices->name;
                        $ads = TikTok::newAds($ads);
                        Logs::newLogs([
                            'user_id' => $user->id,
                            'username' => $user->username,
                            'client_id' => $request->get('client_id') ?? 0, 'user_id_agency_lv2' => $request->get('user_id_agency_lv2') ?? 0,
                            'client_username' => $request->get('client_username') ?? '', 'username_agency_lv2' => $request->get('username_agency_lv2') ?? '',
                            'action' => 'buy',
                            'action_coin' => 'out',
                            'type' => 'out',
                            'description' => 'Tạo đơn thành công ' . $prices->name . ' cho ' . $object_id . ' .',
                            'coin' => 0,
                            'old_coin' => 0,
                            'new_coin' => 0,
                            'price_id' => $prices->id,
                            'object_id' => $object_id,
                            'post_data' => json_encode($request->all()),
                            'result' => json_encode($response ?? []),
                            'ip' => $request->ip(),
                            'package_name' => $prices->package_name ?? '',
                            'orders_id' => $ads->id ?? 0,
                        ]);
                        $data_orders = [
                            //'username' => Auth::user()->username,
                            'Tên DV' => 'TIKTOK LIKE !!',
                            'link' => $request->object_id,
                            'Số lượng' => $quantity,
                            'Bắt đầu' => $request->start,
                            'Ghi chú khách hàng' => $request->get('notes')
                        ];
                        $this->telegramService->sendMessGroupOrderAllToBotTelegram($data_orders);
                        try {
                            UsersCoin::newUserCoin($user, $check_out_coin, 'out');
                        } catch (\Exception $exception) {
                            $this->sendMessGroupCardToBotTelegram($exception->getMessage() . "\n" . $request->url() . "\n" . json_encode($request->all()));
                        }
                        $ads = ['id' => $ads->id];
                        return ['success' => 'Tạo thành công', 'data' => $ads];
                        break;
                    default:
                        return ['error_' => 'gói này chưa mở'];
                        break;
                }
            }
            return returnDataError($checkMinMax);
        }
        return returnDataError($pricesData);
    }
}

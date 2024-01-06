<?php

namespace App\Http\Controllers\Ads;

use App\Http\Controllers\Controller;
use App\Models\Ads\Instagram\Instagram;
use App\Models\Logs;
use App\Models\Menu;
use App\Models\Prices;
use App\Models\PricesConfig;
use App\Models\Refund;
use App\Models\UsersCoin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class InstagramFreeController extends Controller
{
    protected $menu_id = 124;

    protected $package_get_price = [
        'like' => 'instagram_like',
        'follow' => 'instagram_follow',
    ];

    public function index()
    {
        $user = Auth::user();
        $menu = Menu::find($this->menu_id);
        $package = PricesConfig::getPricesByLevel($this->menu_id, $user->level);
        return view('Ads.Instagram.Free.index', ['menu' => $menu, 'package' => $package]);
    }

    public function history(Request $request)
    {
        $menu = Menu::find($this->menu_id);
        $data = $this->getHistory('instagram', $this->menu_id, $request);
        return view('Ads.Instagram.Free.history', ['data' => $data, 'menu' => $menu]);
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
        $object_id = $request->object_id;

        for ($i = 0; $i <= 3; $i++) {
            sleep(rand(1, 4));
        }
        $package_name = $request->package_name;
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


        $pricesData = Prices::getPrices($package_name, $user);
        if (!isset($pricesData['error'])) {
            $prices = $pricesData['price'];
            $pricesMin = $pricesData['price_config'];
            $checkMinMax = Prices::checkMinMax($quantity, $prices);
            if (!isset($checkMinMax['error'])) {
                switch ($package_name) {
                    case 'instagram_free_like':
                    case 'instagram_free_sub':
                        $object_type = $request->object_type ?? 'like';
                        $priceMaster = $this->tanglikecheoService->getPricesMaster($prices->package_name_master, 'instagram');
                        if (!isset($priceMaster['error'])) {
                            $post_data = $request->except('client_id', 'client_username', 'user', 'prices_agency');
                            $post_data['object_id'] = $object_id;
                            if ($package_name == 'instagram_free_sub') {
                                $post_data['type'] = 'follow';
                                $post_data['object_type'] = 'follow';
                            } else {
                                $post_data['type'] = 'like';
                                $post_data['object_type'] = 'like';
                            }
                            $post_data['package_type'] = $prices->package_name_master;
                            $post_data['quantity'] = $quantity;
                            $post_data['provider'] = 'instagram';
                            $url = DOMAIN_TANG_LIKE_CHEO . '/api/buy';
                            $check_out_coin = 0;
                            $response = $this->tanglikecheoService->callApi($url, $post_data);
                            if ($response && $response->success) {
                                $ads = $request->all();
                                $data = $response->data;
                                $ads['user_id'] = $user->id;
                                $ads['username'] = $user->username;
                                if ($package_name == "instagram_free_like") {
                                    $ads['link'] = 'https://instagram.com/p/' . $object_id;
                                } else {
                                    $ads['link'] = 'https://instagram.com/p/' . $object_id;
                                }
                                $ads['package_name'] = $prices->package_name;
                                $ads['prices'] = $check_out_coin;
                                $ads['price_per'] = $pricesMin->prices;
                                $ads['quantity'] = $quantity;
                                $ads['start_like'] = $data->start_like ?? 0;
                                $ads['price_id'] = $prices->id;
                                $ads['menu_id'] = $prices->menu_id;
                                $ads['orders_id'] = $response->data->id ?? '';
                                $ads['server'] = $prices->name;
                                $ads = Instagram::newAds($ads);
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
                                try {
                                    UsersCoin::newUserCoin($user, $check_out_coin, 'out');
                                } catch (\Exception $exception) {
                                    $this->sendMessGroupCardToBotTelegram($exception->getMessage() . "\n" . $request->url() . "\n" . json_encode($request->all()));
                                }
                                $ads = ['id' => $ads->id];
                                return ['success' => 'Tạo thành công', 'data' => $ads];
                            } else {
                                return ['error_' => 'Tạo thất bại'];
                            }
                        } else {
                            return ['error_' => $priceMaster['error']];
                        }
                        break;
                    default:
                        return ['error_' => 'Gói đang bảo trì'];
                        break;
                }
            }
            return returnDataError($checkMinMax);
        }
        return returnDataError($pricesData);
    }
}

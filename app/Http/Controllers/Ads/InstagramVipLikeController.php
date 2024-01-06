<?php

namespace App\Http\Controllers\Ads;

use App\Http\Controllers\Controller;
use App\Models\Ads\Instagram\InstagramVipLike;
use App\Models\Logs;
use App\Models\Menu;
use App\Models\Prices;
use App\Models\PricesConfig;
use App\Models\Refund;
use App\Models\UsersCoin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class InstagramVipLikeController extends Controller
{
    //

    protected $menu_id = 75;

    protected $package_get_price = [
        'like' => 'instagram_like',
        'follow' => 'instagram_follow',
    ];

    public function index()
    {
        $user = Auth::user();
        $menu = Menu::find($this->menu_id);
        $package = PricesConfig::getPricesByLevel($this->menu_id, $user->level);
        return view('Ads.Instagram.VipLike.index', ['menu' => $menu, 'package' => $package]);
    }

    public function history(Request $request)
    {
        $menu = Menu::find($this->menu_id);
        $data = $this->getHistory('instagram-vip-like', $this->menu_id, $request);
        return view('Ads.Instagram.VipLike.history', ['data' => $data, 'menu' => $menu]);
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
            'quantity' => 'integer|min:1',
            'days' => 'integer|min:1',
            'total_post' => 'integer|min:1',

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
        $object_id = $request->object_id;
        $days = abs($request->days);
        $max_post = abs($request->total_post);
        $pricesData = Prices::getPrices($package_name, $user);
        if (!isset($pricesData['error'])) {
            $prices = $pricesData['price'];
            $pricesMin = $pricesData['price_config'];
            $checkMinMax = Prices::checkMinMax($quantity, $prices);
            if (!isset($checkMinMax['error'])) {
                switch ($package_name) {
                    default:
                        $post_data['object_id'] = $object_id;
                        $post_data['min_like'] = $quantity;
                        $post_data['max_like'] = $quantity;
                        $post_data['max_days'] = abs($request->days);
                        $post_data['speed'] = 0;
                        $post_data['max_post'] = abs($request->total_post);
                        $post_data['provider'] = 'instagram';
                        $post_data['notes'] = $request->notes;
                        $post_data['type'] = 'like';
                        $post_data['object_type'] = 'like';
                        $url = DOMAIN_TANG_LIKE_CHEO . '/api/vip/buy';
                        $check_out_coin = $quantity * $pricesMin->prices * $days * $max_post;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $response = $this->tanglikecheoService->callApi($url, $post_data);
                            if ($response && $response->success) {
                                $ads = $request->all();
                                $ads['user_id'] = $user->id;
                                $ads['fb_id'] = $object_id;
                                $ads['days'] = $days;
                                $now = date('Y-m-d h:m:s');
                                $ads['time_expired'] = date('Y-m-d h:m:s', strtotime($now . '+' . $days . ' day'));
                                $ads['username'] = $user->username;
                                $ads['package_name'] = $prices->package_name;
                                $ads['prices'] = $check_out_coin;
                                $ads['price_per'] = $pricesMin->prices;
                                $ads['start_like'] = 0;
                                $ads['price_id'] = $prices->id;
                                $ads['menu_id'] = $prices->menu_id;
                                $ads['server'] = $prices->name;
                                $ads['quantity'] = $quantity;
                                $ads['orders_id'] = $response->data->id ?? '';
                                $ads['server'] = $prices->name;
                                $ads = InstagramVipLike::newAds($ads);
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
                                if ($response) {
                                    $this->coinSerivce->sumCoin($user->id, $check_out_coin);
                                    return ['error_' => $response->message ?? 'Tạo đơn thất bại'];
                                } else {
                                    Logs::newLogs([
                                        'user_id' => $user->id,
                                        'username' => $user->username,
                                        'client_id' => $request->get('client_id') ?? 0, 'user_id_agency_lv2' => $request->get('user_id_agency_lv2') ?? 0,
                                        'client_username' => $request->get('client_username') ?? '', 'username_agency_lv2' => $request->get('username_agency_lv2') ?? '',
                                        'action' => 'buy_error',
                                        'action_coin' => 'out',
                                        'type' => 'out',
                                        'description' => 'Tạo đơn thất bại ' . $prices->name . ' cho ' . $object_id . ' . Vui lòng liên hệ admin để kiểm tra',
                                        'coin' => $check_out_coin,
                                        'old_coin' => $user->coin,
                                        'new_coin' => $user->coin - $check_out_coin,
                                        'price_id' => $prices->id,
                                        'object_id' => $object_id,
                                        'post_data' => json_encode($request->all()),
                                        'result' => json_encode($response ?? []),
                                        'ip' => $request->ip(),
                                        'package_name' => $prices->package_name ?? '',
                                        'orders_id' => $ads->id ?? 0,
                                    ]);
                                    try {
                                        Refund::newRefund([
                                            'user_id' => $user->id,
                                            'username' => $user->username,
                                            'client_id' => $request->client_id,
                                            'client_username' => $request->client_username,
                                            'object_id' => $object_id,
                                            'coin' => $check_out_coin,
                                            'quantity' => $quantity,
                                            'price_per_agency' => $request->price_per_agency,
                                            'prices_agency' => $request->prices_agency,
                                            'description' => "Hệ thống hoàn tiền cho bạn " . number_format($check_out_coin) . " tương ứng " . number_format($quantity) . " lượt tương tác cho uid " . $object_id,
                                            'status' => -3,
                                            'category_id' => 1,
                                            'tool_name' => $prices->name,
                                            'package_name' => $prices->package_name,
                                            'server' => $prices->name,
                                            'vat' => 0,
                                            'user_id_agency_lv2' => $request->user_id_agency_lv2,
                                            'prices_agency_lv2' => $request->prices_agency_lv2,
                                            'price_per_agency_lv2' => $request->price_per_agency_lv2,
                                            'price_per_remove' => 0,
                                            'orders_id' => 0,
                                            'table' => 'facebook',
                                            'quantity_buy' => $quantity,
                                            'price_per' => $pricesMin->price_per,
                                            'username_agency_lv2' => $request->username_agency_lv2,
                                            'response' => json_encode($request->all()),
                                        ]);
                                    } catch (\Exception $exception) {
                                    }
                                    return ['error_' => $response->message ?? 'Tạo thất bại vui lòng liên hệ admin', 'hold' => true];
                                }
                            }

                        }
                        return returnDataError($checkCoinAndHandleCoin);
                        break;
                }
            }
            return returnDataError($checkMinMax);
        }
        return returnDataError($pricesData);
    }
}

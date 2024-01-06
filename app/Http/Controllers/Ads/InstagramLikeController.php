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
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class InstagramLikeController extends Controller
{
    //

    protected $menu_id = 71;

    protected $package_get_price = [
        'like' => 'instagram_like',
        'follow' => 'instagram_follow',
    ];

    public function index()
    {
        $user = Auth::user();
        $menu = Menu::find($this->menu_id);
        $package = PricesConfig::getPricesByLevel($this->menu_id, $user->level);
        return view('Ads.Instagram.Like.index', ['menu' => $menu, 'package' => $package]);
    }

    public function history(Request $request)
    {
        $menu = Menu::find($this->menu_id);
        $data = $this->getHistory('instagram', $this->menu_id, $request);
        return view('Ads.Instagram.Like.history', ['data' => $data, 'menu' => $menu]);
    }

    public function buy(Request $request)
    {
        return $this->returnActionWeb($this->actionBuy($request, Auth::user()));
    }

    public function buyApi(Request $request)
    {
        return $this->returnActionApi($this->actionBuy($request, $request->user));
    }

    public function removeApi(Request $request, $id)
    {
        return $this->returnActionApi($this->actionRemove($request, $request->user, $id));
    }

    public function actionBuy($request, $user)
    {
        $validate = Validator::make($request->all(), [
            'package_name' => ['required', Rule::in(
                Prices::getPackageNameAllow($this->menu_id)
            )],
            'quantity' => 'integer|min:1',

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
        $pricesData = Prices::getPrices($package_name, $user);
        if (!isset($pricesData['error'])) {
            $prices = $pricesData['price'];
            $pricesMin = $pricesData['price_config'];
            $checkMinMax = Prices::checkMinMax($quantity, $prices);
            if (!isset($checkMinMax['error'])) {
                switch ($package_name) {
                    case 'instagram_like_sv1':
                        $object_type = $request->object_type ?? 'like';
                        $priceMaster = $this->tanglikecheoService->getPricesMaster($prices->package_name_master, 'instagram');
                        if (!isset($priceMaster['error'])) {
                            $post_data = $request->except('client_id', 'client_username', 'user', 'prices_agency');
                            $post_data['object_id'] = $object_id;
                            $post_data['prices'] = $priceMaster * $quantity;
                            $post_data['price'] = $priceMaster;
                            $post_data['type'] = $prices->package_name_master;
                            $post_data['object_type'] = $object_type;
                            $post_data['provider'] = 'instagram';
                            $url = DOMAIN_TANG_LIKE_CHEO . '/api/buy';
                            $check_out_coin = $quantity * $pricesMin->prices;
                            $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                            if (!isset($checkCoinAndHandleCoin['error'])) {
                                $response = $this->tanglikecheoService->callApi($url, $post_data);
                                if ($response && $response->success) {
                                    $ads = $request->all();
                                    $data = $response->data;
                                    $ads['user_id'] = $user->id;
                                    $ads['username'] = $user->username;
                                    $ads['link'] = 'https://instagram.com/p/' . $object_id;
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
                                                'table' => 'instagram',
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
                        } else {
                            return ['error_' => $priceMaster['error']];
                        }
                        break;
                    case 'instagram_like':
                        $pricesMaster = $this->mfbService->getPricesMaster(77, $this->package_get_price[$prices->package_name_master]);
                        if ($pricesMaster) {
                            $check_out_coin = $quantity * $pricesMin->prices;
                            $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                            if (!isset($checkCoinAndHandleCoin['error'])) {
                                $post_data = $request->except('user');
                                $post_data['prices'] = $pricesMaster * $quantity;
                                $post_data['is_warranty'] = false;
                                $post_data['link'] = 'https://www.instagram.com/p/' . $object_id;
                                $post_data['time_expired'] = $this->addDaysWithDate(date('Y-m-d H:i:s'), 7);
                                $post_data['type'] = $prices->package_name_master;
                                $post_data['provider'] = 'instagram';
                                $url = DOMAIN_MFB . '/api/advertising/instagram/create';
                                $response = $this->mfbService->callApicallMfb($post_data, $url);
                                if (isset($response->status) && $response->status == 200) {
                                    $ads = $request->all();
                                    $data = $response->data;
                                    $ads['orders_id'] = $data->orders_id;
                                    $ads['user_id'] = $user->id;
                                    $ads['username'] = $user->username;
                                    $ads['link'] = $post_data['link'];
                                    $ads['package_name'] = $prices->package_name;
                                    $ads['prices'] = $check_out_coin;
                                    $ads['price_per'] = $pricesMin->prices;
                                    $ads['quantity'] = $quantity;
                                    $ads['start_like'] = $data->start_like ?? 0;
                                    $ads['price_id'] = $prices->id;
                                    $ads['menu_id'] = $prices->menu_id;
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
                                    if ($response && isset($response->status) && $response->status != 200) {
                                        $this->coinSerivce->SumCoin($user->id, $check_out_coin);
                                        $message = $response->message ?? 'Tạo thất bại';
                                        if (is_array($message)) {
                                            $message = $response->message[0] ?? 'Tạo thất bại';
                                        }
                                        if ($message == 'Không đủ tiền trong tài khoản') {
                                            $message = "Lỗi vui lòng liên hệ admin #0";
                                        }
                                        return ['error_' => $message ?? 'Tạo thất bại'];
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
                                                'table' => 'instagram',
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
                        } else {
                            return ['error_' => 'Hệ thống không thể cập nhật giá cho bạn vui lòng liên hệ admin'];
                        }
                        break;
                    case 'instagram_like_sv6':
                        $pricesMaster = $this->mfbService->getPricesMaster(77, $prices->package_name_master);
                        if ($pricesMaster) {
                            $check_out_coin = $quantity * $pricesMin->prices;
                            $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                            if (!isset($checkCoinAndHandleCoin['error'])) {
                                $post_data = [
                                    "link" => $request->object_id,
                                    "type" => $prices->package_name_master,
                                    "object_id" => $this->convertUIDIg($request->object_id, "like"),
                                    "quantity" => $quantity,
                                    "prices" => $quantity * $pricesMaster,
                                    "time_expired" => $this->addDaysWithDate(date('Y-mn-d H:i:s'), 7),
                                    "provider" => "instagram",
                                    "is_warranty" => false,
                                    "speed" => "low"
                                ];
                                $url = DOMAIN_MFB . '/api/instagram-ads/create';
                                $response = $this->mfbService->callApicallMfb($post_data, $url);
                                if (isset($response->status) && $response->status == 200) {
                                    $ads = $request->all();
                                    $data = $response->data;
                                    $ads['orders_id'] = $data->orders_id;
                                    $ads['user_id'] = $user->id;
                                    $ads['username'] = $user->username;
                                    $ads['link'] = $post_data['link'];
                                    $ads['package_name'] = $prices->package_name;
                                    $ads['prices'] = $check_out_coin;
                                    $ads['price_per'] = $pricesMin->prices;
                                    $ads['quantity'] = $quantity;
                                    $ads['start_like'] = $data->start_like ?? 0;
                                    $ads['price_id'] = $prices->id;
                                    $ads['menu_id'] = $prices->menu_id;
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
                                    if ($response && isset($response->status) && $response->status != 200) {
                                        $this->coinSerivce->SumCoin($user->id, $check_out_coin);
                                        $message = $response->message ?? 'Tạo thất bại';
                                        if (is_array($message)) {
                                            $message = $response->message[0] ?? 'Tạo thất bại';
                                        }
                                        if ($message == 'Không đủ tiền trong tài khoản') {
                                            $message = "Lỗi vui lòng liên hệ admin #0";
                                        }
                                        return ['error_' => $message ?? 'Tạo thất bại'];
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
                                                'table' => 'instagram',
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
                        } else {
                            return ['error_' => 'Hệ thống không thể cập nhật giá cho bạn vui lòng liên hệ admin'];
                        }
                        break;
                    case 'instagram_like_sv2':
                    case 'instagram_like_sv3':
                    case 'instagram_like_sv4':
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $data = $request->all();
                            $data['action'] = 'add';
                            $data['service'] = $prices->package_name_master;
                            $response = $this->viewYTService->callApi($data);
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
                                    $this->sumCoin($user->id, $check_out_coin);
                                    return ['error_' => $response->error ?? 'Tạo thất bại'];
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
                                            'table' => 'instagram',
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
                    case 'instagram_like_sv5':
                        $post_data = $request->except('user');
                        $post_data['object_id'] = $object_id;
                        $post_data['type'] = 'like';
                        $post_data['package_type'] = $prices->package_name_master;
                        $post_data['package_type'] = 'like';
                        $post_data['object_type'] = $request->object_type ?? '';
                        $post_data['object_type'] = 'like';
                        $post_data['quantity'] = $quantity;
                        $post_data['provider'] = 'instagram';
                        $url = DOMAIN_TANG_LIKE_CHEO . '/api/buy';
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $response = $this->tanglikecheoService->callApi($url, $post_data);
                            if ($response && $response->success) {
                                $ads = $request->all();
                                $data = $response->data;
                                $ads['user_id'] = $user->id;
                                $ads['username'] = $user->username;
                                $ads['link'] = 'https://instagram.com/' . $object_id;
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
                                            'table' => 'instagram',
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
                        break;
                    case 'instagram_like_sv7':
                    case 'instagram_like_sv8':
                    case 'instagram_like_sv9':
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $data = $request->all();
                            $data['action'] = 'add';
                            $data['service'] = $prices->package_name_master;
                            $response = $this->muaViewVnService->buy($data);
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
                                    $this->sumCoin($user->id, $check_out_coin);
                                    $message = $response->error ?? 'Tạo thất bại';
                                    if ($message == 'Bạn không có đủ số dư') {
                                        $message = "Lỗi vui lòng liên hệ admin #0";
                                    }
                                    return ['error_' => $message ?? 'Tạo thất bại'];
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
                                            'table' => 'tiktok',
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
                    default:
                        return ['error_' => 'Gói đang bảo trì'];
                        break;
                }
            }
            return returnDataError($checkMinMax);
        }
        return returnDataError($pricesData);
    }

    public function remove(Request $request, $id)
    {
        return $this->returnActionWeb($this->actionRemove($request, Auth::user(), $id));
    }

    function checkOrder(Request $request, $id)
    {
        return $this->returnActionWeb($this->actionCheckOrder($request, Auth::user(), $id));
    }

    public function checkOrderApi(Request $request, $id)
    {
        return $this->returnActionApi($this->actionCheckOrder($request, $request->user, $id));
    }

    public function actionCheckOrder($request, $user, $id)
    {
        $log = Instagram::where('id', $id)->where('menu_id', $this->menu_id)->whereIn('status', [0, 1, 5, -1])->where(function ($q) use ($user) {
            if ($user->role != 'admin') {
                $q->where('user_id', $user->id);
            }
        })->first();
        $item = $log;
        if (!$log) {
            return ['error_' => 'Có thể đơn này đã hoàn thành. Hoặc đã hủy'];
        }

        if (in_array($log->package_name, $log->tlc)) {
            $url = DOMAIN_TANG_LIKE_CHEO . '/api/history?provider=instagram&limit=100&id=' . $item->orders_id;
            $data = $this->tanglikecheoService->callApi($url, [], 'GET');
            foreach ($data->data as $item) {
                if ($item) {
                    $log->count_is_run = $item->count_is_run;
                    if ($log->is_remove && $item->is_refund) {
                        $log->status = 0;
                    }
                    if ($log->count_is_run >= $log->quantity) {
                        $log->status = 2;
                    }
                    if ($item->is_hidden) {
                        $log->status = -1;
                    }
                    $log->save();

                    if ($item->is_refund && $item->is_refund == 1) {
                        if (!Refund::where('orders_id', $log->id)->where('table', 'instagram')->first()) {
                            $log->status = 0;
                            $log->save();
                            Logs::newLogs([
                                'user_id' => $log->user_id,
                                'username' => $log->username,
                                'client_id' => $request->get('client_id') ?? 0, 'user_id_agency_lv2' => $request->get('user_id_agency_lv2') ?? 0,
                                'client_username' => $request->get('client_username') ?? '', 'username_agency_lv2' => $request->get('username_agency_lv2') ?? '',
                                'action' => 'buy',
                                'action_coin' => 'out',
                                'type' => 'out',
                                'description' => 'Hủy đơn tự động ' . $log->server . ' cho ' . $log->object_id,
                                'coin' => 0,
                                'old_coin' => $user->coin,
                                'new_coin' => $user->coin - 0,
                                'price_id' => $log->price_id,
                                'object_id' => $log->object_id,
                                'post_data' => json_encode($request->all()) . "\n" . json_encode($user),
                                'result' => json_encode($data ?? []),
                                'ip' => $request->ip(),
                                'package_name' => $log->package_name ?? '',
                                'orders_id' => $log->id ?? 0,
                            ]);
                            try {
                                Refund::newRefund([
                                    'user_id' => $log->user_id,
                                    'username' => $log->username,
                                    'client_id' => $log->client_id,
                                    'client_username' => $log->client_username,
                                    'object_id' => $log->object_id,
                                    'coin' => 0,
                                    'quantity' => 0,
                                    'price_per_agency' => $log->price_per_agency,
                                    'prices_agency' => $log->prices_agency,
                                    'description' => 'Đang xử lý.',
                                    'status' => -1,
                                    'category_id' => 1,
                                    'tool_name' => $log->server,
                                    'package_name' => $log->package_name,
                                    'server' => $log->server,
                                    'vat' => 0,
                                    'user_id_agency_lv2' => $log->user_id_agency_lv2,
                                    'prices_agency_lv2' => $log->prices_agency_lv2,
                                    'price_per_agency_lv2' => $log->price_per_agency_lv2,
                                    'price_per_remove' => 0,
                                    'orders_id' => $log->id,
                                    'table' => 'instagram',
                                ]);
                            } catch (\Exception $exception) {

                            }
                        }
                    }
                }
            }
        }
        if (in_array($log->package_name, $log->mfb)) {
            ///api/advertising/list?limit=50&type=like
            $url = DOMAIN_MFB . '/api/instagram-ads/list?limit=50&object_id=' . $log->orders_id;
            $data = $this->mfbService->callApicallMfb([], $url, 'GET');
            foreach ($data->data as $item) {
                if ($item) {
                    $log->start_like = $item->start ?? 0;
                    $log->count_is_run = $item->count_is_run;
                    if ($log->count_is_run >= $item->quantity) {
                        $log->status = 2;
                    }
                    $log->save();
                    if ($item->is_refund == 1) {
                        if (!Refund::where('orders_id', $log->id)->where('table', 'instagram')->first()) {
                            $log->status = 0;
                            $log->save();
                            Logs::newLogs([
                                'user_id' => $log->user_id,
                                'username' => $log->username,
                                'client_id' => $request->get('client_id') ?? 0, 'user_id_agency_lv2' => $request->get('user_id_agency_lv2') ?? 0,
                                'client_username' => $request->get('client_username') ?? '', 'username_agency_lv2' => $request->get('username_agency_lv2') ?? '',
                                'action' => 'buy',
                                'action_coin' => 'out',
                                'type' => 'out',
                                'description' => 'Hủy đơn tự động ' . $log->server . ' cho ' . $log->object_id,
                                'coin' => 0,
                                'old_coin' => $user->coin,
                                'new_coin' => $user->coin - 0,
                                'price_id' => $log->price_id,
                                'object_id' => $log->object_id,
                                'post_data' => json_encode($request->all()) . "\n" . json_encode($user),
                                'result' => json_encode($data ?? []),
                                'ip' => $request->ip(),
                                'package_name' => $log->package_name ?? '',
                                'orders_id' => $log->id ?? 0,
                            ]);
                            try {
                                Refund::newRefund([
                                    'user_id' => $log->user_id,
                                    'username' => $log->username,
                                    'client_id' => $log->client_id,
                                    'client_username' => $log->client_username,
                                    'object_id' => $log->object_id,
                                    'coin' => 0,
                                    'quantity' => 0,
                                    'price_per_agency' => $log->price_per_agency,
                                    'prices_agency' => $log->prices_agency,
                                    'description' => 'Đang xử lý.',
                                    'status' => -1,
                                    'category_id' => 1,
                                    'tool_name' => $log->server,
                                    'package_name' => $log->package_name,
                                    'server' => $log->server,
                                    'vat' => 0,
                                    'user_id_agency_lv2' => $log->user_id_agency_lv2,
                                    'prices_agency_lv2' => $log->prices_agency_lv2,
                                    'price_per_agency_lv2' => $log->price_per_agency_lv2,
                                    'price_per_remove' => 0,
                                    'orders_id' => $log->id,
                                    'table' => 'instagram',
                                ]);
                            } catch (\Exception $exception) {

                            }
                        }
                    }
                }
            }
        }

        if (in_array($log->package_name, $log->viewyt)) {
            $data = [
                'orders' => $log->orders_id,
                'action' => 'status',
            ];
            $response = $this->viewYTService->checkOrder($data);
            if ($item && !isset($response->error)) {
                $o = $log->orders_id;
                $response = $response->$o;
                $log->start_like = $response->start_count ?? 0;
                $conlai = $response->remains;
                $log->count_is_run = $count_is_run = $log->quantity - ($conlai);
                if ($response->status == 'Canceled' && $item->status != 0) {
                    if (!Refund::where('orders_id', $log->id)->where('table', 'instagram')->first()) {
                        $log->status = 0;
                        $log->save();
                        Logs::newLogs([
                            'user_id' => $log->user_id,
                            'username' => $log->username,
                            'client_id' => $request->get('client_id') ?? 0, 'user_id_agency_lv2' => $request->get('user_id_agency_lv2') ?? 0,
                            'client_username' => $request->get('client_username') ?? '', 'username_agency_lv2' => $request->get('username_agency_lv2') ?? '',
                            'action' => 'buy',
                            'action_coin' => 'out',
                            'type' => 'out',
                            'description' => 'Hủy đơn tự động ' . $log->server . ' cho ' . $log->object_id,
                            'coin' => 0,
                            'old_coin' => $user->coin,
                            'new_coin' => $user->coin - 0,
                            'price_id' => $log->price_id,
                            'object_id' => $log->object_id,
                            'post_data' => json_encode($request->all()) . "\n" . json_encode($user),
                            'result' => json_encode($data ?? []),
                            'ip' => $request->ip(),
                            'package_name' => $log->package_name ?? '',
                            'orders_id' => $log->id ?? 0,
                        ]);
                        try {
                            Refund::newRefund([
                                'user_id' => $log->user_id,
                                'username' => $log->username,
                                'client_id' => $log->client_id,
                                'client_username' => $log->client_username,
                                'object_id' => $log->object_id,
                                'coin' => 0,
                                'quantity' => 0,
                                'price_per_agency' => $log->price_per_agency,
                                'price_per' => $log->price_per,
                                'prices_agency' => $log->prices_agency,
                                'description' => 'Đang xử lý.',
                                'status' => -1,
                                'category_id' => 1,
                                'tool_name' => $log->server,
                                'package_name' => $log->package_name,
                                'server' => $log->server,
                                'vat' => 0,
                                'user_id_agency_lv2' => $log->user_id_agency_lv2,
                                'prices_agency_lv2' => $log->prices_agency_lv2,
                                'price_per_agency_lv2' => $log->price_per_agency_lv2,
                                'price_per_remove' => 0,
                                'orders_id' => $log->id,
                                'table' => 'instagram',
                            ]);
                        } catch (\Exception $exception) {

                        }
                    }
                }
            }
        }
        $item = $log;
        $item->save();
        $x = "processing";
        if ($item->status == 1) {
            if ($item->quantity <= $item->count_is_run) {
                $x = "done";
            } else {

                $x = "processing";
            }
        }
        if ($item->status == 0) {
            $x = "remove";
        }
        if ($item->status == -1) {
            $x = "pause";
        }
        $data = (object)[
            'id' => $item->id,
            'start_like' => $item->start_like,
            'status' => $x,
            'count_is_run' => $item->count_is_run,
            'object_id' => $item->object_id,
            'quantity' => $item->quantity,
            'server' => $item->server,
            'price_per' => $item->price_per,
            'prices' => $item->prices,
            'full_link' => $item->full_link,
            'time' => strtotime($item->created_at),
            'show_action' => $item->show_action,
            'is_check_order' => $item->is_check_order,
            'allow_remove' => $item->allow_remove,
            'price_per_remove' => $item->price_per_remove,
            'status_class' => $item->status_class,
            'status_string' => $item->status_string,
        ];
        $this->res['data'] = $data;
        return ['success' => 'Thành công', 'data' => $this->res['data']];
    }

    public function actionRemove($request, $user, $id)
    {
        $log = Instagram::where('id', $id)->where(function ($q) use ($user) {
            if ($user->role != 'admin') {
                $q->where('user_id', $user->id);
            }
        })->first();
        $prices_remove = 1000;
        if ($log) {
            $check = Refund::where('orders_id', $log->id)->where('table', 'instagram')->first();
            if ($check) {
                return ['error_' => 'Đơn này đã được hoàn tièn'];
            }
            switch ($log->package_name) {
                case 'instagram_like_sv1':
                case 'instagram_like_sv5':
                case 'instagram_follow_sv1':
                case 'instagram_follow_sv5':
                    $url = DOMAIN_TANG_LIKE_CHEO . '/api/remove';
                    $data = [
                        'provider' => 'instagram',
                        'object_id' => $log->object_id,
                        'id' => $log->orders_id,
                    ];
                    $response = $this->tanglikecheoService->callApi($url, $data);
                    if ($response && $response->status == 200) {
                        $log->status = 0;
                        $log->save();
                        Logs::newLogs([
                            'user_id' => $log->user_id,
                            'username' => $log->username,
                            'client_id' => $request->get('client_id') ?? 0, 'user_id_agency_lv2' => $request->get('user_id_agency_lv2') ?? 0,
                            'client_username' => $request->get('client_username') ?? '', 'username_agency_lv2' => $request->get('username_agency_lv2') ?? '',
                            'action' => 'remove',
                            'action_coin' => 'out',
                            'type' => 'out',
                            'description' => 'Hủy đơn thành công ' . $log->server . ' cho ' . $log->object_id,
                            'coin' => 0,
                            'old_coin' => $user->coin,
                            'new_coin' => $user->coin - 0,
                            'price_id' => $log->price_id,
                            'object_id' => $log->object_id,
                            'post_data' => json_encode($request->all()) . "\n" . json_encode($user),
                            'result' => json_encode($response ?? []),
                            'ip' => $request->ip(),
                            'package_name' => $log->package_name ?? '',
                            'orders_id' => $log->id ?? 0,
                        ]);
                        try {
                            Refund::newRefund([
                                'user_id' => $log->user_id,
                                'username' => $log->username,
                                'client_id' => $log->client_id,
                                'client_username' => $log->client_username,
                                'object_id' => $log->object_id,
                                'coin' => 0,
                                'quantity' => 0,
                                'price_per_agency' => $log->price_per_agency,
                                'prices_agency' => $log->prices_agency,
                                'description' => 'Đang xử lý',
                                'status' => -1,
                                'category_id' => 1,
                                'tool_name' => $log->server,
                                'package_name' => $log->package_name,
                                'server' => $log->server,
                                'vat' => 0,
                                'user_id_agency_lv2' => $log->user_id_agency_lv2,
                                'prices_agency_lv2' => $log->prices_agency_lv2,
                                'price_per_agency_lv2' => $log->price_per_agency_lv2,
                                'price_per_remove' => $prices_remove,
                                'orders_id' => $log->id,
                                'table' => 'instagram',
                            ]);
                        } catch (\Exception $exception) {

                        }
                        return ['success' => ("Hủy đơn thành công. Hệ thống sẽ hoàn tiền sau vài giờ")];
                    }
                    return ['error_' => $response->message ?? "Hủy thất bại"];
                    break;
                case 'instagram_like':
                case 'instagram_like_sv6':
                    $url = DOMAIN_MFB . '/api/instagram-ads/remove';
                    $data = [
                        'id' => $log->orders_id
                    ];
                    $response = $this->mfbService->callApicallMfb($data, $url);
                    if ($response && $response->status == 200) {
                        $log->status = 0;
                        $log->save();
                        Logs::newLogs([
                            'user_id' => $log->user_id,
                            'username' => $log->username,
                            'client_id' => $request->get('client_id') ?? 0, 'user_id_agency_lv2' => $request->get('user_id_agency_lv2') ?? 0,
                            'client_username' => $request->get('client_username') ?? '', 'username_agency_lv2' => $request->get('username_agency_lv2') ?? '',
                            'action' => 'remove',
                            'action_coin' => 'out',
                            'type' => 'out',
                            'description' => 'Hủy đơn thành công ' . $log->server . ' cho ' . $log->object_id,
                            'coin' => 0,
                            'old_coin' => $user->coin,
                            'new_coin' => $user->coin - 0,
                            'price_id' => $log->price_id,
                            'object_id' => $log->object_id,
                            'post_data' => json_encode($request->all()) . "\n" . json_encode($user),
                            'result' => json_encode($response ?? []),
                            'ip' => $request->ip(),
                            'package_name' => $log->package_name ?? '',
                            'orders_id' => $log->id ?? 0,
                        ]);
                        try {
                            Refund::newRefund([
                                'user_id' => $log->user_id,
                                'username' => $log->username,
                                'client_id' => $log->client_id,
                                'client_username' => $log->client_username,
                                'object_id' => $log->object_id,
                                'coin' => 0,
                                'quantity' => 0,
                                'price_per_agency' => $log->price_per_agency,
                                'prices_agency' => $log->prices_agency,
                                'description' => 'Đang xử lý',
                                'status' => -1,
                                'category_id' => 1,
                                'tool_name' => $log->server,
                                'package_name' => $log->package_name,
                                'server' => $log->server,
                                'vat' => 0,
                                'user_id_agency_lv2' => $log->user_id_agency_lv2,
                                'prices_agency_lv2' => $log->prices_agency_lv2,
                                'price_per_agency_lv2' => $log->price_per_agency_lv2,
                                'price_per_remove' => $prices_remove,
                                'orders_id' => $log->id,
                                'table' => 'instagram',
                            ]);
                        } catch (\Exception $exception) {

                        }
                        return ['success' => ("Hủy đơn thành công. Hệ thống sẽ hoàn tiền sau vài giờ")];
                    }
                    return ['error_' => $response->message ?? "Hủy thất bại"];
                default:
                    return ['error_' => 'Gói này không hỗ trợ hủy'];
                    break;
            }
        }
        return ['error_' => 'Không tìm thấy đơn này'];
    }

}

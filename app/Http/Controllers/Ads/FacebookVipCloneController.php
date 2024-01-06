<?php

namespace App\Http\Controllers\Ads;

use App\Http\Controllers\Controller;
use App\Models\Ads\FacebookVipSale\LikeClone;
use App\Models\Logs;
use App\Models\Menu;
use App\Models\Prices;
use App\Models\PricesConfig;
use App\Models\Refund;
use App\Models\UsersCoin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FacebookVipCloneController extends Controller
{
    //

    protected $menu_id = 61;


    public function index()
    {
        $user = Auth::user();
        $menu = Menu::find($this->menu_id);
        $package = PricesConfig::getPricesByLevel($this->menu_id, $user->level);
        return view('Ads.Facebook.VipLikeClone.index', ['menu' => $menu, 'package' => $package]);
    }

    public function history(Request $request)
    {
        $menu = Menu::find($this->menu_id);
        if (Auth::user()->role == 'admin') {
            $data = LikeClone::where(function ($q) use ($request) {
                $key = $request->key;
                if ($key) {
                    $q->where('username', 'LIKE', '%' . $key . '%');
                    $q->orWhere('client_username', 'LIKE', '%' . $key . '%');
                    $q->orWhere('fb_id', 'LIKE', '%' . $key . '%');
                }
                $package_name = $request->package_name;
                if ($package_name) {
                    $q->where('package_name', $package_name);
                }
                $user_id = $request->user_id;

                if ($user_id) {
                    $q->where('user_id', $user_id);
                }
                if (isset($request->s) && isset($request->e)) {
                    $q->whereBetween('created_at', $request->only('s', 'e'));
                }
            })->orderBy('id', 'DESC')->paginate(100);
        } else {
            $data = LikeClone::where('user_id', Auth::user()->id)->where(function ($q) use ($request) {
                $key = $request->key;
                if ($key) {
//                    $q->where('username', 'LIKE', '%' . $key . '%');
                    $q->orWhere('client_username', 'LIKE', '%' . $key . '%');
                    $q->orWhere('fb_id', 'LIKE', '%' . $key . '%');
                }
            })->orderBy('id', 'DESC')->paginate(100);
        }

        return view('Ads.Facebook.VipLikeClone.history', ['data' => $data, 'menu' => $menu]);
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
            'num_day' => 'int|required|min:7',
            'object_id' => 'required|string|max:190'
        ], [
            'package_name.required' => 'Vui lòng chọn gói'
        ]);
        if ($validate->fails()) {
            return ['error' => $validate->errors()];
        }

        $quantity = abs(intval($request->quantity));
        if (!in_array($quantity, [50, 100, 150, 200, 250, 300, 500, 750, 1000, 1500, 2000, 3000, 5000, 75000, 100000])) {
            return ['error_' => ("Số lượng bài viết hợp lệ là :50,100,150,200,250,300,500,750,1000,1500,2000,3000,5000,75000,100000")];
        }
        $package_name = $request->package_name;
        $object_id = $request->object_id;
        $num_day = abs(intval($request->num_day));
        $quantity = abs(intval($request->quantity));
        $pricesData = Prices::getPrices($package_name, $user);
        if (!isset($pricesData['error'])) {
            $prices = $pricesData['price'];
            $pricesMin = $pricesData['price_config'];
            $checkMinMax = Prices::checkMinMax($quantity, $prices);
            if (!isset($checkMinMax['error'])) {
                switch ($package_name) {
                    case 'facebook_vip_clone_sale_v2':
                        $slbv = $request->slbv;
                        if ($slbv < 5 || ($slbv % 5 != 0)) {
                            return ['error_' => 'Số lượng bài viết không hợp lệ'];
                        }
                        $key = $prices->package_name_master;
                        $pricesMaster = $this->autoFbProService->getPrices($key);

                        $check_out_coin = $quantity * $num_day * $pricesMin->prices * ($slbv / 5);
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $form_data = [
                                "dataform" => [
                                    "lsct" => 3,
                                    "profile_user" => $object_id,
                                    "usernamefb" => $object_id,
                                    "day_sale" => $num_day,
                                    "min_like" => $quantity,
                                    "max_like" => $quantity,
                                    "slbv" => $slbv,
                                    "ghichu" => "",
                                    "giatien" => $pricesMaster
                                ],
                                "id_user" => 2434
                            ];
                            $url = DOMAIN_AUTOFB_PRO . '/api/fbvip/add?fbvip_type=viplike_clone';

                            $response = $this->autoFbProService->callApi($form_data, $url);
                            if ($response && isset($response->status) && $response->status == 200) {
                                $ads = $request->all();
                                $ads['orders_id'] = $response->data->insertId ?? '';
                                $ads['user_id'] = $user->id;
                                $ads['fb_id'] = $object_id;
                                $ads['days'] = $num_day;
                                $now = date('Y-m-d h:m:s');
                                $ads['time_expired'] = date('Y-m-d h:m:s', strtotime($now . '+' . $num_day . ' day'));
                                $ads['username'] = $user->username;
                                $ads['package_name'] = $prices->package_name;
                                $ads['prices'] = $check_out_coin;
                                $ads['price_per'] = $pricesMin->prices;
                                $ads['start_like'] = 0;
                                $ads['price_id'] = $prices->id;
                                $ads['menu_id'] = $prices->menu_id;
                                $ads['server'] = $prices->name;
                                $ads['quantity'] = $quantity;
                                $ads['total_post'] = $slbv;
                                $ads['link'] = 'https://facebook.com/' . $object_id;
                                $ads = LikeClone::newAds($ads);
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
                                    $this->coinSerivce->SumCoin($user->id, $check_out_coin);
                                    return ['error_' => $response->message ?? ' Tạo đơn thất bại'];
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
                    case 'facebook_vip_clone_sale_v4':
                        $slbv = $request->slbv;
                        if ($slbv < 5 || ($slbv % 5 != 0)) {
                            return ['error_' => 'Số lượng bài viết không hợp lệ'];
                        }
                        $key = $prices->package_name_master;
//                        $num_day = intval(abs($request->num_day_input));
                        if ($num_day < 1) {
                            return ["error_" => "Vui lòng chọn lại số ngày"];
                        }
                        if ($quantity < 100) {
                            return ["error_" => 'số lượng mua ít nhất 100'];
                        }
                        $check_out_coin = $quantity * $num_day * $pricesMin->prices * ($slbv / 5);
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $form_data = [
                                'uid' => $object_id,
                                'secret_key' => "eyJpdiI6IjFZSUk4M0lwU0lDQUk5QkkxQWVWbGc9PSIsInZhbHVlIjoiaGxCcVJMUUZES1VGbzY2MkhaUmJVSGJDV0w0S3lCZkVvck9udjVKa3Y2dlJYK0lZcUl3UlZBYWo5N1RVbyt6Y0pWRXM1S3VLRUFjOXlFdGdPZ2M5c1E9PSIsIm1hYyI6IjFhYTM5ZDg0OGE3ZjU0NjAzYzYyOWFlMzBjOGNmMGMzOWU5Njk2NDVmYTIzZDlhNmVhZWFkYTUxMTdhNWU5YmEiLCJ0YWciOiIifQ==",
                                'name' => $object_id,
                                'amount' => $quantity,
                                'days' => $num_day,
//                                'limitPost' => intval($slbv),
                                'server' => $key,
                                'note' => $request->notes,
//                                'minutes' => 5,
                            ];
                            $url = DOMAIN_TRUM_LIKE_SUB . '/api/service/facebook/vip-like-clc-new-cc/order';
                            $response = $this->trumLikeSub->callApi($form_data, $url);
//                            dd($response);
                            if ($response && isset($response->status) && $response->status) {
                                $ads = $request->all();
                                $ads['orders_id'] = $response->data->order_id ?? '';
                                $ads['user_id'] = $user->id;
                                $ads['fb_id'] = $object_id;
                                $ads['days'] = $num_day;
                                $now = date('Y-m-d h:m:s');
                                $ads['time_expired'] = date('Y-m-d h:m:s', strtotime($now . '+' . $num_day . ' day'));
                                $ads['username'] = $user->username;
                                $ads['package_name'] = $prices->package_name;
                                $ads['prices'] = $check_out_coin;
                                $ads['price_per'] = $pricesMin->prices;
                                $ads['start_like'] = 0;
                                $ads['price_id'] = $prices->id;
                                $ads['menu_id'] = $prices->menu_id;
                                $ads['server'] = $prices->name;
                                $ads['quantity'] = $quantity;
                                $ads['link'] = 'https://facebook.com/' . $object_id;
                                $ads = LikeClone::newAds($ads);
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
                                    $this->coinSerivce->SumCoin($user->id, $check_out_coin);
                                    $message = $response->message ?? ' Tạo đơn thất bại';
                                    if ($message == 'Số dư không đủ để thực hiện') {
                                        $message = 'Xảy ra lỗi #0';
                                    }
                                    return ['error_' => $message];
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
                    case 'facebook_vip_clone_sale_v5':
                        if ($num_day != 30) {
                            return ['error_' => 'Gói này chỉ có thể mua 30 ngày'];
                        }
                        if ($quantity > 150) {
                            return ['error_' => 'Gói này chỉ có thể mua 150 like'];
                        }
                        $check_out_coin = $quantity * $num_day * $pricesMin->prices;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $ads = $request->all();
                            $ads['user_id'] = $user->id;
                            $ads['fb_id'] = $object_id;
                            $ads['days'] = $num_day;
                            $now = date('Y-m-d h:m:s');
                            $ads['time_expired'] = date('Y-m-d h:m:s', strtotime($now . '+' . $num_day . ' day'));
                            $ads['username'] = $user->username;
                            $ads['package_name'] = $prices->package_name;
                            $ads['prices'] = $check_out_coin;
                            $ads['price_per'] = $pricesMin->prices;
                            $ads['start_like'] = 0;
                            $ads['price_id'] = $prices->id;
                            $ads['menu_id'] = $prices->menu_id;
                            $ads['server'] = $prices->name;
                            $ads['quantity'] = $quantity;
                            $ads['link'] = 'https://facebook.com/' . $object_id;
                            $ads = LikeClone::newAds($ads);
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
                            $data_orders = [
                                'Tên DV' => 'LIKE CLONE',
                                'Loại' => $prices->name,
                                'username' => $user->username,
                                'link' => $object_id,
                                'name' => strtotime('now'),
                                'Số lượng' => $quantity,
                                'Số ngày' => $num_day,
                                'Tiền' => number_format($check_out_coin),
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

                        }
                        return returnDataError($checkCoinAndHandleCoin);
                        break;
                    case 'facebook_vip_clone_sale_v6':
                        $slbv = $request->slbv;
                        $key = $prices->package_name_master;
                        if (!in_array($quantity, [50, 100, 150, 200, 300, 400, 500, 1000])) {
                            return ['error_' => 'gói này chỉ hỗ trợ số lượng 50, 100, 150, 200, 300, 400, 500, 1000'];
                        }
                        if ($num_day != 30) {
                            return ['error_' => 'Gói này chỉ có thể mua 30 ngày'];
                        }
                        $check_out_coin = $quantity * $num_day * $pricesMin->prices * ($slbv / 5);
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $form_data = [
                                'uid' => $object_id,
                                'name' => strtotime('now'),
                                'vip_package' => $quantity,
                                'days' => $num_day,
                                'max_post' => '4',
                            ];
                            $url = DOMAIN_TRUM_LIKE_SUB . '/api/viplike-v2';

                            $response = $this->trumLikeSub->callApi($form_data, $url);
                            if ($response && isset($response->status) && $response->status == 'success') {
                                $ads = $request->all();
                                $ads['orders_id'] = $data->insertId ?? '';
                                $ads['user_id'] = $user->id;
                                $ads['fb_id'] = $object_id;
                                $ads['days'] = $num_day;
                                $now = date('Y-m-d h:m:s');
                                $ads['time_expired'] = date('Y-m-d h:m:s', strtotime($now . '+' . $num_day . ' day'));
                                $ads['username'] = $user->username;
                                $ads['package_name'] = $prices->package_name;
                                $ads['prices'] = $check_out_coin;
                                $ads['price_per'] = $pricesMin->prices;
                                $ads['start_like'] = 0;
                                $ads['price_id'] = $prices->id;
                                $ads['menu_id'] = $prices->menu_id;
                                $ads['server'] = $prices->name;
                                $ads['quantity'] = $quantity;
                                $ads['link'] = 'https://facebook.com/' . $object_id;
                                $ads = LikeClone::newAds($ads);
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
                                    $this->coinSerivce->SumCoin($user->id, $check_out_coin);
                                    return ['error_' => $response->msg ?? ' Tạo đơn thất bại'];
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
                    case 'facebook_vip_clone_sale_v7':
                        $validate = Validator::make($request->all(), [
                            'quantity' => ['required', Rule::in(
                                30, 50, 100, 150, 200, 300, 400, 500, 600, 700, 800, 900, 1000
                            )]
                        ]);
                        if ($validate->fails()) {
                            return ['error_' => 'Số lượng sẽ là 30, 50, 100, 150, 200, 300, 400, 500, 600, 700, 800, 900, 1000'];
                        }
                        $validate = Validator::make($request->all(), [
                            'num_day' => ['required', Rule::in(
                                30, 60, 90
                            )]
                        ]);
                        if ($validate->fails()) {
                            return ['error_' => 'Số ngày mua sẽ là 30, 60, 90 ngày'];
                        }
                        $check_out_coin = $quantity * $num_day * $pricesMin->prices;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $form_data = [
                                'page_id' => $object_id,
                                'num_parkage' => ($num_day / 30),
                                'parkage_id' => $this->getGoiLike($quantity),
                            ];
                            $url = DOMAIN_CONGLIKE . '/api/cai_viplike';

                            $response = $this->congLikeService->callApi($form_data, $url);
                            if ($response && isset($response->code) && $response->code == '100') {
                                $ads = $request->all();
                                $ads['orders_id'] = '';
                                $ads['user_id'] = $user->id;
                                $ads['fb_id'] = $object_id;
                                $ads['days'] = $num_day;
                                $now = date('Y-m-d h:m:s');
                                $ads['time_expired'] = date('Y-m-d h:m:s', strtotime($now . '+' . $num_day . ' day'));
                                $ads['username'] = $user->username;
                                $ads['package_name'] = $prices->package_name;
                                $ads['prices'] = $check_out_coin;
                                $ads['price_per'] = $pricesMin->prices;
                                $ads['start_like'] = 0;
                                $ads['price_id'] = $prices->id;
                                $ads['menu_id'] = $prices->menu_id;
                                $ads['server'] = $prices->name;
                                $ads['quantity'] = $quantity;
                                $ads['link'] = 'https://facebook.com/' . $object_id;
                                $ads = LikeClone::newAds($ads);
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
                                    $this->coinSerivce->SumCoin($user->id, $check_out_coin);
                                    return ['error_' => $response->message ?? ' Tạo đơn thất bại'];
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
                    case 'facebook_vip_clone_sale_v8':
                        $validate = Validator::make($request->all(), [
                            'quantity' => ['required', Rule::in(
                                30, 50, 100, 150, 200, 300, 400, 500, 600, 700, 800, 900, 1000
                            )]
                        ]);
                        if ($validate->fails()) {
                            return ['error_' => 'Số lượng sẽ là 30, 50, 100, 150, 200, 300, 400, 500, 600, 700, 800, 900, 1000'];
                        }
//                        $validate = Validator::make($request->all(), [
//                            'num_day' => ['required', Rule::in(
//                                30, 60, 90
//                            )]
//                        ]);
//                        if ($validate->fails()) {
//                            return ['error_' => 'Số ngày mua sẽ là 30, 60, 90 ngày'];
//                        }
                        $slbv = $request->slbv;
                        if ($slbv < 5 || ($slbv % 5 != 0)) {
                            return ['error_' => 'Số lượng bài viết không hợp lệ'];
                        }
                        $check_out_coin = $quantity * $num_day * $pricesMin->prices * ($slbv / 5);
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $form_data = array(
                                'fb_id' => $object_id,
                                'quantity' => $quantity,
                                'notes' => '',
                                'days' => $num_day,
                                'max_post_daily' => $slbv,
                                'type' => 'vip_like_sv3',
                                'min_like' => $quantity,
                                'max_like' => $quantity,
                                'min_speed' => 0,
                                'max_speed' => 1,
                                'renew' => 1,
                            );
                            $url = DOMAIN_MFB . '/api/facebook-ads/vip-like/buy';
                            $response = $this->mfbService->callApicallMfb($form_data, $url);
                            if ($response && isset($response->status) && $response->status == 200) {
                                $ads = $request->all();
                                $ads['orders_id'] = $response->data->id ?? null;
                                $ads['user_id'] = $user->id;
                                $ads['fb_id'] = $object_id;
                                $ads['days'] = $num_day;
                                $now = date('Y-m-d h:m:s');
                                $ads['time_expired'] = date('Y-m-d h:m:s', strtotime($now . '+' . $num_day . ' day'));
                                $ads['username'] = $user->username;
                                $ads['package_name'] = $prices->package_name;
                                $ads['prices'] = $check_out_coin;
                                $ads['price_per'] = $pricesMin->prices;
                                $ads['start_like'] = 0;
                                $ads['price_id'] = $prices->id;
                                $ads['menu_id'] = $prices->menu_id;
                                $ads['server'] = $prices->name;
                                $ads['quantity'] = $quantity;
                                $ads['total_post'] = $slbv;
                                $ads['link'] = 'https://facebook.com/' . $object_id;
                                $ads = LikeClone::newAds($ads);
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
                                    $this->coinSerivce->SumCoin($user->id, $check_out_coin);
                                    return ['error_' => $response->message ?? ' Tạo đơn thất bại'];
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
                    case 'facebook_vip_clone_sale_v9':
                        $reaction_type = 'LIKE|';
                        if (!in_array($num_day, [30, 60, 90])) {
                            return ["error_" => "Gói này chỉ áp dụng 30,60,90 ngày"];
                        }
                        if ($request->object_type && is_array($request->object_type)) {
                            $reaction_type = strtoupper(implode("|", $request->object_type));
                        }
                        $domain = DOMAIN_SA_BOM_MO . "/api/vip-order?access_token=" . getConfig('sabommo');


                        $key = $prices->package_name_master;
                        $check_out_coin = $quantity * $pricesMin->prices * ($num_day);
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $form_data = [
                                'service_id' => 'vip-like',
                                'seeding_uid' => $object_id,
                                'server_id' => 1,
                                'month' => ($num_day / 30),
                                'reaction_type' => "LIKE",
                                'commend_need' => "",
                                'order_amount' => $quantity,
                            ];
                            $url = DOMAIN_SA_BOM_MO . '/api/index.php';
                            $response = $this->saBomMoService->buyVipV2($form_data, $url);
                            if ($response && isset($response->result) && $response->result && isset($response->order_id) && $response->order_id > 1) {
                                $ads = LikeClone::newAds([
                                    'orders_id' => $response->order_id ?? 0,
                                    'package_name' => $package_name,
                                    'server' => $prices->name,
                                    'user_id' => $user->id,
                                    'username' => $user->username,
                                    'client_id' => $request->get('client_id') ?? 0, 'user_id_agency_lv2' => $request->get('user_id_agency_lv2') ?? 0,
                                    'client_username' => $request->get('client_username') ?? '', 'username_agency_lv2' => $request->get('username_agency_lv2') ?? '',
                                    'fb_id' => $object_id,
                                    'fb_name' => $object_id,
                                    'min_like' => $quantity,
                                    'max_like' => $quantity,
                                    'quantity' => $quantity,
                                    'days' => $num_day,
                                    'time_expired' => date('Y-m-d h:m:s', strtotime(date('Y-m-d h:m:s') . '+' . $num_day . ' day')),
                                    'total_post' => 5,
                                    'max_post_daily' => 5,
                                    'min_delay' => 5,
                                    'max_delay' => 5,
                                    'pause' => 0,
                                    'description' => null,
                                    'prices' => $check_out_coin,
                                    'price_per' => $pricesMin->prices,
                                    'notes' => $request->get('notes'),
                                    'result' => json_encode($response ?? []),
                                    'list_message' => $request->get('list_message'),
                                    'price_per_agency' => $request->get('price_per_agency'),
                                    'prices_agency' => $request->get('prices_agency'),
                                ]);
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
                                    $this->coinSerivce->SumCoin($user->id, $check_out_coin);
                                    return ['error_' => $response->msg ?? ' Tạo đơn thất bại'];
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
                    case 'facebook_vip_clone_sale_v10':
                        if ($num_day != 30) {
                            return ["error_" => "Gói này chỉ áp dụng 30 ngày"];
                        }
                        $check_out_coin = $quantity * $pricesMin->prices * ($num_day);
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $p_data = array(
                                'object_id' => $object_id,
                                'product_name' => $prices->package_name_master,
                                'quantity' => $quantity,
                                'days' => $num_day,
                                'notes' => $data['notes'] ?? '',
                                'confirm' => 1,
                            );
                            $response = $this->farmService->addVip($p_data);
                            if ($response && isset($response->result->order_id)) {
                                $ads = LikeClone::newAds([
                                    'orders_id' => $response->result->order_id ?? 0,
                                    'user_id' => $user->id,
                                    'username' => $user->username,
                                    'client_id' => $request->get('client_id') ?? 0, 'user_id_agency_lv2' => $request->get('user_id_agency_lv2') ?? 0,
                                    'client_username' => $request->get('client_username') ?? '', 'username_agency_lv2' => $request->get('username_agency_lv2') ?? '',
                                    'fb_id' => $object_id,
                                    'package_name' => $package_name,
                                    'fb_name' => $object_id,
                                    'min_like' => $quantity,
                                    'max_like' => $quantity,
                                    'quantity' => $quantity,
                                    'days' => $num_day,
                                    'server' => $prices->name,
                                    'time_expired' => date('Y-m-d h:m:s', strtotime(date('Y-m-d h:m:s') . '+' . $num_day . ' day')),
                                    'total_post' => 5,
                                    'max_post_daily' => 5,
                                    'min_delay' => 5,
                                    'max_delay' => 5,
                                    'pause' => 0,
                                    'description' => null,
                                    'prices' => $check_out_coin,
                                    'price_per' => $pricesMin->prices,
                                    'notes' => $request->get('notes'),
                                    'result' => json_encode($response ?? []),
                                    'list_message' => $request->get('list_message'),
                                    'price_per_agency' => $request->get('price_per_agency'),
                                    'prices_agency' => $request->get('prices_agency'),
                                ]);
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
                                    $this->coinSerivce->SumCoin($user->id, $check_out_coin);
                                    return ['error_' => $response->messages ?? ' Tạo đơn thất bại'];
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
                    case 'facebook_vip_clone_sale_v1':
                    case 'facebook_vip_clone_sale_v3':
                        $validate = Validator::make($request->all(), [
                            'quantity' => ['required', Rule::in(
                                50, 100, 150, 200, 300, 400, 500, 600, 700, 800, 900, 1000
                            )]
                        ]);
                        if ($validate->fails()) {
                            return ['error_' => 'Số lượng sẽ là 50, 100, 150, 200, 300, 400, 500, 600, 700, 800, 900, 1000'];
                        }
                        $slbv = $request->slbv;
                        if ($slbv < 5) {
                            return ['error_' => 'Số lượng bài viết ít nhất 5 bài'];
                        }
                        $check_out_coin = $quantity * $num_day * $pricesMin->prices * ($slbv / 5);
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $url = DOMAIN_TRAODOISUB . '/mua/viplike/themid.php';
                            $time = time() . '_' . str_rand(3);
                            $data = [
                                'maghinho' => $time,
                                'id' => $object_id,
                                'sever' => $prices->package_name_master,
                                'time_pack' => $num_day,
                                'packet' => $quantity,
                                'post' => $slbv,
                                'dateTime' => urlencode(date('Y-m-d H:i:s')),
                            ];
                            $response = $this->traoDoiSubService->buy($url, $data);
                            if ($response && $response == 'Mua thành công!') {
                                $ads = $request->all();
                                $ads['orders_id'] = $time;
                                $ads['user_id'] = $user->id;
                                $ads['fb_id'] = $object_id;
                                $ads['days'] = $num_day;
                                $now = date('Y-m-d h:m:s');
                                $ads['time_expired'] = date('Y-m-d h:m:s', strtotime($now . '+' . $num_day . ' day'));
                                $ads['username'] = $user->username;
                                $ads['package_name'] = $prices->package_name;
                                $ads['prices'] = $check_out_coin;
                                $ads['price_per'] = $pricesMin->prices;
                                $ads['start_like'] = 0;
                                $ads['price_id'] = $prices->id;
                                $ads['menu_id'] = $prices->menu_id;
                                $ads['server'] = $prices->name;
                                $ads['quantity'] = $quantity;
                                $ads['total_post'] = $slbv;
                                $ads['link'] = 'https://facebook.com/' . $object_id;
                                $ads = LikeClone::newAds($ads);
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
                                    $this->coinSerivce->SumCoin($user->id, $check_out_coin);
                                    return ['error_' => $response->message ?? ' Tạo đơn thất bại'];
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
                    default:
                        return ['error_' => 'Gói bảo trì'];
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

    public function actionRemove($request, $user, $id)
    {
        $log = LikeClone::where('id', $id)->where('status', '<>', 0)->where(function ($q) use ($user) {
            if ($user->role != 'admin') {
                $q->where('user_id', $user->id);
            }
        })->first();
        $prices_remove = 5000;
        $d = 7;
        if ($log) {
            $check = Refund::where('orders_id', $log->id)->where('table', 'vip_facebook')->first();
            if ($check) {
                return ['error_' => 'Đơn này đã được hoàn tièn'];
            }
            //$check_out_coin = $quantity * $num_day * $pricesMin->prices * ($slbv / 5);
            switch ($log->package_name) {
                case 'facebook_vip_clone_sale_v2'://autofb
                    $url = DOMAIN_AUTOFB_PRO . '/api/fbvip/cancelorder?fbvip_type=viplike_clone';
                    $data = [
                        'id' => $log->orders_id
                    ];
                    $date = Carbon::create($log->time_expired);
                    $now = Carbon::create(date('Y-m-d H:i:s'));

                    $days = $now->diffInDays($date) - $d;
                    if ($days < 1) {
                        return ['error' => 'Vip này gần hết hạn không thể dừng'];
                    }
                    $check_out_coin = ($log->quantity * $days * $log->price_per * ($log->total_post / 5)) - 5000;
                    $response = $this->autoFbProService->callApi($data, $url);
                    if ($response && isset($response->status) && $response->status == 200) {
                        $log->status = 0;
                        $log->save();
                        $message = "Hủy thành công. Đơn hàng của bạn quá nhỏ không thể hoàn tiền";
                        if ($days > 1 && $check_out_coin > 0) {
                            $message = ("Hủy đơn thành công. Hệ thống sẽ hoàn tiền sau vài giờ");
                            Logs::newLogs([
                                'user_id' => $log->user_id,
                                'username' => $log->username,
                                'client_id' => $request->get('client_id') ?? 0, 'user_id_agency_lv2' => $request->get('user_id_agency_lv2') ?? 0,
                                'client_username' => $request->get('client_username') ?? '', 'username_agency_lv2' => $request->get('username_agency_lv2') ?? '',
                                'action' => 'remove_vip',
                                'action_coin' => 'out',
                                'type' => 'out',
                                'description' => 'Hủy đơn thành công ' . $log->server . ' cho ' . $log->fb_id,
                                'coin' => 0,
                                'old_coin' => $user->coin,
                                'new_coin' => $user->coin - 0,
                                'price_id' => $log->price_id,
                                'object_id' => $log->object_id,
                                'post_data' => json_encode($request->all()) . "\n" . json_encode($user),
                                'result' => json_encode($response ?? []),
                                'ip' => $request->ip(),
                            ]);
                            try {
                                if ($user->role == 'admin' && Auth::check()) {
                                    Logs::newLogsAdmin([
                                        'user_id' => Auth::user()->id,
                                        'username' => Auth::user()->username,
                                        'client_user_id' => null,
                                        'client_username' => null,
                                        'action' => 'log_admin',
                                        'action_coin' => 'in',
                                        'type' => 'log_admin',
                                        'description' => 'Hủy đơn cho uid' . $log->object_id,
                                        'coin' => 0,
                                        'old_coin' => 0,
                                        'new_coin' => 0,
                                        'price_id' => 0,
                                        'object_id' => $log->object_id,
                                        'post_data' => json_encode($request->all()),
                                        'result' => true,
                                        'ip' => $request->ip(),
                                    ]);
                                }
                                Refund::newRefund([
                                    'user_id' => $log->user_id,
                                    'username' => $log->username,
                                    'client_id' => $log->client_id,
                                    'client_username' => $log->client_username,
                                    'object_id' => $log->fb_id,
                                    'coin' => $check_out_coin,
                                    'quantity' => $log->quantity,
                                    'price_per_agency' => $log->price_per_agency,
                                    'prices_agency' => $log->prices_agency,
                                    'description' => 'Hệ thống hoàn ' . $check_out_coin . ' cho vip id ' . $log->id . '  số ngày còn lại ' . $days . '(đã - 7 ngày) và trừ phí dịch vụ 5000',
                                    'status' => 0,
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
                                    'table' => 'vip_facebook',
                                    'days' => $days,
                                ]);
                            } catch (\Exception $exception) {

                            }
                        }
                        return ['success' => $message];
                    }
                    return ['error_' => $response->message ?? "Hủy thất bại"];
                    break;
                case 'facebook_vip_clone_sale_v8':
                    //https://api.mfb.vn/api/facebook-ads/vip-like/remove/7132 //method post
                    $date = Carbon::create($log->time_expired);
                    $now = Carbon::create(date('Y-m-d H:i:s'));
                    $days = $now->diffInDays($date) - $d;
                    $url = DOMAIN_MFB . '/api/facebook-ads/vip-like/remove/' . $log->orders_id;
                    $check_out_coin = ($log->quantity * $days * $log->price_per * ($log->total_post / 5)) - 5000;
                    $response = $this->mfbService->callApicallMfb([], $url);
                    if ($response && isset($response->status) && $response->status == 200) {
                        $log->status = 0;
                        $log->save();
                        $message = "Hủy thành công. Đơn hàng của bạn quá nhỏ không thể hoàn tiền";
                        if ($days > 1 && $check_out_coin > 0) {
                            $message = ("Hủy đơn thành công. Hệ thống sẽ hoàn tiền sau vài giờ");
                            Logs::newLogs([
                                'user_id' => $log->user_id,
                                'username' => $log->username,
                                'client_id' => $request->get('client_id') ?? 0, 'user_id_agency_lv2' => $request->get('user_id_agency_lv2') ?? 0,
                                'client_username' => $request->get('client_username') ?? '', 'username_agency_lv2' => $request->get('username_agency_lv2') ?? '',
                                'action' => 'remove_vip',
                                'action_coin' => 'out',
                                'type' => 'out',
                                'description' => 'Hủy đơn thành công ' . $log->server . ' cho ' . $log->fb_id,
                                'coin' => 0,
                                'old_coin' => $user->coin,
                                'new_coin' => $user->coin - 0,
                                'price_id' => $log->price_id,
                                'object_id' => $log->object_id,
                                'post_data' => json_encode($request->all()) . "\n" . json_encode($user),
                                'result' => json_encode($response ?? []),
                                'ip' => $request->ip(),
                            ]);
                            try {
                                if ($user->role == 'admin' && Auth::check()) {
                                    Logs::newLogsAdmin([
                                        'user_id' => Auth::user()->id,
                                        'username' => Auth::user()->username,
                                        'client_user_id' => null,
                                        'client_username' => null,
                                        'action' => 'log_admin',
                                        'action_coin' => 'in',
                                        'type' => 'log_admin',
                                        'description' => 'Hủy đơn cho uid' . $log->object_id,
                                        'coin' => 0,
                                        'old_coin' => 0,
                                        'new_coin' => 0,
                                        'price_id' => 0,
                                        'object_id' => $log->object_id,
                                        'post_data' => json_encode($request->all()),
                                        'result' => true,
                                        'ip' => $request->ip(),
                                    ]);
                                }
                                Refund::newRefund([
                                    'user_id' => $log->user_id,
                                    'username' => $log->username,
                                    'client_id' => $log->client_id,
                                    'client_username' => $log->client_username,
                                    'object_id' => $log->fb_id,
                                    'coin' => $check_out_coin,
                                    'quantity' => $log->quantity,
                                    'price_per_agency' => $log->price_per_agency,
                                    'prices_agency' => $log->prices_agency,
                                    'description' => 'Hệ thống hoàn ' . $check_out_coin . ' cho vip id ' . $log->id . '  số ngày còn lại ' . $days . '(đã - 7 ngày) và trừ phí dịch vụ 5000',
                                    'status' => 0,
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
                                    'table' => 'vip_facebook',
                                    'days' => $days,
                                ]);
                            } catch (\Exception $exception) {

                            }
                        }
                        return ['success' => $message];
                    }
                    return ['error_' => $response->message ?? "Hủy thất bại"];
                    break;
                case 'facebook_vip_clone_sale_v10':
                    $date = Carbon::create($log->time_expired);
                    $now = Carbon::create(date('Y-m-d H:i:s'));
                    $days = $now->diffInDays($date) - $d;
                    $url = DOMAIN_FARM . '/seller/ordervip/confirm';
                    $check_out_coin = ($log->quantity * $days * $log->price_per * 1) - 5000;

                    $response = $this->farmService->actionOrderVip([
                        'orders_id' => $log->orders_id
                    ]);
                    if ($response && isset($response->status) && $response->status == 200) {
                        $message = "Hủy thành công. Đơn hàng của bạn quá nhỏ không thể hoàn tiền";
                        if ($days > 1 && $check_out_coin > 0) {
                            $message = ("Hủy đơn thành công. Hệ thống sẽ hoàn tiền sau vài giờ");
                            $log->status = 0;
                            $log->save();
                            Logs::newLogs([
                                'user_id' => $log->user_id,
                                'username' => $log->username,
                                'client_id' => $request->get('client_id') ?? 0, 'user_id_agency_lv2' => $request->get('user_id_agency_lv2') ?? 0,
                                'client_username' => $request->get('client_username') ?? '', 'username_agency_lv2' => $request->get('username_agency_lv2') ?? '',
                                'action' => 'remove_vip',
                                'action_coin' => 'out',
                                'type' => 'out',
                                'description' => 'Hủy đơn thành công ' . $log->server . ' cho ' . $log->fb_id,
                                'coin' => 0,
                                'old_coin' => $user->coin,
                                'new_coin' => $user->coin - 0,
                                'price_id' => $log->price_id,
                                'object_id' => $log->object_id,
                                'post_data' => json_encode($request->all()) . "\n" . json_encode($user),
                                'result' => json_encode($response ?? []),
                                'ip' => $request->ip(),
                            ]);
                            try {
                                if ($user->role == 'admin' && Auth::check()) {
                                    Logs::newLogsAdmin([
                                        'user_id' => Auth::user()->id,
                                        'username' => Auth::user()->username,
                                        'client_user_id' => null,
                                        'client_username' => null,
                                        'action' => 'log_admin',
                                        'action_coin' => 'in',
                                        'type' => 'log_admin',
                                        'description' => 'Hủy đơn cho uid' . $log->object_id,
                                        'coin' => 0,
                                        'old_coin' => 0,
                                        'new_coin' => 0,
                                        'price_id' => 0,
                                        'object_id' => $log->object_id,
                                        'post_data' => json_encode($request->all()),
                                        'result' => true,
                                        'ip' => $request->ip(),
                                    ]);
                                }
                                Refund::newRefund([
                                    'user_id' => $log->user_id,
                                    'username' => $log->username,
                                    'client_id' => $log->client_id,
                                    'client_username' => $log->client_username,
                                    'object_id' => $log->fb_id,
                                    'coin' => $check_out_coin,
                                    'quantity' => $log->quantity,
                                    'price_per_agency' => $log->price_per_agency,
                                    'prices_agency' => $log->prices_agency,
                                    'description' => 'Hệ thống hoàn ' . $check_out_coin . ' cho vip id ' . $log->id . '  số ngày còn lại ' . $days . '(đã - 7 ngày) và trừ phí dịch vụ 5000',
                                    'status' => 0,
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
                                    'table' => 'vip_facebook',
                                    'days' => $days,
                                ]);
                            } catch (\Exception $exception) {

                            }
                        }
                        return ['success' => $message];
                    }
                    return ['error_' => $response->message ?? "Hủy thất bại"];
                    break;
                case 'facebook_vip_clone_sale_v9':
                    //action=refund-orders-vip-api&id_user=&token=&id=1
                    $date = Carbon::create($log->time_expired);
                    $now = Carbon::create(date('Y-m-d H:i:s'));
                    $days = $now->diffInDays($date) - $d;
                    $check_out_coin = ($log->quantity * $days * $log->price_per * 1) - 5000;
                    $response = $this->saBomMoService->callApi([
                        'action' => 'refund-orders-vip-api',
                        'id' => $log->orders_id
                    ]);
                    if ($response && isset($response->result) && $response->result == true) {
                        $message = "Hủy thành công. Đơn hàng của bạn quá nhỏ không thể hoàn tiền";
                        if ($days > 1 && $check_out_coin > 0) {
                            $message = ("Hủy đơn thành công. Hệ thống sẽ hoàn tiền sau vài giờ");
                            $log->status = 0;
                            $log->save();
                            Logs::newLogs([
                                'user_id' => $log->user_id,
                                'username' => $log->username,
                                'client_id' => $request->get('client_id') ?? 0, 'user_id_agency_lv2' => $request->get('user_id_agency_lv2') ?? 0,
                                'client_username' => $request->get('client_username') ?? '', 'username_agency_lv2' => $request->get('username_agency_lv2') ?? '',
                                'action' => 'remove_vip',
                                'action_coin' => 'out',
                                'type' => 'out',
                                'description' => 'Hủy đơn thành công ' . $log->server . ' cho ' . $log->fb_id,
                                'coin' => 0,
                                'old_coin' => $user->coin,
                                'new_coin' => $user->coin - 0,
                                'price_id' => $log->price_id,
                                'object_id' => $log->object_id,
                                'post_data' => json_encode($request->all()) . "\n" . json_encode($user),
                                'result' => json_encode($response ?? []),
                                'ip' => $request->ip(),
                            ]);
                            try {
                                if ($user->role == 'admin' && Auth::check()) {
                                    Logs::newLogsAdmin([
                                        'user_id' => Auth::user()->id,
                                        'username' => Auth::user()->username,
                                        'client_user_id' => null,
                                        'client_username' => null,
                                        'action' => 'log_admin',
                                        'action_coin' => 'in',
                                        'type' => 'log_admin',
                                        'description' => 'Hủy đơn cho uid' . $log->object_id,
                                        'coin' => 0,
                                        'old_coin' => 0,
                                        'new_coin' => 0,
                                        'price_id' => 0,
                                        'object_id' => $log->object_id,
                                        'post_data' => json_encode($request->all()),
                                        'result' => true,
                                        'ip' => $request->ip(),
                                    ]);
                                }
                                Refund::newRefund([
                                    'user_id' => $log->user_id,
                                    'username' => $log->username,
                                    'client_id' => $log->client_id,
                                    'client_username' => $log->client_username,
                                    'object_id' => $log->fb_id,
                                    'coin' => $check_out_coin,
                                    'quantity' => $log->quantity,
                                    'price_per_agency' => $log->price_per_agency,
                                    'prices_agency' => $log->prices_agency,
                                    'description' => 'Hệ thống hoàn ' . $check_out_coin . ' cho vip id ' . $log->id . '  số ngày còn lại ' . $days . '(đã - 7 ngày) và trừ phí dịch vụ 5000',
                                    'status' => 0,
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
                                    'table' => 'vip_facebook',
                                    'days' => $days,
                                ]);
                            } catch (\Exception $exception) {

                            }
                        }
                        return ['success' => $message];
                    }
                    return ['error_' => $response->message ?? "Hủy thất bại"];
                    break;
                default:
                    return ['error_' => 'Gói này không hỗ trợ hủy'];
                    break;
            }
        }
        return ['error_' => 'Không tìm thấy đơn này'];
    }

    public function getGoiLike($quantity)
    {
        switch ($quantity) {
            case 30:
                return 1;
                break;
            case 50:
                return 3;
                break;
            case 100:
                return 5;
                break;
            case 200:
                return 6;
                break;
            case 150:
                return 12;
                break;
            case 400:
                return 16;
            case 500:
                return 19;
            case 600:
                return 25;
            case 700:
                return 26;
            case 800:
                return 27;
            case 900:
                return 28;
            case 1000:
                return 29;
                break;
            default:
                return 0;
        }
    }

    public function renewApi(Request $request, $id)
    {
        $log = LikeClone::where('id', $id)->where(function ($q) use ($request) {
            if ($request->user->role != 'admin') {
                $q->where('user_id', $request->user->id);
            }
        })->first();
        if ($log) {
            $request->request->add([
                'package_name' => $log->package_name,
                'quantity' => $log->quantity,
                'num_day' => $log->days,
                'object_id' => $log->object_id,
                'slbv' => $log->total_post,
            ]);
            return $this->returnActionApi($this->actionBuy($request, $request->user));
        }
        $this->res['message'] = "Không tìm thấy đơn này";
        return returnResponseError($this->res);
    }

}

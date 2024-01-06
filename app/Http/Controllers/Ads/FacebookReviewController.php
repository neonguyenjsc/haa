<?php

namespace App\Http\Controllers\Ads;

use App\Http\Controllers\Controller;
use App\Models\Ads\Facebook\Eyes;
use App\Models\Ads\Facebook\Facebook;
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

class FacebookReviewController extends Controller
{
    //

    protected $menu_id = 55;


    public function index()
    {
        $user = Auth::user();
        $menu = Menu::find($this->menu_id);
        $package = PricesConfig::getPricesByLevel($this->menu_id, $user->level);
        return view('Ads.Facebook.Review.index', ['menu' => $menu, 'package' => $package]);
    }

    public function history(Request $request)
    {
        $menu = Menu::find($this->menu_id);
        $data = $this->getHistory('facebook', $this->menu_id, $request);
        return view('Ads.Facebook.Review.history', ['data' => $data, 'menu' => $menu]);
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
            'list_message' => 'required',

            'object_id' => 'required|string|max:190'
        ], [
            'package_name.required' => 'Vui lòng chọn gói'
        ]);
        if ($validate->fails()) {
            return ['error' => $validate->errors()];
        }
        $list_message = array_filter(explode("\n", $request->list_message));
        $check = false;
        $str = 'Nội dung dòng thứ ';
        foreach ($list_message as $i => $item) {
            $length = strlen($item);
            if ($length <= 25) {
                $check = true;
                $str = $str . ($i + 1) . ",";
            }
        }
        if ($check) {
            $str = $str . " phải đạt 25 ký tự";
            return ['error_' => $str];
        }
        $list_message = array_filter(explode("\n", $request->get('list_message')));
        $quantity = abs(intval(count($list_message)));
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
                    case 'facebook_rating_page_sv1':
                    case 'facebook_rating_page_sv2':
                    case 'facebook_checkin_page_sv3':
                    case 'facebook_checkin_page_sv4':
                        $params = \request()->all();
                        $data_master = json_decode($prices->package_name_master);
                        $form_data = [
                            "baohanh" => 0,
                            "gc" => "",
                            "gtmtt" => $data_master->gtmtt ?? -111,
                            "id_user" => 2427,
                            "lhibv" => $params['object_id'],
                            "lnc_battat" => "2",
                            "lnc_dotuoi_den" => "",
                            "lnc_dotuoi_tu" => "",
                            "lnc_namnu" => "3",
                            "lnc_sobanbe_den" => "",
                            "lnc_sobanbe_tu" => "",
                            "lsct" => $data_master->lsct ?? -111,
                            "ndr" => $params['list_message'] ?? '',
                            "slct" => abs(intval($quantity)),
                            "type_api" => 'review_fanpage'
                        ];
                        $url = DOMAIN_AUTOFB_PRO . '/api/facebook_buff/create';
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $response = $this->autoFbProService->callApi($form_data, $url);
                            if ($response && isset($response->status) && $response->status == 200) {
                                $ads = $request->all();
                                $data = $response->data;
                                $ads['orders_id'] = $data->insertId ?? 0;
                                $ads['user_id'] = $user->id;
                                $ads['username'] = $user->username;
                                $ads['link'] = 'https://facebook.com/' . $object_id;
                                $ads['package_name'] = $prices->package_name;
                                $ads['prices'] = $check_out_coin;
                                $ads['price_per'] = $pricesMin->prices;
                                $ads['quantity'] = $quantity;
                                $ads['start_like'] = $data->start_like ?? 0;
                                $ads['price_id'] = $prices->id;
                                $ads['menu_id'] = $prices->menu_id;
                                $ads['server'] = $prices->name;
                                $ads = Facebook::newAds($ads);
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
                                if ($response) {
                                    $this->sumCoin($user->id, $check_out_coin);
                                    return ['error_' => $response->message ?? 'Tạo thất bại'];
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
                                        'post_data' => json_encode($request->except('list_message')),
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
                    case 'facebook_checkin_page_sv5':
                    case 'facebook_checkin_page_sv6_':
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $ads = $request->all();
                            $ads['user_id'] = $user->id;
                            $ads['username'] = $user->username;
                            $ads['link'] = 'https://facebook.com/' . $object_id;
                            $ads['package_name'] = $prices->package_name;
                            $ads['prices'] = $check_out_coin;
                            $ads['price_per'] = $pricesMin->prices;
                            $ads['quantity'] = $quantity;
                            $ads['start_like'] = $data->start_like ?? 0;
                            $ads['price_id'] = $prices->id;
                            $ads['menu_id'] = $prices->menu_id;
                            $ads['orders_id'] = $response->data->id ?? '';
                            $ads['server'] = $prices->name;
                            $ads = Facebook::newAds($ads);
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

                            $data_orders = [
                                //'username' => Auth::user()->username,
                                'Tên DV' => 'ĐÁNH GIÁ PAGE',
                                'link' => $object_id,
                                'Loại' => $prices->name,
//                                'username' => $user->username,
                                'Số lượng' => $quantity,
                                'Tiền' => number_format($check_out_coin),
                                'NỘI DUNG' => $request->list_message,
                                'Ghi chú khách hàng' => $request->get('notes')
                            ];
                            $this->telegramService->sendMessGroupOrder1ToBotTelegram($data_orders);
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
                    case 'facebook_checkin_page_sv6':
                    case 'facebook_checkin_page_sv7':
//                        $regex = "/facebook.com/";
//                        preg_match($regex, $object_id, $data);
//                        //dd($data);
//                        if (count($data) < 1) {
//                            return ['error_' => 'Vui lòng nhập lại link'];
//                        }
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandle = $this->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandle['error'])) {
                            $form_data = [
                                "object_id" => $object_id,
                                "quantity" => $quantity,
                                "type" => 'review_page',
                                "comments" => $request->list_message,
                                "reaction" => $request->object_type ?? 'like',
                            ];
//                            dd($form_data);
                            $url = DOMAIN_BAOSTAR_TOOL . '/api/order';
                            $response = $this->baostarService->callApi($url, $form_data);
                            if ($response && isset($response->status) && $response->status == 200) {
                                $ads = $request->all();
                                $ads['user_id'] = $user->id;
                                $ads['username'] = $user->username;
                                $ads['link'] = 'https://facebook.com/' . $object_id;
                                $ads['package_name'] = $prices->package_name;
                                $ads['prices'] = $check_out_coin;
                                $ads['price_per'] = $pricesMin->prices;
                                $ads['quantity'] = $quantity;
                                $ads['start_like'] = $data->start_num ?? 0;
                                $ads['price_id'] = $prices->id;
                                $ads['menu_id'] = $prices->menu_id;
                                $ads['orders_id'] = $response->id ?? '';
                                $ads['server'] = $prices->name;
                                $ads = Facebook::newAds($ads);
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

                                    return ['error_' => $response->msg ?? 'Tạo thất bại vui lòng liên hệ admin', 'hold' => true];
                                }
                            }
                        } else {
                            return ['error_' => $checkCoinAndHandle['error']];
                        }
                        break;
                        break;
                    default:
                        return ['error_' => 'Gói này bảo trì'];
                        break;
                }
            }
            return returnDataError($checkMinMax);
        }
        return returnDataError($pricesData);
    }

    public function runOrder(Request $request, $id)
    {
        return $this->returnActionWeb($this->runOrderAction($request, Auth::user(), $id));
    }

    public function runOrderApi(Request $request, $id)
    {
        return $this->returnActionApi($this->runOrderAction($request, $request->user, $id));
    }

    public function runOrderAction($request, $user, $id)
    {
        $log = Facebook::where('id', $id)->whereIn('status', [-1])->where(function ($q) use ($user) {
            if ($user->role != 'admin') {
                $q->where('user_id', $user->id);
            }
        })->first();
        if ($log) {
            if (in_array($log->package_name, $log->baostar)) {
                $url = DOMAIN_BAOSTAR_TOOL.'/api/jobs-action/' . $log->orders_id;
                $data = [
                    'action' => 'run'
                ];
                $response = $this->baostarService->actionJobs($url, $data);
                if ($response && $response->status == 200) {
                    $log->status = 1;
                    $log->save();
                    return ['success' => 'Thành công'];
                } else {
                    return ['error_' => $response->messages ?? 'Thất bại'];
                }
            }
        }
        return ['error_' => 'Không tìm thấy đơn này'];
    }

    public function remove(Request $request, $id)
    {
        return $this->returnActionWeb($this->actionRemove($request, Auth::user(), $id));
    }

    public function removeApi(Request $request, $id)
    {
        return $this->returnActionApi($this->actionRemove($request, $request->user, $id));
    }

    public function actionRemove($request, $user, $id)
    {
        $log = Facebook::where('id', $id)->where(function ($q) use ($user) {
            if ($user->role != 'admin') {
                $q->where('user_id', $user->id);
            }
        })->first();
        $prices_remove = $log->price_per_remove;
        if ($log) {
            $check = Refund::where('orders_id', $log->id)->where('table', 'facebook')->first();
            if ($check) {
                return ['error_' => 'Đơn này đã được hoàn tièn'];
            }
            switch ($log->package_name) {
                default:
                    $url = DOMAIN_BAOSTAR_TOOL . '/api/jobs-action/' . $log->orders_id;
                    $data = [
                        'action' => 'remove'
                    ];
                    $response = $this->baostarService->actionJobs($url, $data);
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
                                'table' => 'facebook',
                            ]);
                        } catch (\Exception $exception) {

                        }
                        return ['success' => ("Hủy đơn thành công. Hệ thống sẽ hoàn tiền sau vài giờ")];
                    }
                    return ['error_' => $response->message ?? ("Hủy đơn thất bại")];
                    break;
            }
        }
        return ['error_' => 'Không tìm thấy đơn này'];
    }
}

<?php

namespace App\Http\Controllers\Ads;

use App\Http\Controllers\Controller;
use App\Models\Ads\FacebookVip\VipComment;
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

class FacebookVipCommentController extends Controller
{
    //


    protected $menu_id = 63;


    public function index()
    {
        $user = Auth::user();
        $menu = Menu::find($this->menu_id);
        $package = PricesConfig::getPricesByLevel($this->menu_id, $user->level);
        return view('Ads.Facebook.VipComment.index', ['menu' => $menu, 'package' => $package]);
    }

    public function history(Request $request)
    {
        $menu = Menu::find($this->menu_id);
        if (Auth::user()->role == 'admin') {
            $data = VipComment::where(function ($q) use ($request) {
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
            $data = VipComment::where('user_id', Auth::user()->id)->where(function ($q) use ($request) {
                $key = $request->key;
                if ($key) {
//                    $q->where('username', 'LIKE', '%' . $key . '%');
                    $q->orWhere('client_username', 'LIKE', '%' . $key . '%');
                    $q->orWhere('fb_id', 'LIKE', '%' . $key . '%');
                }
            })->orderBy('id', 'DESC')->paginate(100);
        }

        return view('Ads.Facebook.VipComment.history', ['data' => $data, 'menu' => $menu]);
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
            'quantity' => 'integer|min:5',
            'list_message' => 'required',
            'num_day' => 'int|required|min:30',
            'object_id' => 'required|string|max:190'
        ], [
            'package_name.required' => 'Vui lòng chọn gói'
        ]);
        if ($validate->fails()) {
            return ['error' => $validate->errors()];
        }

        $quantity = abs(intval($request->quantity));
        $num_day = abs(intval($request->num_day));
        $fb_id = $request->object_id;

        $package_name = $request->package_name;
        $object_id = $request->object_id;
        $pricesData = Prices::getPrices($package_name, $user);
        if (!isset($pricesData['error'])) {
            $prices = $pricesData['price'];
            $pricesMin = $pricesData['price_config'];
            $checkMinMax = Prices::checkMinMax($quantity, $prices);
            if (!isset($checkMinMax['error'])) {
                switch ($package_name) {
                    case 'vip_comment_sv2':
                        $slbv = $request->slbv;
                        if ($slbv < 5 || ($slbv % 5 != 0)) {
                            return ['error_' => 'Số lượng bài viết không hợp lệ'];
                        }
                        $check_out_coin = $quantity * $num_day * $pricesMin->prices * ($slbv);
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $form_data = [
//                                'token' => $request->token,
                                'idfb' => $object_id,
                                'days' => $num_day,
                                'done_time' => 10,
                                'daily_post_limit' => $slbv,
                                'comment' => $quantity,
                                'comments' => $request->list_message,
                                'note' => $request->notes ?? time(),
                            ];
                            $url = DOMAIN_TRUM_LIKE_SUB . '/api/vipcmt-v2';

                            $response = $this->trumLikeSub->callApi($form_data, $url);
                            if ($response && isset($response->status) && $response->status == 'success') {
                                $ads = VipComment::newAds([
                                    'orders_id' => $data->insertId ?? 0,
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
                                    'server' => $prices->name,
                                    'package_name' => $prices->package_name,
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
                    case 'vip_comment_sv3':
                        $key = $prices->package_name_master;
                        $pricesMaster = $this->autoFbProService->getPrices($key);
                        $check_out_coin = $quantity * $pricesMin->prices * ($num_day / 30);
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $form_data = [
                                "idfb" => $fb_id,
                                "commentanh" => "",
                                "usernamefb" => $fb_id,
                                "lsct" => "1",
                                "ndcmt" => $request->list_message,
                                "goicmt" => $quantity,
                                "tgsd" => $num_day,
                                "gioitinh" => "all",
                                "tocdocmt" => "5",
                                "randomsticker" => "0",
                                "sticker" => [],
                                "sticker_pack" => (object)[],
                                "gtmtt" => $pricesMaster,
                                "id_user" => 2427
                            ];
                            $url = DOMAIN_AUTOFB_PRO . '/api/fbvip/add?fbvip_type=facebookvipcomment';
                            $response = $this->autoFbProService->callApi($form_data, $url);
                            if ($response && isset($response->status) && $response->status == 200) {
                                $data = $response->data;
                                $ads = VipComment::newAds([
                                    'orders_id' => $data->insertId ?? 0,
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
                                    'server' => $prices->name,
                                    'package_name' => $prices->package_name,
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
                    case 'vip_comment_sv4':
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
                                'commend_need' => $request->list_message,
                                'order_amount' => $quantity,
                            ];
                            $url = DOMAIN_SA_BOM_MO . '/api/index.php';
                            $response = $this->saBomMoService->buyVipV2($form_data, $url);
                            if ($response && isset($response->result) && $response->result && isset($response->order_id) && $response->order_id > 1) {
                                $ads = VipComment::newAds([
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
                    case 'vip_comment_sv5':
                        if ($num_day != 30) {
                            return ["error_" => "Gói này chỉ áp dụng 30 ngày"];
                        }
                        $check_out_coin = $quantity * $pricesMin->prices * ($num_day / 30);
                        $product_id = $this->getProductId($quantity);
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $list_message = array_filter(explode("\n", $request->get('list_message')));
                            $p_data = array(
                                'object_id' => $object_id,
                                'product_id' => $product_id,
                                'quantity' => $quantity,
                                'days' => $num_day,
                                'notes' => $data['notes'] ?? '',
                                'confirm' => 1,
                                'comments' => implode("|", $list_message),
                            );
                            $response = $this->farmService->addVipComment($p_data);
                            if ($response && isset($response->result->order_id)) {
                                $ads = VipComment::newAds([
                                    'orders_id' => $response->result->order_id ?? 0,
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
                                    'server' => $prices->name,
                                    'package_name' => $prices->package_name,
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
                        $key = $prices->package_name_master;
                        $pricesMaster = $this->autoFbProService->getPrices($key);
                        $check_out_coin = $quantity * $pricesMin->prices * ($num_day / 30);
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $form_data = [
                                "idfb" => $fb_id,
                                "commentanh" => "",
                                "usernamefb" => $fb_id,
                                "lsct" => "0",
                                "ndcmt" => $request->list_message,
                                "goicmt" => $quantity,
                                "tgsd" => $num_day,
                                "gioitinh" => "all",
                                "tocdocmt" => "5",
                                "randomsticker" => "0",
                                "sticker" => [],
                                "sticker_pack" => (object)[],
                                "gtmtt" => $pricesMaster,
                                "id_user" => 2427
                            ];
                            $url = DOMAIN_AUTOFB_PRO . '/api/fbvip/add?fbvip_type=facebookvipcomment';
                            $response = $this->autoFbProService->callApi($form_data, $url);
                            if ($response && isset($response->status) && $response->status == 200) {
                                $data = $response->data;
                                $ads = VipComment::newAds([
                                    'orders_id' => $data->insertId ?? 0,
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
                                    'server' => $prices->name,
                                    'package_name' => $prices->package_name,
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
                }
            }
            return returnDataError($checkMinMax);
        }
        return returnDataError($pricesData);
    }


    public function changeComment(Request $request, $id)
    {
        return $this->returnActionWeb($this->changeCommentAction($request, Auth::user(), $id));
    }

    public function changeCommentApi(Request $request, $id)
    {
        return $this->returnActionWeb($this->changeCommentAction($request, $request->user, $id));
    }

    public function changeCommentAction($request, $user, $id)
    {
        $vip = VipComment::where('id', $id)->whereIn('package_name', ['vip_comment_sv2', 'vip_comment_sv4'])->where(function ($q) use ($user) {
            if ($user->role != 'admin') {
                $q->where('user_id', $user->id);
            }
        })->first();
        if ($vip) {
            $data = [
                'id' => $vip->orders_id,
                'ndr' => $request->list_message,
                'tocdocmt' => 5,
                'sticker' => [],
                'sticker_pack' => [],
                'commentanh' => "",
                'status' => 1,
            ];
            $url = DOMAIN_AUTOFB_PRO . '/api/fbvip/editfbvip?fbvip_type=facebookvipcomment';
            $response = $this->autoFbProService->callApi($data, $url);
            if ($response && isset($response->status) && $response->status == 200) {
                return ['success' => 'Thành công'];
            } else {
                return ['error_' => $response->message ?? 'Thất bại'];
            }
        }
        return ['error_' => 'Không tìm thấy đơn này'];
    }

    public function getProductId($quantity)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => DOMAIN_FARM . '/seller/ordervip/products',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('token' => 'test'),
            CURLOPT_HTTPHEADER => array(
                'Cookie: ci_session=7i932hquvebn9397hak6f6io334p2ean'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response);
        if (isset($response->result->products->vip_comment)) {
            foreach ($response->result->products->vip_comment as $item) {
                if ($item->quantity == $quantity) {
                    return $item->product_id;
                }
            }
        }
        return false;
    }
}

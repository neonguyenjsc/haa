<?php

namespace App\Http\Controllers\Ads;

use App\Http\Controllers\Controller;
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

class FacebookFillController extends Controller
{
    //

    protected $menu_id = 52;


    public function index()
    {
        $user = Auth::user();
        $menu = Menu::find($this->menu_id);
        $package = PricesConfig::getPricesByLevel($this->menu_id, $user->level);
        return view('Ads.Facebook.Fill.index', ['menu' => $menu, 'package' => $package]);
    }

    public function getUid(Request $request)
    {
        $data = $request->all();
        $url = DOMAIN_AUTOFB_PRO . '/api/checklinkfb/check';
        $response = $this->autoFbProService->callApi($data, $url);
        $this->res['data'] = $response->data ?? ["group_name" => "Không tìm thấy", "group_id" => null];
        return $this->setResponse($this->res);
    }


    public function history(Request $request)
    {
        $menu = Menu::find($this->menu_id);
        $data = $this->getHistory('facebook', $this->menu_id, $request);
        return view('Ads.Facebook.Fill.history', ['data' => $data, 'menu' => $menu]);
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

            'cookie' => 'required|string'
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
        $profile = $this->checkCookie($request->all());
        if (is_array($profile) && isset($profile['error'])) {
            return ['error_' => $profile['error']];
        }
        if ($profile->data) {
            $profile = $profile->data;
        }
        $pricesData = Prices::getPrices($package_name, $user);
        if (!isset($pricesData['error'])) {
            $prices = $pricesData['price'];
            $pricesMin = $pricesData['price_config'];
            switch ($prices->package_name) {
                case 'facebook_fill_sv2':

                    return ['error_' => 'Bảo trì'];
                    $check_out_coin = $pricesMin->prices;
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
                            'post_data' => json_encode($request->all()),
                            'result' => json_encode($response ?? []),
                            'ip' => $request->ip(),
                            'package_name' => $prices->package_name ?? '',
                            'orders_id' => $ads->id ?? 0,
                        ]);
                        $data_orders = [
                            'Tên DV' => 'LỌC BẠN BÈ',
                            'cookie' => $request->object_id,
                            'Loại' => $prices->name,
                            'username' => $user->username,
                            'Số lượng' => $quantity,
                            'Tiền' => number_format($check_out_coin),
                            'Ghi chú khách hàng' => $request->get('notes')
                        ];
                        $this->telegramService->sendMessGroupOrderFillToBotTelegram($data_orders);
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
                default:
                    $form_data = [
                        "cookie" => $request->cookie,
                        "dtsg" => $profile->dtsg,
                        "idfb" => $profile->uid,
                        "usernamefb" => $profile->name,
                        "id_user" => 2427 // id cua user
                    ];
                    $check_out_coin = $check_out_coin = $pricesMin->prices;
                    $url = DOMAIN_AUTOFB_PRO . '/api/facebooklocbanbekhongtuongtac/add';
                    $checkCoinAndHandle = $this->checkCoinAndHandleCoin($user->id, $check_out_coin);
                    if (!isset($checkCoinAndHandle['error'])) {

                        $response = $this->autoFbProService->callApi($form_data, $url);
                        if ($response && isset($response->status) && $response->status == 200) {
                            $ads = $request->all();
                            $data = $response->data;
                            $ads['object_id'] = $profile->uid;
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
                                $mess = $response->message;
                                return ['error_' => $mess ?? 'Tạo thất bại'];
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
                                    $object_id = $profile->uid;
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
                    } else {
                        return ['error_' => $checkCoinAndHandle['error']];
                    }
                    break;
            }
        }
        return returnDataError($pricesData);
    }


    public function checkCookie($params)
    {
//        $params = \request()->all();
        if (empty($params['cookie'])) {
            return ['error' => 'Vui lòng nhập cookie'];
        }
        $url = DOMAIN_AUTOFB_PRO . '/api/fbbot/checkcookie';
        $rs = $this->autoFbProService->callApi($params, $url);
//        $rs = json_decode($rs);
        if ($rs && $rs->status && $rs->status == 200 && isset($rs->data)) {
            return $rs;
        } else {
            return ['error' => $rs->message ?? 'Không thành công'];
        }
    }
}

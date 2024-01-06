<?php

namespace App\Http\Controllers\Ads;

use App\Http\Controllers\Controller;
use App\Models\Ads\Facebook\BotComment;
use App\Models\Ads\Proxy\Proxy;
use App\Models\Logs;
use App\Models\Menu;
use App\Models\Prices;
use App\Models\PricesConfig;
use App\Models\Refund;
use App\Models\UsersCoin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FacebookBotCommentController extends Controller
{
    //

    protected $menu_id = 56;


    public function index()
    {
        $user = Auth::user();
        $menu = Menu::find($this->menu_id);
        $list_proxy = Proxy::where('user_id', Auth::user()->id)->get();
        $package = PricesConfig::getPricesByLevel($this->menu_id, $user->level);
        return view('Ads.Facebook.BotComment.index', ['menu' => $menu, 'package' => $package, 'proxy' => $list_proxy]);
    }

    public function detail($id)
    {
        $menu = Menu::find($this->menu_id);
        $list_proxy = Proxy::where('user_id', Auth::user()->id)->get();
        $data = BotComment::where('id', $id)->where(function ($q) {
            if (Auth::user()->role != 'admin') {
                $q->where('user_id', Auth::user()->id);
            }
        })->first();
        return view('Ads.Facebook.BotComment.detail', ['menu' => $menu, 'data' => $data, 'proxy' => $list_proxy]);
    }

    public function history(Request $request)
    {
        $menu = Menu::find($this->menu_id);
        $list_proxy = Proxy::where('user_id', Auth::user()->id)->get();
        $data = $this->getHistory('bot-comment', $this->menu_id, $request);
        return view('Ads.Facebook.BotComment.history', ['data' => $data, 'menu' => $menu, 'proxy' => $list_proxy]);
    }

    public function buy(Request $request)
    {
        return $this->returnActionWeb($this->actionBuy($request, Auth::user()));
    }

    public function buyApi(Request $request)
    {
        return $this->returnActionApi($this->actionBuy($request, $request->user));
    }

    public function updateWeb(Request $request)
    {
        return $this->returnActionWeb($this->actionUpdateV2($request, Auth::user()));
    }

    public function updateApi(Request $request)
    {
        return $this->returnActionApi($this->actionUpdate($request, $request->user));
    }

    public function actionUpdateV2($request, $user)
    {
        $va = Validator::make($request->all(), [
            'ctkfcc' => 'required'
        ]);
        if ($va->fails()) {
            return ['error' => $va->errors()];
        }
        $cookie = BotComment::where('id', $request->id_order)->where('user_id', $user->id)->first();
        if ($cookie) {
            $profile = $this->checkCookie($request->all());
            $post_data = (object)[
                "id" => $cookie->orders_id,
                "ctkfcc" => $request->get('ctkfcc'),//cookie
                "cookie" => $request->get('ctkfcc'),//cookie
                "tgctt_tu" => $request->get('tgctt_tu'),//thời gian tương tác từ
                "tgctt_den" => $request->get('tgctt_den'),// thời gian tương tác đến
                "lnncx" => "1",//like ngẫu nhiên cảm xúc
                "lnncx_type" => $request->get('lnncx_type'),//like ngẫu nhiên cảm xúc array
                "lnncx_tdmn" => $request->get('lnncx_tdmn') ?? 200, // like ngẫu nhiên tối đa 1 ngày
                "blbv" => "1",
                "blbv_cmt" => $request->get('blbv_cmt') ?? '',
                "blbv_tdmn" => $request->get('blbv_tdmn') ?? 200,
                "snmcatt" => $request->get('snmcatt') ?? 10,
                "gioitinh_edit" => $request->get('gioitinh_edit') ?? 'all',
                "gc" => "",
                "userAgent" => "",
                "idfb" => $profile->uid,
                "usernamefb" => $profile->name,
                "dtsg" => $profile->dtsg,
                "id_proxy" => $request->id_proxy,
                "ttv" => $request->ttv,
                "gioitinh" => "all",
                "bvtp" => $request->bvtp,
                "s_check_edit" => 0,
                "ca_check_edit" => 0,
                "ghichu" => "",
                "listid" => $request->listid ?? "",
                "blacklisttukhoa" => $request->blacklisttukhoa ?? "",
                "blacklistid" => $request->blacklistid ?? "",
                "sticker" => [
                    "254593389337365",
                    "1458993767465733",
                    "1775284809378890",
                ],
                "commentanh" => "",
                "ca_check" => "0",
                "s_check" => "0",
                "sticker_pack" => [
                    "254593106004060" => [
                        "254593389337365"
                    ],
                    "1440530709312039" => [
                        "1458993767465733"
                    ],
                    "1775273559380015" => [
                        "1775284809378890"
                    ],
                ],
                "newapi" => "0",
                "id_user" => 2427
            ];
            $url = DOMAIN_AUTOFB_PRO . '/api/fbbot/updatecookie?fbbot_type=facebookbotcmt';
            $response = $this->autoFbProService->callApi($post_data, $url);
            if ($response && isset($response->status) && $response->status == 200) {
                return ['success' => 'Cập nhật thành công'];
            } else {
                return ['error_' => 'Cập nhât thất bại'];
            }
        }
        return ['error_' => 'Không tìm thấy đơn này'];
    }

    public function actionUpdate($request, $user)
    {
        $va = Validator::make($request->all(), [
            'ctkfcc' => 'required'
        ]);
        if ($va->fails()) {
            return ['error' => $va->errors()];
        }
        $cookie = BotComment::where('id', $request->id_order)->where('user_id', $user->id)->first();
        if ($cookie) {
            $profile = $this->checkCookie($request->all());
            if (is_array($profile) && isset($profile['error'])) {
                return ['error_' => $profile['error'] ?? 'Vui lòng liên hệ admin'];
            }

            $old_post_data = json_decode($cookie->post_data);
            $post_data = (object)[
                "id" => $cookie->orders_id,
                "ctkfcc" => $request->get('ctkfcc'),//cookie
                "cookie" => $request->get('ctkfcc'),//cookie
                "tgctt_tu" => $old_post_data->tgctt_tu,//thời gian tương tác từ
                "tgctt_den" => $old_post_data->tgctt_den,// thời gian tương tác đến
                "lnncx" => "1",//like ngẫu nhiên cảm xúc
                "lnncx_type" => $old_post_data->lnncx_type,//like ngẫu nhiên cảm xúc array
                "lnncx_tdmn" => $old_post_data->lnncx_tdmn ?? 200, // like ngẫu nhiên tối đa 1 ngày
                "blbv" => "1",
                "blbv_cmt" => $old_post_data->blbv_cmt ?? '',
                "blbv_tdmn" => $old_post_data->blbv_tdmn ?? 200,
                "snmcatt" => $old_post_data->snmcatt ?? 10,
                "gioitinh_edit" => $old_post_data->gioitinh_edit ?? 'all',
                "gc" => "",
                "userAgent" => "",
                "idfb" => $profile->uid,
                "usernamefb" => $profile->name,
                "dtsg" => $profile->dtsg,
                "id_proxy" => $old_post_data->id_proxy,
                "ttv" => $old_post_data->ttv,
                "gioitinh" => "all",
                "bvtp" => $old_post_data->bvtp,
                "s_check_edit" => 0,
                "ca_check_edit" => 0,
                "ghichu" => "",
                "listid" => $old_post_data->listid ?? "",
                "blacklisttukhoa" => $old_post_data->blacklisttukhoa ?? "",
                "blacklistid" => $old_post_data->blacklistid ?? "",
                "sticker" => [
                    "254593389337365",
                    "1458993767465733",
                    "1775284809378890",
                ],
                "commentanh" => "",
                "ca_check" => "0",
                "s_check" => "0",
                "sticker_pack" => [
                    "254593106004060" => [
                        "254593389337365"
                    ],
                    "1440530709312039" => [
                        "1458993767465733"
                    ],
                    "1775273559380015" => [
                        "1775284809378890"
                    ],
                ],
                "newapi" => "0",
                "id_user" => 2427
            ];
            $url = DOMAIN_AUTOFB_PRO . '/api/fbbot/updatecookie?fbbot_type=facebookbotcmt';
            $response = $this->autoFbProService->callApi($post_data, $url);
            if ($response && isset($response->status) && $response->status == 200) {
                return ['success' => 'Cập nhật thành công'];
            } else {
                return ['error_' => 'Cập nhât thất bại'];
            }
        }
        return ['error_' => 'Không tìm thấy đơn này'];
    }

    public function actionBuy($request, $user)
    {
        $validate = Validator::make($request->all(), [
            'package_name' => ['required', Rule::in(
                Prices::getPackageNameAllow($this->menu_id)
            )],
            'ctkfcc' => 'required',

            'snmcatt' => 'integer'
        ], [
            'package_name.required' => 'Vui lòng chọn gói'
        ]);
        if ($validate->fails()) {
            return ['error' => $validate->errors()];
        }

        $package_name = $request->package_name;
        $quantity = abs(intval($request->get('snmcatt')));
        $pricesData = Prices::getPrices($package_name, $user);

        if (!isset($pricesData['error'])) {
            $prices = $pricesData['price'];
            $pricesMin = $pricesData['price_config'];
            $checkMinMax = Prices::checkMinMax(1, $prices);
            $profile = $this->checkCookie($request->all());
            if (is_array($profile) && isset($profile['error'])) {
                return ['error_' => $profile['error']];
            }
            if (true) {
                switch ($package_name) {
                    default:
                        $key = $prices->package_name_master;
                        $pricesMaster = $this->autoFbProService->getPrices($key);

                        $post_data = [
                            "ctkfcc" => $request->get('ctkfcc'),//cookie
                            "tgctt_tu" => $request->get('tgctt_tu'),//thời gian tương tác từ
                            "tgctt_den" => $request->get('tgctt_den'),// thời gian tương tác đến
                            "lnncx" => "1",//like ngẫu nhiên cảm xúc
                            "lnncx_type" => $request->get('lnncx_type'),//like ngẫu nhiên cảm xúc array
                            "lnncx_tdmn" => $request->get('lnncx_tdmn') ?? 200, // like ngẫu nhiên tối đa 1 ngày
                            "blbv" => "1",
                            "blbv_cmt" => $request->get('blbv_cmt') ?? '',
                            "blbv_tdmn" => $request->get('blbv_tdmn') ?? 200,
                            "snmcatt" => $request->get('snmcatt') ?? 10,
                            "gc" => "",
                            "userAgent" => "",
                            "gtmtt" => $pricesMaster,
                            "idfb" => $profile->uid,
                            "usernamefb" => $profile->name,
                            "dtsg" => $profile->dtsg,
                            "id_proxy" => $request->id_proxy,
                            "ttv" => $request->ttv,
                            "gioitinh" => "all",
                            "bvtp" => $request->bvtp,
                            "listid" => $request->listid ?? "",
                            "blacklisttukhoa" => $request->blacklisttukhoa ?? "",
                            "blacklistid" => $request->blacklistid ?? "",
                            "sticker" => [
                                "254593389337365",
                                "1458993767465733",
                                "1775284809378890",
                            ],
                            "commentanh" => "",
                            "ca_check" => "0",
                            "s_check" => "0",
                            "sticker_pack" => [
                                "254593106004060" => [
                                    "254593389337365"
                                ],
                                "1440530709312039" => [
                                    "1458993767465733"
                                ],
                                "1775273559380015" => [
                                    "1775284809378890"
                                ],
                            ],
                            "newapi" => "0",
                            "id_user" => 2427
                        ];
                        $check_out_coin = $pricesMin->prices * $quantity;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $url = DOMAIN_AUTOFB_PRO . '/api/fbbot/add?fbbot_type=facebookbotcmt';
                            $response = $this->autoFbProService->callApi($post_data, $url);
//                            $response = $this->callBot($post_data);
                            if ($response && isset($response->status) && $response->status == 200) {
                                $data = $response->data;
                                $ads = BotComment::newThis([
                                    'orders_id' => $data->insertId ?? '',
                                    'fb_id' => $profile->uid,
                                    'fb_name' => $profile->name,
                                    'days' => intval(abs($request->snmcatt)),
                                    'time_end' => strtotime($this->addDaysWithDate(date('Y-m-d H:i:s'), intval(abs($request->snmcatt)))),
                                    'user_id' => $user->id,
                                    'client_id' => $request->client_id,
                                    'user_id_agency_lv2' => $request->user_id_agency_lv2,
                                    'username' => $user->username,
                                    'client_username' => $request->client_username,
                                    'username_agency_lv2' => $request->username_agency_lv2,
                                    'prices' => $check_out_coin,
                                    'prices_agency' => $request->prices_agency,
                                    'prices_agency_lv2' => $request->prices_agency_lv2,
                                    'price_per' => $prices->prices,
                                    'price_per_agency' => $request->price_per_agency,
                                    'price_per_agency_lv2' => $request->price_per_agency_lv2,
                                    'menu_id' => $this->menu_id,
                                    'package_name' => $prices->package_name,
                                    'server' => $prices->name,
                                    'notes' => $request->notes,
                                    'proxy' => $request->id_proxy,
                                    'object_id' => $profile->uid,
                                    'post_data' => json_encode($post_data),
                                ]);
                                Logs::newLogs([
                                    'user_id' => $user->id,
                                    'username' => $user->username,
                                    'client_id' => $request->get('client_id') ?? 0, 'user_id_agency_lv2' => $request->get('user_id_agency_lv2') ?? 0,
                                    'client_username' => $request->get('client_username') ?? '', 'username_agency_lv2' => $request->get('username_agency_lv2') ?? '',
                                    'action' => 'buy',
                                    'action_coin' => 'out',
                                    'type' => 'out',
                                    'description' => 'Tạo đơn thành công ' . $prices->name,
                                    'coin' => $check_out_coin,
                                    'old_coin' => $user->coin,
                                    'new_coin' => $user->coin - $check_out_coin,
                                    'price_id' => $prices->id,
                                    'object_id' => '',
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
                                    $message = $response->message;
                                    if ($message == 'Tài khoản không đủ tiền!') {
                                        $message = "Xảy ra lỗi #0";
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
                                        'description' => 'Tạo đơn thất bại ' . $prices->name . ' cho ',
                                        'coin' => $check_out_coin,
                                        'old_coin' => $user->coin,
                                        'new_coin' => $user->coin - $check_out_coin,
                                        'price_id' => $prices->id,
                                        'object_id' => '',
                                        'post_data' => json_encode($request->all()),
                                        'result' => json_encode($response ?? []),
                                        'ip' => $request->ip(),
                                    ]);
                                    $object_id = $profile->uid;
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
                        } else {
                            return returnDataError($checkCoinAndHandleCoin);
                        }
                        break;
                }
            }
            return returnDataError($checkMinMax);
        }
        return returnDataError($pricesData);
    }


    public function checkCookie($params)
    {
//        $params = \request()->all();
        if (empty($params['ctkfcc'])) {
            return ['error' => 'Vui lòng nhập cookie'];
        }
        $params['cookie'] = $params['ctkfcc'];
        $url = DOMAIN_AUTOFB_PRO . '/api/fbbot/checkcookie';
        $rs = $this->autoFbProService->callApi($params, $url);
        if ($rs && $rs->status && $rs->status == 200 && isset($rs->data)) {
            return $rs->data;
        } else {
            return ['error' => $rs->message ?? 'Không thành công'];
        }
    }

    public function callBot($data)
    {
        $config = DB::table('config')->where('alias', 'key_ctvsubvn')->first();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://autofb.pro/api/fbbot/add?fbbot_type=facebookbotcmt',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'authority: autofb.pro',
                'sec-ch-ua: " Not;A Brand";v="99", "Google Chrome";v="91", "Chromium";v="91"',
                'accept: application/json, text/plain, */*',
                'sec-ch-ua-mobile: ?0',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'ht-token: ' . $config->value,
                'content-type: application/json',
                'origin: https://autofb.pro',
                'sec-fetch-site: same-origin',
                'sec-fetch-mode: cors',
                'sec-fetch-dest: empty',
                'referer: https://autofb.pro/tool/facebookbotcmt',
                'accept-language: en-US,en;q=0.9',
                'cookie: _ga=GA1.2.436084604.1625851303; _gid=GA1.2.485504555.1625851303'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }
}

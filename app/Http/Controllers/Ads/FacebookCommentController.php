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

class FacebookCommentController extends Controller
{
    //

    protected $package_mfb = [
        'comment' => 'seeding_by_workers_comment'
    ];

    protected $menu_id = 44;


    public function index()
    {
        $user = Auth::user();
        $menu = Menu::find($this->menu_id);
        $package = PricesConfig::getPricesByLevel($this->menu_id, $user->level);
        return view('Ads.Facebook.Comment.index', ['menu' => $menu, 'package' => $package]);
    }

    public function history(Request $request)
    {
        $menu = Menu::find($this->menu_id);
        $data = $this->getHistory('facebook', $this->menu_id, $request);
        return view('Ads.Facebook.Comment.history', ['data' => $data, 'menu' => $menu]);
    }

    public function buy(Request $request)
    {
        return $this->returnActionWeb($this->actionBuy($request, Auth::user()));
    }

    public function buyApi(Request $request)
    {
        return $this->returnActionApi($this->actionBuy($request, $request->user));
    }


    public function checkComment($comment)
    {
        $regex = 'https|http|.com|.xyz|đkm|dkm|cái lồn|cai lon|thang cho|thằng chó|con mẹ mày|con me may|con cac|con cặc|thằng mất dạy|thang mat day|dm|đm|đ m|d m|địt mẹ|dit me|lừađảo|conchó|trảtiền|mấtdạy|lừa đảo|con chó|trả tiền|mất dạy|lua dao|con cho|tra tien|mat day|chính phủ|chinh phu|nhà nước|nha nuoc|đéo|^dm$|^dm | dm| dm |^đm$|^đm | đm$| đm |^cc$| cc|dit|djt|djt me|địt|thằng chó|cmm|clm|con chó|vcc|gomlua|gom lúa|vnnn|vn ngày nay|vn ngay nay|xóa app|xoa app|app sập|app xap|app sap|mẹ | mẹ$|^mẹ$|ngu |^ngu$|cút|biến |^biến$| biến$|mã giới thiệu|ma gioi thieu|^lồn$|lồn | lồn$|^cặc$|cặc | cặc$|auto';
        preg_match('/(' . $regex . ')/', $comment, $check);
        if (isset($check[0])) {
            return ['error' => $check[0]];
        }
        return true;
    }

    public function actionBuy($request, $user)
    {
//        return ['error_' => "Bảo trì"];
        $validate = Validator::make($request->all(), [
            'package_name' => ['required', Rule::in(
                Prices::getPackageNameAllow($this->menu_id)
            )],
            'quantity' => 'integer|min:1',
            'list_message' => 'required',

            'object_id' => 'required|string|max:190'
        ], [
            'package_name.required' => 'Vui lòng chọn gói'
        ]);
        if ($validate->fails()) {
            return ['error' => $validate->errors()];
        }
        $checkComment = $this->checkComment($request->list_message);
        if (isset($checkComment['error'])) {
            $m = 'Nội dung bình luận có từ không hợp lệ: ' . $checkComment['error'];
            if (in_array($checkComment['error'], ['https', 'http'])) {
                $m = "Không được nhập link";
            }
            return ['error_' => $m];
        }

        $quantity = abs(intval($request->quantity));
        $price_per = abs(intval($request->price_per));
        $package_name = $request->package_name;
        $object_id = $request->object_id;
        $pricesData = Prices::getPrices($package_name, $user);
        $list_message = array_filter(explode("\n", $request->get('list_message')));
        $quantity = abs(intval(count($list_message)));
        if (!isset($pricesData['error'])) {
            $prices = $pricesData['price'];
            $pricesMin = $pricesData['price_config'];
            $checkMinMax = Prices::checkMinMax($quantity, $prices);
            if (!isset($checkMinMax['error'])) {
                switch ($package_name) {
                    case 'facebook_comment_sv10':
                        $object_type = $request->object_type ?? 'comment';
                        $priceMaster = $this->tanglikecheoService->getPricesMaster($prices->package_name_master);
                        if (!isset($priceMaster['error'])) {
                            $list_message = explode("\n", $request->get('list_message'));
                            $quantity = abs(intval(count($list_message)));
                            $post_data['object_id'] = $object_id;
                            $post_data['prices'] = $priceMaster * $quantity;
                            $post_data['price'] = $priceMaster;
                            $post_data['type'] = $object_type;
                            $post_data['quantity'] = $quantity;
                            $post_data['object_type'] = $prices->package_name_master;
                            $post_data['list_comment'] = $request->list_message;
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
                                        $message = $response->message;
                                        if ($message == 'Bạn không đủ Xu để tạo Job, vui lòng nạp thêm!') {
                                            $message = "Vui lòng liên hệ admin #0";
                                        }
                                        return ['error_' => $message ?? 'Tạo đơn thất bại'];
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
                                                'table' => 'youtube',
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
                    case 'facebook_comment_sv1':
                        $list_message = array_filter(explode("\n", $request->get('list_message')));
                        $quantity = abs(intval(count($list_message)));
                        $post_data = $request->except('user');
                        $post_data['object_id'] = $object_id;
                        $post_data['type'] = 'comment';
                        $post_data['package_type'] = $prices->package_name_master;
                        $post_data['object_type'] = $request->object_type ?? 'like';
                        $post_data['quantity'] = $quantity;
                        $post_data['list_comment'] = $request->list_message;
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
                                            'table' => 'youtube',
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
                    case 'facebook_comment_sv9':
                        $pricesMaster = $this->mfbService->getPricesMaster(69, $this->package_mfb[$prices->package_name_master]);
                        if ($pricesMaster) {
                            $array_list_message = explode("\n", $request->list_message);
                            $quantity = abs(intval(count($array_list_message)));
                            $check_out_coin = $quantity * $pricesMin->prices;
                            $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                            if (!isset($checkCoinAndHandleCoin['error'])) {
//                                $post_data = $request->except('client_id','client_username');

                                $post_data = $request->only('object_id');
                                $post_data['is_warranty'] = false;
                                $post_data['link'] = 'https://www.facebook.com/' . $object_id;
                                $post_data['prices'] = $pricesMaster * $quantity;
                                $post_data['price_per'] = $pricesMaster;
                                $post_data['time_expired'] = $this->addDaysWithDate(date('Y-m-d H:i:s'), 7);
                                $post_data['type'] = $prices->package_name_master;
                                $post_data['list_messages'] = $array_list_message;
                                $post_data['quantity'] = $quantity;
                                $url = DOMAIN_MFB . '/api/advertising/create';
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
                                                'table' => 'youtube',
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
                    case 'facebook_comment_sv2':
                        $str_s = '';
                        $list_message = array_filter(explode("\n", $request->get('list_message')));
                        $quantity = count($list_message);
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $t = strtotime('now');
                            $data_create_comment = [
                                'name' => "comment.$t",
                                'contents' => $list_message
                            ];
                            $response = $this->autolikeccService->createComment($data_create_comment);
                            if ($response && isset($response->code) && $response->code == 200) {
                                $package_name_master = json_decode($prices->package_name_master);
                                $form_data = [
                                    'url_service' => $object_id,
                                    'type' => $package_name_master->name,
                                    'number' => $quantity,
                                    "speed" => $package_name_master->option,
                                    "comment_id" => $response->data->comment_id,
                                ];
                                $url = DOMAIN_AUTO_LIKE_CC . '/public-api/v1/agency/services/create-V2';
                                $response = $this->autolikeccService->callAutoCC($form_data, $url);
                                if ($response && isset($response->code) && $response->code == 200) {
                                    $data_confirm = [
                                        "transaction_code" => $response->data->transaction_code
                                    ];
                                    $url_confirm = DOMAIN_AUTO_LIKE_CC . '/public-api/v1/agency/services/confirm';
                                    $response_confirm = $this->autolikeccService->callAutoCC($data_confirm, $url_confirm);
                                    if ($response_confirm && isset($response_confirm->code) && $response_confirm->code == 200) {
                                        $ads = $request->all();
                                        $data = $response->data;
                                        $ads['orders_id'] = $response->data->service_codes[0] ?? '';
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
                                        $ads = Facebook::newAds($ads);
                                        Logs::newLogs([
                                            'user_id' => $user->id,
                                            'username' => $user->username,
                                            'client_id' => $request->get('client_id') ?? 0, 'user_id_agency_lv2' => $request->get('user_id_agency_lv2') ?? 0,
                                            'client_username' => $request->get('client_username') ?? '', 'username_agency_lv2' => $request->get('username_agency_lv2') ?? '',
                                            'action' => 'buy',
                                            'action_coin' => 'out',
                                            'type' => 'out',
                                            'description' => 'Tạo đơn thành công ' . $prices->name . ' cho ' . $object_id,
                                            'coin' => $check_out_coin,
                                            'old_coin' => $user->coin,
                                            'new_coin' => $user->coin - $check_out_coin,
                                            'price_id' => $prices->id,
                                            'object_id' => $object_id,
                                            'post_data' => json_encode($request->all()) . $str_s,
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
                                        return ['error_' => "Duyệt đơn thất bại. Vui lòng liên hệ admin", 'hold' => true];
                                    }
                                } else {
                                    $this->coinSerivce->SumCoin($user->id, $check_out_coin);
                                    return ['error_' => $response->message ?? 'Mua thất bại'];
                                }
                            } else {
                                $this->coinSerivce->SumCoin($user->id, $check_out_coin);
                                return ['error_' => $response->message ?? 'Mua thất bại'];
                            }
                        }
                        return returnDataError($checkCoinAndHandleCoin);
                        break;
                    case 'facebook_comment_sv3':
                    case 'facebook_comment_sv4':
                    case 'facebook_comment_sv5':
                        $list_message = array_filter(explode("\n", $request->get('list_message')));
                        $quantity = count($list_message);
                        $post_data['object_id'] = $object_id;
                        $post_data['product_id'] = $prices->package_name_master;
                        $post_data['quantity'] = $quantity;
                        $post_data['notes'] = $request->notes;
                        $post_data['data_comments'] = 69;
                        $post_data['comments'] = implode("|", $list_message);
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandle = $this->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandle['error'])) {
                            $object_type = $request->object_type ?? 'like';
                            $response = $this->farmService->addOrder($post_data);
                            if ($response && isset($response->result->order_id)) {
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
                                $ads['orders_id'] = $response->result->order_id ?? '';
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
                                    $this->coinSerivce->sumCoin($user->id, $check_out_coin);
                                    return ['error_' => $response->messages ?? 'Tạo đơn thất bại'];
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
                                        $this->sendMessGroupCardToBotTelegram("refund error " . $exception->getMessage());
                                    }
                                    return ['error_' => $response->message ?? 'Tạo thất bại vui lòng liên hệ admin', 'hold' => true];
                                }
                            }
                        } else {
                            return ['error_' => $checkCoinAndHandle['error']];
                        }
                        break;
                    case 'facebook_comment_sv6':
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $data = $request->all();
                            $data['action'] = 'add';
                            $data['service'] = $prices->package_name_master;
                            $data['comments'] = $request->get('list_message');
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
                                    $message = $response->error;
                                    if ($response->error == 'Balance not enough !') {
                                        $message = "Lỗi tạo đơn vui lòng liên hệ admin";
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
                    case 'facebook_comment_sv7':
                        $regex = "/facebook.com/";
                        preg_match($regex, $object_id, $data);
                        //dd($data);
                        if (count($data) < 1) {
                            return ['error_' => 'Vui lòng nhập lại link'];
                        }
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandle = $this->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandle['error'])) {
                            $form_data = [
                                "object_id" => $object_id,
                                "quantity" => $quantity,
                                "type" => 'comment',
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
                    default:
                        $check_out_coin = $pricesMin->prices;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $ads = $request->all();
                            $ads['user_id'] = $user->id;
                            $ads['username'] = $user->username;
                            $pos = strpos($object_id, 'facebook.com');
                            if ($pos !== false) {
                                $ads['object_id'] = $object_id;
                                $ads['link'] = $object_id;
                            } else {
                                $ads['object_id'] = $object_id;
                                $ads['link'] = 'https://www.facebook.com/' . $object_id;
                            }
                            if (isset($request->speed) && $request->speed == '2') {
                                $ads['type'] = 'comment_speed';
                            } else {
                                $ads['type'] = 'comment';
                            }
                            $ads['link'] = 'https://facebook.com/' . $object_id;
                            $ads['package_name'] = $prices->package_name;
                            $ads['object_id'] = $object_id;
                            $ads['prices'] = $check_out_coin;
                            $ads['price_per'] = $pricesMin->prices;
                            $ads['quantity'] = 1;
                            $ads['start_like'] = 0;
                            $ads['price_id'] = $prices->id;
                            $ads['menu_id'] = $prices->menu_id;
                            $ads['orders_id'] = 0;
                            $ads['server'] = $prices->name;
                            $ads['list_message'] = $request->list_message;
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
                        }
                        return returnDataError($checkCoinAndHandleCoin);
                        break;
                }
            }
            return returnDataError($checkMinMax);
        }
        return returnDataError($pricesData);
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
        $log = Facebook::where('id', $id)->where('menu_id', $this->menu_id)->whereIn('status', [0, 1, 5, -1])->where(function ($q) use ($user) {
            if ($user->role != 'admin') {
                $q->where('user_id', $user->id);
            }
        })->first();
        $item = $log;
        if (!$log) {
            return ['error_' => 'Có thể đơn này đã hoàn thành. Hoặc đã hủy'];
        }
        if (in_array($log->package_name, $log->autolike)) {
            $response = $this->autolikeccService->callAutoCC(['service_codes' => [$item->orders_id]], DOMAIN_AUTO_LIKE_CC . '/public-api/v1/agency/services/all-by-codes');
            $item->count_is_run = $response->data[0]->number_success_int ?? $item->count_is_run;
            if (isset($response->data[0]->follows_start)) {
                $item->start_like = $response->data[0]->follows_start;
            }
            if ($item->status != 7) {
                if ($response->data[0]->status != 'Active') {
                    $item->status = 0;
                }
                if ($response->data[0]->status == 'Report') {
                    $item->status = 3;
                }
                if ($response->data[0]->status == 'Active') {
                    $item->status = 1;
                }
                if ($item->count_is_run >= $item->quantity) {
                    $item->status = 2;
                    $item->warranty = 1;
                }
            }
        }
        if (in_array($log->package_name, $log->farm)) {
            $response = $this->farmService->checkOrder(['orders_id' => $log->orders_id]);
            if (isset($response->result->order)) {
                $log->count_is_run = $response->result->order->job_success;
                $log->start_like = str_replace(".", "", $response->result->order->initial_interaction->like);
                if ($response->result->order->status == 'pause') {
                    $log->status = -1;
                }
                if ($response->result->order->status == 'processing') {
                    $log->status = 1;
                }
                if ($log->count_is_run >= $log->quantity) {
                    $log->status = 2;
                    $log->warranty = 1;
                }
                $log->save();
                if (isset($response->result->order->status)) {
                    if ($response->result->order->status == "cancelled" && $response->result->order->confirm == 'confirmed') {
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
                                'table' => 'facebook',
                            ]);
                        } catch (\Exception $exception) {
                        }
                    }
                }
            }
        }
        if (in_array($log->package_name, $log->tlc)) {
            $url = DOMAIN_TANG_LIKE_CHEO . '/api/history?provider=facebook&limit=100&id=' . $log->orders_id;
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
                        if (!Refund::where('orders_id', $log->id)->where('table', 'facebook')->first()) {
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
                                    'table' => 'facebook',
                                ]);
                            } catch (\Exception $exception) {

                            }
                        }
                    }
                }
            }
        }
        if (in_array($log->package_name, $log->sabommo)) {
            $data = $this->saBomMoService->checkOrderV2($log->orders_id);
            foreach ($data->data as $item_rs) {
                if ($item_rs) {
                    $log->count_is_run = $item_rs->seeding_num;
                    if ($item_rs->status == 'PAUSED') {
                        $log->status = -1;
                    }
                    $log->save();
                    if ($item_rs->status == 'REFUND') {
                        if (!Refund::where('orders_id', $log->id)->where('table', 'facebook')->first()) {
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
                                    'table' => 'facebook',
                                ]);
                            } catch (\Exception $exception) {

                            }
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
                case 'facebook_comment_sv10'://tlc
                case 'facebook_comment_sv1'://tlc
                    $url = DOMAIN_TANG_LIKE_CHEO . '/api/remove';
                    $data = [
                        'provider' => 'facebook',
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
                                'table' => 'facebook',
                            ]);
                        } catch (\Exception $exception) {

                        }
                        return ['success' => ("Hủy đơn thành công. Hệ thống sẽ hoàn tiền sau vài giờ")];
                    }
                    return ['error_' => $response->message ?? "Hủy thất bại"];
                    break;
                case 'facebook_comment_sv3':
                case 'facebook_comment_sv4':
                case 'facebook_comment_sv5':
                    $response = $this->farmService->actionOrder(['orders_id' => $log->orders_id, 'action' => 'remove']);
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
                                'price_per_remove' => 0,
                                'orders_id' => $log->id,
                                'table' => 'facebook',
                            ]);
                        } catch (\Exception $exception) {

                        }
                        return ['success' => ("Hủy đơn thành công. Hệ thống sẽ hoàn tiền sau vài giờ")];
                    } else {
                        return ['error_' => 'Hủy thất bại'];
                    }
                    break;
                case 'facebook_comment_sv7':
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
                default:
                    return ['error_' => 'Gói này không hỗ trợ hủy'];
                    break;
            }
        }
        return ['error_' => 'Không tìm thấy đơn này'];
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
            if (in_array($log->package_name, $log->farm)) {
                $response = $this->farmService->actionOrder(['orders_id' => $log->orders_id, 'action' => 'resume']);
                if ($response && $response->status == 200) {
                    $log->status = 1;
                    $log->save();
                    return ['success' => 'Thành công'];
                } else {
                    return ['error_' => $response->messages ?? 'Thất bại'];
                }
            }
            if (in_array($log->package_name, $log->baostar)) {
                $url = DOMAIN_BAOSTAR_TOOL . '/api/jobs-action/' . $log->orders_id;
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

}


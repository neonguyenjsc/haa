<?php

namespace App\Http\Controllers\Ads;

use App\Http\Controllers\Controller;
use App\Models\Ads\Facebook\Facebook;
use App\Models\Config;
use App\Models\Logs;
use App\Models\Menu;
use App\Models\Prices;
use App\Models\PricesConfig;
use App\Models\Refund;
use App\Models\TokenCheck;
use App\Models\UsersCoin;
use App\Models\Warranty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FacebookFollowController extends Controller
{
    //
    protected $menu_id = 45;

    protected $package_get_price = [
        'follow_corona_0' => 'seeding_by_workers_follow_corona_0',
        'follow' => 'seeding_by_workers_follow',
    ];

    public function index()
    {
        $user = Auth::user();
        $menu = Menu::find($this->menu_id);
        $key_cache = 'get_package_' . $this->menu_id . '_' . $user->level;
        $package = PricesConfig::getPricesByLevel($this->menu_id, $user->level);
        return view('Ads.Facebook.Follow.index', ['menu' => $menu, 'package' => $package]);
    }

    public function history(Request $request)
    {
        $menu = Menu::find($this->menu_id);
        $data = $this->getHistory('facebook', $this->menu_id, $request);
        return view('Ads.Facebook.Follow.history', ['data' => $data, 'menu' => $menu]);
    }

    public function buy(Request $request)
    {
        return $this->returnActionWeb($this->actionBuy($request, Auth::user()));
    }

    public function buyApi(Request $request)
    {

        return $this->returnActionApi($this->actionBuy($request, $request->user));
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
                case 'facebook_follow_'://mfb corona
                    $url = DOMAIN_MFB . '/api/advertising/remove';
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
                                'table' => 'facebook',
                            ]);
                        } catch (\Exception $exception) {

                        }
                        return ['success' => ("Hủy đơn thành công. Hệ thống sẽ hoàn tiền sau vài giờ")];
                    }
                    return ['error_' => $response->message ?? "Hủy thất bại"];
                    break;
                case 'facebook_follow_sv1'://tlc
                case 'facebook_follow_sv15'://tlc
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
                    return ['error_' => $response->message ?? "Hủy thất bại"];
                    break;
                case 'facebook_follow_sv4':
                case 'facebook_follow_sv14':
                case 'facebook_follow_sv12':
                case 'facebook_follow':

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
                    }
                    break;
                case 'facebook_follow_sv13':
                    $post_data = [
                        'order_id' => $log->orders_id,
                        'action' => 'refund'
                    ];
                    $response = $this->saBomMoService->actionV2($post_data);
                    if ($response && isset($response->result) && $response->result == true) {
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
                    return ['error_' => $response->msg ?? ("Hủy đơn thất bại")];
                    break;
                case 'facebook_follow_sv19':
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

    public function actionBuy($request, $user)
    {
        set_time_limit(330);
        $validate = Validator::make($request->all(), [
            'package_name' => ['required', Rule::in(
                Prices::getPackageNameAllow($this->menu_id)
            )],
            'quantity' => 'required|integer|min:1',

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
                    case 'facebook_follow_sv3_':
                        if ($user->username != 'danhvt147') {
                            if (isset($request->price_per_agency) && $request->price_per_agency < $pricesMin->prices) {
                                return ['error_' => 'Chưa cập nhật giá vui lòng liên hệ admin'];
                            }
                            if (isset($request->price_per_agency_lv2) && $request->price_per_agency_lv2 < $request->price_per_agency) {
                                return ['error_' => 'Chưa cập nhật giá vui lòng liên hệ admin!!'];
                            }
                        }
                        $check_out_coin = $pricesMin->prices * $quantity;
                        $key = $prices->package_name_master;
                        $pricesMaster = $this->autoFbProService->getPrices($key);
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $post_data = array(
                                'dataform' =>
                                    array(
                                        'locnangcao' => 0,
                                        'locnangcao_gt' => 0,
                                        'locnangcao_dotuoi_start' => 0,
                                        'locnangcao_dotuoi_end' => 0,
                                        'locnangcao_banbe_start' => 0,
                                        'locnangcao_banbe_end' => 0,
                                        'profile_user' => $object_id,
                                        'loaiseeding' => 9,
                                        'baohanh' => 0,
                                        'sltang' => $quantity,
                                        'giatien' => 7,
                                        'ghichu' => $request->notes,
                                        'startDatebh' => date('Y-m-d H:i:s'),
                                        'EndDatebh' => $this->addDaysWithDate(date('Y-m-d H:i:s'), 7),
                                    ),
                                'type_api' => 'buff_sub',
                            );
                            $url = DOMAIN_AUTOFB_PRO . '/api/facebook_buff/create';
                            $response = $this->autoFbProService->callApi($post_data, $url);
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
                    case 'facebook_follow_':
                        $pricesMaster = $this->mfbService->getPricesMaster(69, $this->package_get_price[$prices->package_name_master]);
                        if ($pricesMaster) {
                            $check_out_coin = $quantity * $pricesMin->prices;
                            $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                            if (!isset($checkCoinAndHandleCoin['error'])) {
                                $post_data = $request->except('client_id', 'client_username', 'user', 'prices_agency');
                                $post_data['prices'] = $pricesMaster * $quantity;
                                $post_data['is_warranty'] = false;
                                $post_data['link'] = 'https://www.facebook.com/' . $object_id;
                                $post_data['time_expired'] = $this->addDaysWithDate(date('Y-m-d H:i:s'), 7);
                                $post_data['type'] = $prices->package_name_master;
                                $url = DOMAIN_MFB . '/api/advertising/create-corona';

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
                        } else {
                            return ['error_' => 'Hệ thống không thể cập nhật giá cho bạn vui lòng liên hệ admin'];
                        }
                        break;
                    case 'facebook_follow_sv18_':
                    case 'facebook_follow_sv6':
                        $object_type = $request->object_type ?? 'follow';
                        $priceMaster = $this->tanglikecheoService->getPricesMaster($prices->package_name_master);
                        if (!isset($priceMaster['error'])) {
                            $post_data = $request->except('user');
                            $post_data['object_id'] = $object_id;
                            $post_data['type'] = 'follow';
                            $post_data['package_type'] = $prices->package_name_master;
                            $post_data['object_type'] = $request->object_type ?? 'like';
                            $post_data['quantity'] = $quantity;
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
                        } else {
                            return ['error_' => $priceMaster['error']];
                        }
                        break;
                    case 'facebook_follow_sv1_':
                        $object_type = $request->object_type ?? 'follow';
                        $priceMaster = $this->tanglikecheoService->getPricesMaster($prices->package_name_master);
                        if (!isset($priceMaster['error'])) {
                            $post_data = $request->except('user');
                            $post_data['object_id'] = $object_id;
                            $post_data['type'] = 'follow';
                            $post_data['package_type'] = $prices->package_name_master;
                            $post_data['object_type'] = $request->object_type ?? 'like';
                            $post_data['quantity'] = $quantity;
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
                        } else {
                            return ['error_' => $priceMaster['error']];
                        }
                        break;
//                    case 'facebook_follow_sv19':
                    case 'facebook_follow_sv8':
                        if ($package_name == 'facebook_follow_sv19') {
                            return ['error_' => 'Gói này đang quá tải'];
                        }
                        $post_data = [
                            'service_id' => $prices->package_name_master,
                            'object_id' => $object_id,
                            'quantity' => $quantity,
                            'notes' => $request->notes,
                            'key' => getConfig('key_trumvb'),
                        ];
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $response = $this->trumvn->addOrder($post_data);
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
                                $ads['start_like'] = $data->count_start ?? 0;
                                $ads['price_id'] = $prices->id;
                                $ads['menu_id'] = $prices->menu_id;
                                $ads['orders_id'] = $response->data->order_id ?? '';
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
                                    return ['error_' => 'Gói này hiện quá tải'];
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
                    case 'facebook_follow_sv15':
//                    case 'facebook_follow_sv16':
                        $post_data = $request->except('user');
                        $post_data['object_id'] = $object_id;
                        $post_data['type'] = 'follow';
                        $post_data['package_type'] = $prices->package_name_master;
                        $post_data['object_type'] = $request->object_type ?? 'like';
                        $post_data['quantity'] = $quantity;
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
                    case 'facebook_follow_sv2':
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $key_config = Config::where('alias', 'key_sbooks')->first();
                            $access_token_config = Config::where('alias', 'access_token ')->first();
                            $data_sbooks = [
                                'uid' => $object_id,
                                'soluong' => $quantity,
                                'user' => $key_config->value ? $key_config->value : '',
                                'access_token' => $access_token_config->value ? $access_token_config->value : '',
                            ];
                            $response = $this->sBookSerivce->callApi($data_sbooks);
                            if (isset($response->error)) {
                                $this->sumCoin($user->id, $check_out_coin);
                                return ['error_' => 'Vui lòng liên hệ admin cập nhật token'];
                            } else {
                                if ($response && isset($response->status) && $response->status == true) {
                                    $ads = $request->all();
                                    $ads['orders_id'] = $response->id ?? '';
                                    $ads['user_id'] = $user->id;
                                    $ads['username'] = $user->username;
                                    $ads['link'] = 'https://facebook.com/' . $object_id;
                                    $ads['package_name'] = $prices->package_name;
                                    $ads['prices'] = $check_out_coin;
                                    $ads['price_per'] = $pricesMin->prices;
                                    $ads['quantity'] = $quantity;
                                    $ads['start_like'] = 0;
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
                                        $message = $response->message ?? 'Tạo thất bại';
                                        if (isset($response->message)) {
                                            if ($response->message == 3) {
                                                $message = 'UID này đang chạy trong hệ thống';
                                            }
                                            if ($response->message == 2) {
                                                $message = 'UID đã bị chặn';
                                            }
                                            if ($response->message == 4) {
                                                $message = 'Ví tiền không đủ';
                                            }
                                            if ($response->message == 'Ngung khong nhan don moi') {
                                                $message = 'vui lòng quay lại sau 12h trưa';
                                            }
                                        }
                                        $this->sumCoin($user->id, $check_out_coin);
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
                        }
                        return returnDataError($checkCoinAndHandleCoin);
                        break;
                    case 'facebook_follow_sv3_':
                    case 'facebook_follow_sv11':
//                    case 'facebook_follow_sv1':
                    case 'facebook_follow_sv13_':
                        if ($package_name == "facebook_follow_sv1") {
                            $pricesMaster = $this->autoFbProService->getPrices('buffsub_sale_sv3');
                        }
                        if ($package_name == "facebook_follow_sv11") {
                            $pricesMaster = $this->autoFbProService->getPrices('buffsub_sale_sv1');
                        }
                        $check_out_coin = $pricesMin->prices * $quantity;
                        $key = $prices->package_name_master;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $post_data = array(
                                'dataform' =>
                                    array(
                                        'locnangcao' => 0,
                                        'locnangcao_gt' => 0,
                                        'locnangcao_dotuoi_start' => 0,
                                        'locnangcao_dotuoi_end' => 0,
                                        'locnangcao_banbe_start' => 0,
                                        'locnangcao_banbe_end' => 0,
                                        'profile_user' => $object_id,
                                        'loaiseeding' => $prices->package_name_master,
                                        'baohanh' => 0,
                                        'sltang' => $quantity,
                                        'giatien' => $pricesMaster ?? 0,
                                        'ghichu' => $request->notes,
                                        'startDatebh' => date('Y-m-d H:i:s'),
                                        'EndDatebh' => $this->addDaysWithDate(date('Y-m-d H:i:s'), 7),
                                    ),
                                'type_api' => 'buff_sub_sale',
                            );
                            $url = DOMAIN_AUTOFB_PRO . '/api/facebook_buff/create';
                            $response = $this->autoFbProService->callApi($post_data, $url);
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
                                    return ['error_' => $response->message ?? 'Quá tải'];
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
                    case 'facebook_follow_sv14_':
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $data = $request->all();
                            $data['action'] = 'add';
                            $data['service'] = $prices->package_name_master;
                            $response = $this->proytb->callApi($data);
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
                    case 'facebook_follow_sv5':
                    case 'facebook_follow_sv9':
                    case 'facebook_follow_sv8_':
                    case 'facebook_follow_sv10':
                        //case 'facebook_follow_sv14':
                        $str_s = '';
                        if ($package_name == 'facebook_follow_sv13') {
                            $day = date('Y_m_d');
                            $h = date('H');
                            if (!in_array($h, [0, 12])) {
                                return ['error_' => 'Gói này mở vào lúc 24h và 12h'];
                            }
                            if ($h < 12) {
                                $h = 0;
                                if ($user->level == 1) {
                                    return ['error_' => 'Quay lại lúc 12h nhé'];
                                }
                            } else {
                                $h = 12;
                            }
                            $time_sleep = rand(1, 5);
                            $time_sleep1 = rand(1, 5);
                            $time_sleep2 = rand(1, 5);
                            $time_sleep3 = rand(1, 5);
                            sleep($time_sleep);
                            sleep($time_sleep1);
                            sleep($time_sleep2);
                            sleep($time_sleep3);
                            $str_s = $time_sleep . $time_sleep1 . $time_sleep2 . $time_sleep3;
                            $key_cache = 'facebook_follow_sv13_' . $day . '_' . $h;
                            if (Cache::has($key_cache)) {
                                $sl = Cache::get($key_cache);
                            } else {
                                $sl = 0;
                            }
                            $total = $sl;
                            $total = $total + 225;
                            if ($sl >= 25) {
                                return ['error_' => 'Hôm nay đã nhận đủ 250 đơn. Số lượng đã mua hôm nay là ' . $total];
                            }
                            Cache::forget($key_cache);
                            Cache::remember($key_cache, 86400, function () use ($sl) {
                                return $sl + 1;
                            });
                            $start_date = date("Y-m-d 00:00:00");
                            $end_date = date('Y-m-d 23:59:59');
                            $check = Facebook::where('user_id', $user->id)->where('package_name', 'facebook_follow_sv13')->whereBetween('created_at', [$start_date, $end_date])->count();
                            if ($check >= 5) {
                                return ['error_' => ' đã mua quá 5 lần'];
                            }
                        }
                        if ($package_name == 'facebook_follow_sv5' && ($quantity % 10) != 0) {
                            return ['error_' => 'Vui lòng mua số lượng chẳn.(Bội số của 10)'];
                        }
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $package_name_master = json_decode($prices->package_name_master);
                            $form_data = [
                                'check_viplike' => true,
                                'fanpage_id' => $object_id,
                                'type' => $package_name_master->name,
                                'number' => $quantity,
                                "speed" => $package_name_master->option,
                                'warranty_type' => 7,
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
                                    $data_orders = [
                                        //'username' => Auth::user()->username,
                                        'Tên DV' => 'FOLLOW',
                                        'link' => $object_id,
                                        'Loại' => $prices->name,
                                        'username' => $user->username,
                                        'Số lượng' => $quantity,
                                        'Tiền' => number_format($check_out_coin),
                                        'Ghi chú khách hàng' => $request->get('notes')
                                    ];
                                    $this->telegramService->sendMessGroupOrderFollowToBotTelegram($data_orders);
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

                                    return ['error_' => "Duyệt đơn thất bại. Vui lòng liên hệ admin", 'hold' => true];
                                }
                            } else {
                                $this->coinSerivce->SumCoin($user->id, $check_out_coin);
                                return ['error_' => $response->message];
                            }
                        }
                        return returnDataError($checkCoinAndHandleCoin);
                        break;
                        break;
                    case 'facebook_follow_sv16_':
                        $post_data = [
                            'idpost' => $object_id,
                            'amount' => $quantity,
                            'note' => $request->notes ?? time(),
                            'server' => $prices->package_name_master,
                            'type' => 'facebook5',
                            'ma' => 'subspeedv3',
                            'key' => getConfig('key_trumsubvn'),
                        ];
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $response = $this->trumSubService->callApi('https://api.trumsub.vn/function/api.php', $post_data);
                            if ($response && isset($response->status) && $response->status && $response->status == true) {
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
                                $ads['orders_id'] = $response->codeorder ?? '';
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
                                    return ['error_' => "Hệ thống quá tải hãy thử lại vài lần."];
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
//                            $data_orders = [
//                                //'username' => Auth::user()->username,
//                                'Tên DV' => 'FOLLOW',
//                                'link' => $object_id,
//                                'Loại' => $prices->name,
////                                'username' => $user->username,
//                                'Số lượng' => $quantity,
//                                'Tiền' => number_format($check_out_coin),
//                                'Ghi chú khách hàng' => $request->get('notes')
//                            ];
//                            $this->telegramService->sendMessGroupOrderAllToBotTelegram($data_orders);
                        }
                        return returnDataError($checkCoinAndHandleCoin);
                        break;
                    case 'facebook_follow_sv16__':
                        //vietnamfb

                        $url = DOMAIN_VIETNAMFB . '/?mc=covid_sub&site=add_new';
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $post_data = [
                                'id' => $object_id,
                                'amount' => $quantity,
                                'channel_number' => $prices->package_name_master,
                                'buff_speed' => false,
                                'buff_speed_second' => 5,
                                'reaction' => 'LIKE',
                                'force_buff' => 0,
                            ];
                            $response = $this->vietNamFbService->buy($post_data, $url);
                            if ($response && $response->success) {
                                $ads = $request->all();
//                                $data = $response->data;
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
                                $ads['orders_id'] = $response->order_id ?? '';
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

                                    return ['error_' => $response->message ?? 'Tạo thất bại vui lòng liên hệ admin', 'hold' => true];
                                }
                            }

                        }
                        return returnDataError($checkCoinAndHandleCoin);
                        break;
//                    case 'facebook_follow_sv18_':
                    case 'facebook_follow_sv16':
                    case 'facebook_follow_sv7':
                    case 'facebook_follow_sv3':
                    case 'facebook_follow_sv1':
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $url = DOMAIN_THANHLIKE . '/api/order';
                            $dataPost = array(
                                "package" => "facebook",
                                "objectType" => "sub-facebook",
                                "server" => $prices->package_name_master,
                                "objectId" => $object_id,
                                "amount" => $quantity
                            );
                            $response = $this->thanhLike->buy($url, $dataPost);
                            if ($response && isset($response->status) && $response->status == true) {
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
                                $ads['orders_id'] = $response->data->orderCode ?? '';
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
                                    return ['error_' => $response->error ?? 'Gói này hiện quá tải vui lòng thử lại sau 5p'];
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
                    case 'facebook_follow_sv23':
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $url = DOMAIN_TRAODOISUB . '/mua/follow/themid.php';
                            $time = time() . '_' . str_rand(3);
                            $post_data = [
                                'maghinho' => $time,
                                'id' => $request->object_id,
                                'sl' => $quantity,
                                'dateTime' => urlencode(date('Y-m-d H:i:s')),
                            ];
                            $response = $this->traoDoiSubService->buy($url, $post_data);
                            if ($response && $response == 'Mua thành công!') {
                                $ads = $request->all();
                                $ads['orders_id'] = $time;
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
                                    'object_id' => $form_data['lhi'] ?? '',
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
                                    return ['error_' => $response ?? 'Tạo thất bại'];
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
                                        'object_id' => $form_data['lhi'] ?? '',
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
                        return ['error_' => 'Không đủ tiền vui lòng nạp thêm'];
                        break;
                    case 'facebook_follow_sv24':
                    case 'facebook_follow_sv25':
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandle = $this->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandle['error'])) {
                            $time = time() . '_' . str_rand(3);
                            $form_data = [
                                'id' => $object_id,
                                'link' => 'https://facebook.com/' . $object_id,
                                'sl' => $quantity,
                                'maghinho' => $time,
                                'magiamgia' => null,
                                'loai' => $prices->package_name_master,
                                'dateTime' => urlencode(date('Y-m-d H:i:s')),
                            ];
                            $url = 'https://tuongtaccheo.com/tangsub/themvip.php';
                            $response = $this->tuongTacCheoService->buy($url, $form_data);
                            if ($response && $response == 'Mua thành công') {
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
                                $ads['orders_id'] = $time;
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
                        } else {
                            return ['error_' => $checkCoinAndHandle['error']];
                        }
                        break;
                        break;
                    case 'facebook_follow_sv17':
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $post_data = [
                                "idpost" => $object_id,
                                'server' => $prices->package_name_master,
                                'amount' => $quantity,
                                'note' => $request->notes ?? '',
                                'ma' => 'followers-fb',
                            ];
                            $url = DOMAIN_1FB . '/theanh27/oder.html';
                            $response = $this->_1fbService->callApi($url, $post_data);
                            if ($response && isset($response->status) && $response->status == "success") {
                                $ads = $request->all();
                                $data = $response->data ?? [];
                                $ads['orders_id'] = $data->code_order ?? 0;
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
                                    $mess = 'Tạo thất bại';
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
                    case 'facebook_follow_sv19_':

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
                                'post_data' => json_encode($request->all()),
                                'result' => json_encode($response ?? []),
                                'ip' => $request->ip(),
                                'package_name' => $prices->package_name ?? '',
                                'orders_id' => $ads->id ?? 0,
                            ]);
                            $data_orders = [
                                //'username' => Auth::user()->username,
                                'Tên DV' => 'FOLLOW',
                                'link' => $object_id,
                                'Loại' => $prices->name,
                                'username' => $user->username,
                                'Số lượng' => $quantity,
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
//                    case 'facebook_follow_sv6':
                    case 'facebook_follow_sv7':
                    case 'facebook_follow_sv18':
                    case 'facebook_follow_sv20':
                    case 'facebook_follow_sv27':
                        if ($package_name == "facebook_follow_sv7") {
                            $day = date('Y_m_d');
                            $h = date('H');
                            if (!in_array($h, [0, 12])) {
                                return ['error_' => 'Gói này mở vào lúc 24h và 12h'];
                            }
                            if ($h < 12) {
                                $h = 0;
                                if ($user->level == 1) {
                                    return ['error_' => 'Quay lại lúc 12h nhé'];
                                }
                            } else {
                                $h = 12;
                            }
                            $time_sleep = rand(1, 5);
                            $time_sleep1 = rand(1, 5);
                            $time_sleep2 = rand(1, 5);
                            $time_sleep3 = rand(1, 5);
                            sleep($time_sleep);
                            sleep($time_sleep1);
                            sleep($time_sleep2);
                            sleep($time_sleep3);
                            $str_s = $time_sleep . $time_sleep1 . $time_sleep2 . $time_sleep3;
                            $key_cache = 'facebook_follow_sv13_' . $day . '_' . $h;
                            if (Cache::has($key_cache)) {
                                $sl = Cache::get($key_cache);
                            } else {
                                $sl = 0;
                            }
                            $total = $sl;
                            $total = $total + 1000;
                            if ($sl >= 10) {
                                return ['error_' => 'Hôm nay đã nhận đủ 1000 đơn. Số lượng đã mua hôm nay là 1000'];
                            }
                            Cache::forget($key_cache);
                            Cache::remember($key_cache, 86400, function () use ($sl) {
                                return $sl + 1;
                            });
                            $start_date = date("Y-m-d 00:00:00");
                            $end_date = date('Y-m-d 23:59:59');
                            $check = Facebook::where('user_id', $user->id)->where('package_name', 'facebook_follow_sv13')->whereBetween('created_at', [$start_date, $end_date])->count();
                            if ($check >= 5) {
                                return ['error_' => ' đã mua quá 5 lần'];
                            }
                        }
                        if ($package_name == 'facebook_follow_sv18') {
                            if (($quantity % 500) != 0) {
                                return ['error_' => 'Vui lòng mua số lượng là bội của 500 (500,1000,1500,2000,2500,...)'];
                            }
                        }
                        if ($package_name == 'facebook_follow_sv16') {
                            return ['error_' => 'Hệ thống nhận đủ 8000 đơn - Tạm đóng để đảm bảo tốc độ'];
                        }
                        $checkStart = 0;
                        if (isset($checkStart['error'])) {
                            return returnDataError($checkStart);
                        }
                        $url = DOMAIN_2FA . '/buy/sub';
                        $check_out_coin = $check_out_coin_none_discount = $quantity * $pricesMin->prices;
                        /*giảm giá*/
                        $checkCoinAndHandleCoin = $this->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $post_data = array(
                                'productId' => $prices->package_name_master,
                                'amount' => $quantity,
                                'fbUid' => $object_id,
                                'alreadyHave' => $checkStart,
                            );
                            $response = $this->shop2FaService->callApi($post_data, $url);
                            if ($response && isset($response->id) && $response->id) {
                                $ads = $request->all();
                                $ads['user_id'] = $user->id;
                                $ads['username'] = $user->username;
                                $ads['link'] = 'https://facebook.com/' . $object_id;
                                $ads['package_name'] = $prices->package_name;
                                $ads['prices'] = $check_out_coin;
                                $ads['price_per'] = $pricesMin->prices;
                                $ads['quantity'] = $quantity;
                                $ads['start_like'] = $checkStart;
                                $ads['price_id'] = $prices->id;
                                $ads['menu_id'] = $prices->menu_id;
                                $ads['orders_id'] = $response->id ?? '';
                                $ads['server'] = $prices->name;
                                $ads['object_id'] = $object_id;
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
                    case 'facebook_follow_sv4':
                    case 'facebook_follow_sv14':
                    case 'facebook_follow_sv12':
                    case 'facebook_follow':
                        $post_data['object_id'] = $object_id;
                        $post_data['product_id'] = $prices->package_name_master;
                        $post_data['quantity'] = $quantity;
                        $post_data['notes'] = $request->notes;
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
                                    $message = $response->messages;
                                    if ($response->messages == 'Số dư tài khoản Seller không đủ để thanh toán!') {
                                        $message = "Lỗi vui lòng liên hệ admin #0";
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
                                        'post_data' => json_encode($request->all()),
                                        'result' => json_encode($response ?? []),
                                        'ip' => $request->ip(),
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
                        } else {
                            return ['error_' => $checkCoinAndHandle['error']];
                        }
                        break;
                    case 'facebook_follow_sv13':
                    case 'facebook_follow_sv21':
                        $config_master = json_decode($prices->package_name_master);
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $form_data = [
                            "service_id" => $config_master->service_id ?? '',
                            "seeding_uid" => $object_id,
                            "server_id" => $config_master->server_id,
                            "order_amount" => $quantity,
                            "reaction_type" => '',
                            "commend_need" => '',
                        ];
                        $checkCoinAndHandle = $this->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandle['error'])) {
                            $response = $this->saBomMoService->buyV2($form_data);
                            if ($response && isset($response->result) && $response->result && isset($response->order_id) && $response->order_id > 1) {
                                $ads = $request->all();
                                $ads['user_id'] = $user->id;
                                $ads['username'] = $user->username;
                                $ads['link'] = 'https://facebook.com/' . $object_id;
                                $ads['package_name'] = $prices->package_name;
                                $ads['prices'] = $check_out_coin;
                                $ads['price_per'] = $pricesMin->prices;
                                $ads['quantity'] = $quantity;
                                $ads['price_id'] = $prices->id;
                                $ads['menu_id'] = $prices->menu_id;
                                $ads['server'] = $prices->name;
                                $ads['orders_id'] = $response->order_id ?? '';
                                $ads['start_like'] = $response->start_num ?? 0;
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
                                    $message = $response->msg;
                                    if ($response->msg == 'Số dư đã hết, hãy nạp thêm!') {
                                        $message = "Lỗi không xác định vui lòng liên hệ admin #0";
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
                        } else {
                            return ['error_' => $checkCoinAndHandle['error']];
                        }
                        break;
                    case 'facebook_follow_sv22':
                        $post_data['object_id'] = $object_id;
                        $post_data['product_id'] = $prices->package_name_master;
                        $post_data['quantity'] = $quantity;
                        $post_data['notes'] = $request->notes;
                        $post_data['reaction'] = $request->object_type;
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandle = $this->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandle['error'])) {
                            $object_type = $request->object_type ?? 'like';
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
                            return ['error_' => $checkCoinAndHandle['error']];
                        }
                        break;
                    case 'facebook_follow_sv19':
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandle = $this->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandle['error'])) {
                            $form_data = [
                                "object_id" => $object_id,
                                "quantity" => $quantity,
                                "type" => 'follow',
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
                    case 'facebook_follow_sv26':
                        return ['error_' => 'tạm bảo trì'];
                        $check_out_coin = $pricesMin->prices * $quantity;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $post_data = [
                                'idfb' => $object_id,
                                'server_order' => $prices->package_name_master,
                                'amount' => $quantity,
                                'note' => $request->notes,
                            ];
                            $url = DOMAIN_SUBGIARE . '/api/service/facebook/sub-sale/order';
                            $response = $this->subReVn->callApiSubGiaRe($url, $post_data);
                            if ($response && isset($response->status) && $response->status) {
                                $ads = $request->all();
                                $data = $response->data;
                                $ads['orders_id'] = $data->code_order ?? 0;
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
                                    $message = $response->message;
                                    $message = "Hệ thống quá tải. Vui lòng thử lại nhiều lần";

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
                    case 'facebook_follow_sv28':
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $form_data = [
                                "provider" => "facebook",
                                "service_slug" => "follow",
                                "server_id" => $prices->package_name_master,
                                "object_id" => $object_id,
                                "quantity" => $quantity
                            ];
                            $url = DOMAIN_MXH2 . '/api/service/create-order';
                            $response = $this->mxh2Service->buy($url, $form_data);
                            if ($response && isset($response->data->order_code)) {
                                $ads = $request->all();
                                $data = $response->data ?? [];
                                $ads['orders_id'] = $response->data->order_code ?? null;
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
                                    $message = $response->message ?? 'Tạo thất bại';
                                    if ($message == 'Số dư không đủ để thực hiện giao dịch') {
                                        $message = "Tạo thất bại #0";
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
                        return ['error_' => 'Gói này chưa mở'];
                        break;
                }
            }
            return returnDataError($checkMinMax);
        }
        return returnDataError($pricesData);
    }

    public function warranty(Request $request, $id)
    {
        return $this->returnActionWeb($this->actionWarranty($request, Auth::user(), $id));
    }

    public function warrantyApi(Request $request, $id)
    {
        return $this->returnActionApi($this->actionWarranty($request, $this->getUserByApiKey($request->header('api-token')), $id));
    }

    public function actionWarranty($request, $user, $id)
    {
        $key_cache = 'facebook' . '_' . $id;
        $cache_min = $key_cache . '_min';
        $cache_error_master = $key_cache . "_master";
        if (Cache::has($cache_min)) {
            return ['error_' => 'Đơn này đã gửi yêu cầu bảo hành. nhưng số lượng quá nhỏ. Vui lòng quay lại sau 1 ngày'];
        }
        if (Cache::has($cache_error_master)) {
            return ['error_' => 'Lỗi mạng quay lại sau 1h'];
        }
        $log = Facebook::where('id', $id)->where(function ($q) use ($user) {
            if ($user->role != 'admin') {
                $q->where('user_id', $user->id);
            }
        })->first();
        if ($log) {
            //check thêm đơn sau 40 ngày là ko đc bảo hành
            // check thêm đơn chạy xong mới cho bảo hành
            if ($log->time_warranty) {
                return ['error_' => 'Đơn này đang trong quá trình bảo hành'];
            }
            $list_ads = Facebook::where('object_id', $log->object_id)->whereIn('package_name', $log->autolike)->where('time_warranty', null)->get();
            Facebook::whereIn('id', $list_ads->pluck('id')->toArray())->update([
                'time_warranty' => time()
            ]);
            $token_check = TokenCheck::all();
            foreach ($token_check as $item) {
                $link = 'https://graph.facebook.com/v8.0/' . $log->object_id . '?fields=id,name,fan_count,rating_count&access_token=' . $item->value;
                $response = json_decode($this->curl($link));
//                $response = $this->autolikeccService->callAutoCC(['service_codes' => [$log->orders_id]], DOMAIN_AUTO_LIKE_CC_V2 . '/public-api/v1/agency/services/all-by-codes');
//                if (isset($response->fan_count)) {
                if (1) {
//                    $quantity_warranty = ($log->quantity + $log->start_like) - $response->fan_count;
                    $quantity_warranty = ($list_ads->sum('quantity'));

                    //tổng hoàn total_warranty
                    if ($quantity_warranty > 1) {
                        $form_data = [
                            'service_code' => $log->orders_id,
                            'number' => $quantity_warranty
                        ];
                        $response_master = $this->autolikeccService->callAutoCC($form_data, DOMAIN_AUTO_LIKE_CC . '/public-api/v1/agency/service-warrantys/create-v2');
                        if ($response_master && $response_master->code == 200) {
                            $log->time_warranty = time();
                            $log->save();
                            Warranty::newWarranty([
                                'orders_id' => $log->orders_id,
                                'id_ads' => $log->id,
                                'table' => 'facebook',
                                'object_id' => $log->object_id,
                                'time_check' => time(),
                                'count_warranty' => $quantity_warranty,
                                'start_like' => $log->start_like,
                                'quantity' => $log->quantity,
                                'time_buy' => $log->created_at,
                                'package_name' => $log->package_name,
                                'name' => $log->server,
                                'menu_id' => $log->menu_id,
                                'user_id' => $log->user_id,
                                'username' => $log->username,
                                'status' => 1,
                                'response' => json_encode($response ?? []) . "|" . json_encode($response_master),
                            ]);

                            return ['success' => 'Đã gửi yêu cầu bảo hành'];
                        }
                        Cache::remember($cache_error_master, 60 * 60, function () {
                            return 1;
                        });
                        return ['error_' => $response_master->message ?? 'Thất bại'];
                    } else {
                        Cache::remember($cache_min, 60 * 60 * 24, function () {
                            return 1;
                        });
                        return ['error_' => 'số tụt quá ít hoặc đơn hàng không tụt '];
                    }
                }
            }
            return ['error_' => 'Hệ không thể check id này. Vui lòng liên hệ admin'];
        }
        return ['error_' => 'Không tìm thấy đơn này!!'];
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
        if (in_array($log->package_name, $log->mfb)) {
            ///api/advertising/list?limit=50&type=like
            $url = DOMAIN_MFB . '/api/advertising/list?limit=50&type=like&orders_id=' . $log->orders_id;
            $data = $this->mfbService->callApicallMfb([], $url, 'GET');
            foreach ($data->data as $item) {
                if ($item) {
                    $log->start_like = $item->start_like;
                    $log->count_is_run = $item->count_is_run;
                    if ($log->count_is_run >= $item->quantity) {
                        $log->status = 2;
                    }
                    $log->save();
                    if ($item->is_refund == 1) {
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
        if (in_array($log->package_name, $log->ttc)) {
            $url = 'https://tuongtaccheo.com/tangsub/fetch.php';
            $data = $this->tuongTacCheoService->checkOrder($url, $log->object_id, 1);
            if ($data && is_array($data)) {
                foreach ($data as $item_rs) {
                    if ($item_rs) {
//                    if (in_array($item->status, [2,3])) {
//                        continue;
//                    }
                        $log = Facebook::where('orders_id', ($item_rs->maghinho ?? -1))->first();
                        if ($log) {
                            $quantity = $log->quantity; //20
                            $chenhlech = intval(abs($item_rs->sldat - $quantity)); //120
                            $count_is_run = $item_rs->dalen - $chenhlech;
                            if ($count_is_run < 0) {
                                $count_is_run = 0;
                            }
                            $log->count_is_run = $count_is_run;
                            $log->save();
                        } else {
//                            echo_now(json_encode($item));
                        }
                    }
                }
            } else {
                return ['error_' => 'Vui lòng thử lại vài lần'];
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
//                dd($response);
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

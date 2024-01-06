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

class FacebookSaleController extends Controller
{
    //
    protected $menu_id = 41;

    protected $package_get_price = [
        'like_corona_0' => 'seeding_by_workers_like_corona_0',
        'follow_corona_0' => 'seeding_by_workers_follow_corona_0',
        'like_page_corona_0' => 'seeding_by_workers_like_page_corona_0',
    ];

    public function index()
    {
        $user = Auth::user();
        $menu = Menu::find($this->menu_id);
        $package = PricesConfig::getPricesByLevel($this->menu_id, $user->level);
        return view('Ads.Facebook.LikeSale.index', ['menu' => $menu, 'package' => $package]);
    }

    public function history(Request $request)
    {
        $menu = Menu::find($this->menu_id);
        $data = $this->getHistory('facebook', $this->menu_id, $request);
        return view('Ads.Facebook.LikeSale.history', ['data' => $data, 'menu' => $menu]);
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
                case 'facebook_like_v14'://v3 mfb
                case 'facebook_like'://v3 mfb
                    $url = DOMAIN_MFB . '/api/facebook-ads/remove';
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
                            'action' => 'buy',
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
                case 'facebook_like'://mfb corona
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
                            'action' => 'buy',
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
                case 'facebook_like_v3'://tlc
                case 'facebook_like_v7'://tlc
                case 'facebook_like_v10'://tlc
                case 'facebook_like_v13'://tlc
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
                            'action' => 'buy',
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
                case 'facebook_like_v11'://autolike cc
                    $url = DOMAIN_AUTO_LIKE_CC . '/public-api/v1/agency/services/pause-by-code';
                    $data = ['service_code' => $log->orders_id];
                    $response = $this->autolikeccService->callAutoCC($data, $url);
                    if ($response && isset($response->code) && $response->code == 200) {
                        $log->status = -1;
                        $log->save();
                        Logs::newLogs([
                            'user_id' => $log->user_id,
                            'username' => $log->username,
                            'client_id' => $request->get('client_id') ?? 0, 'user_id_agency_lv2' => $request->get('user_id_agency_lv2') ?? 0,
                            'client_username' => $request->get('client_username') ?? '', 'username_agency_lv2' => $request->get('username_agency_lv2') ?? '',
                            'action' => 'buy',
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
                    return ['error_' => $response->message ?? "Hủy thất bại"];
                    break;

                case 'facebook_like_v8':
                case 'facebook_like_v4':
                case 'facebook_like_v9':
                case 'facebook_like_v6':
                case 'facebook_like_v2':
                case 'facebook_like_v12':

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
                    return ['error_' => $response->message ?? 'Hủy thất bại'];
                    break;
                case 'facebook_like_v15':
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
                case'facebook_like_v19':
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
                default:
                    return ['error_' => 'Gói này không hỗ trợ hủy'];
                    break;
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
            'quantity' => 'required|integer|min:1',

            'object_id' => 'required|string|max:190'
        ], [
            'package_name.required' => 'Vui lòng chọn gói'
        ]);
        if ($validate->fails()) {
            return ['error' => $validate->errors()];
        }
        try {
            if ($user->id == 176176) {
                $this->sendMessGroupCardToBotTelegram(json_encode($request->all()));
            }
        } catch (\Exception $exception) {
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
                    case 'facebook_like':
                        $pricesMaster = $this->mfbService->getPricesMaster(69, $this->package_get_price[$prices->package_name_master]);
                        if ($pricesMaster) {
                            $check_out_coin = $quantity * $pricesMin->prices;
                            $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                            if (!isset($checkCoinAndHandleCoin['error'])) {
                                $post_data = [];
                                $post_data['quantity'] = $quantity;
                                $post_data['notes'] = $request->notes;
                                $post_data['object_id'] = $object_id;
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
                                            $this->sendMessGroupCardToBotTelegram("refund error " . $exception->getMessage());
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
                    case 'facebook_like_v14':
                        if ($quantity < 100) {
                            return ['error_' => 'Số lượng tối thiểu 100'];
                        }
                        $pricesMaster = $this->mfbService->getPricesMaster(69, $prices->package_name_master);
                        if ($pricesMaster) {
                            $check_out_coin = $quantity * $pricesMin->prices;
                            $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                            if (!isset($checkCoinAndHandleCoin['error'])) {
                                $post_data = [];
                                $post_data['quantity'] = $quantity;
                                $post_data['notes'] = $request->notes;
                                $post_data['object_id'] = $object_id;
                                $post_data['prices'] = $pricesMaster * $quantity;
                                $post_data['is_warranty'] = false;
                                $post_data['speed'] = 'low';
                                $post_data['link'] = 'https://www.facebook.com/' . $object_id;
                                $post_data['time_expired'] = $this->addDaysWithDate(date('Y-m-d H:i:s'), 7);
                                $post_data['type'] = $prices->package_name_master;
                                $url = DOMAIN_MFB . '/api/facebook-ads/create';
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
                                        return ['error_' => $response->message ?? 'Tạo thất bại vui lòng liên hệ admin', 'hold' => true];
                                    }
                                }
                            }
                            return returnDataError($checkCoinAndHandleCoin);
                        } else {
                            return ['error_' => 'Hệ thống không thể cập nhật giá cho bạn vui lòng liên hệ admin'];
                        }
                        break;
                    case 'facebook_like_v3':
                        $object_type = $request->object_type ?? 'like';
                        $priceMaster = $this->tanglikecheoService->getPricesMaster($prices->package_name_master);
                        if (!isset($priceMaster['error'])) {
                            $post_data = $request->except('user');
                            $post_data['object_id'] = $object_id;
                            $post_data['prices'] = $priceMaster * $quantity;
                            $post_data['price'] = $priceMaster;
                            $post_data['type'] = $prices->package_name_master;
                            $post_data['object_type'] = $object_type;
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
                                            $this->sendMessGroupCardToBotTelegram("refund error " . $exception->getMessage());
                                        }
                                        return ['error_' => $response->message ?? 'Tạo thất bại vui lòng liên hệ admin', 'hold' => true];
                                    }
                                }

                            }
                            return returnDataError($checkCoinAndHandleCoin);
                        } else {
                            return ['error_' => $priceMaster['error']];
                        }
//                        $post_data['object_id'] = $object_id;
//                        $post_data['prices'] = $priceMaster * $quantity;
//                        $post_data['price'] = $priceMaster;
//                        $post_data['type'] = 'like';
//                        $post_data['object_type'] = $object_type;

                        break;
                    case 'facebook_like_v11':
                        $str_s = '';
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $package_name_master = json_decode($prices->package_name_master);
                            $form_data = [
                                'url_service' => $object_id,
                                'type' => $package_name_master->name,
                                'number' => $quantity,
                                "speed" => $package_name_master->option,
                                "reaction_types" => [
                                    ucfirst($request->object_type)
                                ],
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
                                        $this->sendMessGroupCardToBotTelegram("refund error " . $exception->getMessage());
                                    }
                                    return ['error_' => "Duyệt đơn thất bại. Vui lòng liên hệ admin", 'hold' => true];
                                }
                            } else {
                                $this->coinSerivce->SumCoin($user->id, $check_out_coin);
                                return ['error_' => $response->message ?? 'Mua thất bại'];
                            }
                        }
                        return returnDataError($checkCoinAndHandleCoin);
                        break;
                    case 'facebook_like_v9_':
                        $str_s = '';
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $package_name_master = json_decode($prices->package_name_master);
                            $form_data = [
                                'fanpage_id' => $object_id,
                                'type' => $package_name_master->name,
                                'number' => $quantity,
                                "speed" => $package_name_master->option,
                                "warranty_type" => 7,
                                "url_service" => $object_id,
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
                                        $this->sendMessGroupCardToBotTelegram("refund error " . $exception->getMessage());
                                    }
                                    return ['error_' => "Duyệt đơn thất bại. Vui lòng liên hệ admin", 'hold' => true];
                                }
                            } else {
                                $this->coinSerivce->SumCoin($user->id, $check_out_coin);
                                return ['error_' => $response->message ?? 'Mua thất bại'];
                            }
                        }
                        return returnDataError($checkCoinAndHandleCoin);
                        break;

                    case 'facebook_like_v7':
                    case 'facebook_like_v10':
                    case 'facebook_like_v13':
                        $post_data = $request->except('user');
                        $post_data['object_id'] = $object_id;
                        $post_data['type'] = 'like';
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
                                        $this->sendMessGroupCardToBotTelegram("refund error " . $exception->getMessage());
                                    }
                                    return ['error_' => $response->message ?? 'Tạo thất bại vui lòng liên hệ admin', 'hold' => true];
                                }
                            }

                        }
                        return returnDataError($checkCoinAndHandleCoin);
                        break;

                    case 'facebook_like_v8':
                    case 'facebook_like_v4':
                    case 'facebook_like_v6':
                    case 'facebook_like_v9':
                    case 'facebook_like_v2':
                    case 'facebook_like_v12':
                        $post_data['object_id'] = $object_id;
                        $post_data['product_id'] = $prices->package_name_master;
                        $post_data['quantity'] = $quantity;
                        $post_data['notes'] = $request->notes;
                        $post_data['reaction'] = $request->object_type;
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
                                    return ['error_' => $response->message ?? 'Tạo thất bại vui lòng liên hệ admin', 'hold' => true];
                                }
                            }
                        } else {
                            return ['error_' => $checkCoinAndHandle['error']];
                        }
                        break;
                    case 'facebook_like_v15_':
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandle = $this->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandle['error'])) {
                            $form_data = [
                                'action' => 'buy-orders-buff-api',
                                'seeding_uid' => intval($request->object_id),
                                'seeding_type' => $prices->package_name_master,
                                'order_amount' => $quantity,
                                'reaction_type' => null,
                                'comment_need' => $request->get('list_messages'),
                                'note' => $request->notes
                            ];
//                            dd($form_data);
                            $url = DOMAIN_SA_BOM_MO . '/api/index.php';
                            $response = $this->saBomMoService->callApi($form_data, $url);
                            if ($response && isset($response->result) && $response->result == true) {
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
                                    if ($response->msg == 'Bạn không đủ tiền. Vui lòng nạp thêm.') {
                                        $response->msg = "Tạo đơn thất bại. liên hệ admin";
                                    }
                                    return ['error_' => $response->msg ?? 'Tạo đơn thất bại'];
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
                    case 'facebook_like_v15':
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
                                "type" => 'like_post',
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
                    case 'facebook_like_v16':
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandle = $this->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        $config = json_decode($prices->package_name_master);
                        if (!isset($checkCoinAndHandle['error'])) {
                            $time = time() . '_' . str_rand(3);
                            $object_type = strtoupper($request->object_type) ?? null;
                            if ($object_type == 'like') {
                                return ['error_' => 'Gói này chỉ áp dụng cảm xúc LIKE'];
                            }
                            $form_data = [
                                'link' => $request->object_id,
                                'id' => $this->curlTraoDoiSub($request->object_id)->id ?? '',
                                'sl' => $quantity,
                                'loai' => "LIKE",
                                'tocdolen' => $config->tocdolen ?? '',
                                'maghinho' => $time,
                                'magiamgia' => null,
                                'dateTime' => urlencode(date('Y-m-d H:i:s')),
                            ];
                            $url = 'https://tuongtaccheo.com/tangcamxuc/themvip.php';
                            $response = $this->tuongTacCheoService->buy($url, $form_data);
                            if ($response && $response == 'Mua thành công') {
                                $ads = $request->all();
                                $ads['user_id'] = $user->id;
                                $ads['username'] = $user->username;
                                $ads['link'] = $request->object_id;
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
                                    if ($response == 1) {
                                        $response = "Vui lòng liên hệ admin";
                                    }
                                    return ['error_' => $response ?? 'Tạo đơn thất bại'];
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
                    case 'facebook_like_v17':
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandle = $this->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        $config = json_decode($prices->package_name_master);
                        if (!isset($checkCoinAndHandle['error'])) {
                            $time = time() . '_' . str_rand(3);
                            $object_type = strtoupper($request->object_type) ?? null;
                            $form_data = [
                                'link' => $request->object_id,
                                'id' => $this->curlTraoDoiSub($request->object_id)->id ?? '',
                                'sl' => $quantity,
                                'loai' => $config->loai ?? '',
                                'tocdolen' => $config->tocdolen ?? '',
                                'maghinho' => $time,
                                'magiamgia' => null,
                                'dateTime' => urlencode(date('Y-m-d H:i:s')),
                            ];
                            $url = 'https://tuongtaccheo.com/tanglike/themvip.php';
                            $response = $this->tuongTacCheoService->buy($url, $form_data);
                            if ($response && $response == 'Mua thành công') {
                                $ads = $request->all();
                                $ads['user_id'] = $user->id;
                                $ads['username'] = $user->username;
                                $ads['link'] = $request->object_id;
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
                                    if ($response == 1) {
                                        $response = "Vui lòng liên hệ admin";
                                    }
                                    return ['error_' => $response ?? 'Tạo đơn thất bại'];
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
                    case 'facebook_like_v18':
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandleCoin['error'])) {
                            $url = DOMAIN_TRAODOISUB . '/mua/likegiare/themid.php';
                            $time = time() . '_' . str_rand(3);
                            $post_data = [
                                'maghinho' => $time,
                                'id' => $request->object_id,
                                'sl' => $quantity,
                                'is_album' => 'note',
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
                    case 'facebook_like_v19':
                        $object_type = strtoupper($request->object_type);
                        $config_master = json_decode($prices->package_name_master);
                        $check_out_coin = $quantity * $pricesMin->prices;
                        $form_data = [
                            "service_id" => $config_master->service_id ?? '',
                            "seeding_uid" => $object_id,
                            "server_id" => $config_master->server_id,
                            "order_amount" => $quantity,
                            "reaction_type" => $object_type,
                            "commend_need" => '',
                        ];
                        $url = 'https://sabommo.net/api/buff-order?access_token=' . getConfig('sabommo');
                        $checkCoinAndHandle = $this->checkCoinAndHandleCoin($user->id, $check_out_coin);
                        if (!isset($checkCoinAndHandle['error'])) {
                            $response = $this->saBomMoService->callTrungGian($url, $form_data);
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
        return $this->returnActionApi($this->actionWarranty($request, $request->user, $id));
    }

    public function actionWarranty($request, $user, $id)
    {
        $log = Facebook::where('id', $id)->where(function ($q) use ($user) {
            if ($user->role != 'admin') {
                $q->where('user_id', $user->id);
            }
        })->first();
        if ($log) {
            if ($log->warranty == 2) {
                return ['error_' => 'Đang bảo hành'];
            }
            if ($log->warranty == 1) {
                if (in_array($log->package_name, $log->sabommo)) {
                    $response = $this->saBomMoService->checkWarranty($log->orders_id);
                    if ($response && isset($response->result) && $response->result == true) {
                        $log->warranty = 2;
                        $log->save();
                        return ['success' => 'Thành công'];
                    }
                    return ['error_' => $response->msg ?? 'Thất bại'];
                }
                if (in_array($log->package_name, $log->farm)) {
                    $response = $this->farmService->actionOrder(['orders_id' => $log->orders_id, 'action' => 'warranty_claim']);
                    if ($response && $response->status && $response->status == 200) {
                        $log->warranty = 2;
                        $log->save();
                        return ['success' => 'Thành công'];
                    }
                    return ['error_' => $response->mgs ?? 'Thất bại'];
                }
//                switch ($log->package_name) {
//                    case 'facebook_like_page_sv14':
//                    case 'facebook_like_page_sv15':
//                    case 'facebook_follow_sv13':
//                    case 'facebook_mem_no_avatar':
//                        //https://customer.sabommo.net/api/index.php?action=insurance-orders-buff-api&token=&id_user=&id=
//                        $post_data = [
//                            'action' => 'insurance-orders-buff-api',
//                            'id' => $log->orders_id,
//                        ];
//                        $url = DOMAIN_SA_BOM_MO . '/api/index.php';
//                        $response = $this->saBomMoService->callApi($post_data, $url);
//                        if ($response && isset($response->result) && $response->result == true) {
//                            $log->warranty = 2;
//                            $log->save();
//                            return ['success' => 'Thành công'];
//                        }
//                        return ['error_' => $response->msg ?? 'Thất bại'];
//                        break;
//                    default:
//                        $response = $this->farmService->actionOrder(['orders_id' => $log->orders_id, 'action' => 'warranty_claim']);
//                        if ($response && $response->status && $response->status == 200) {
//                            $log->warranty = 2;
//                            $log->save();
//                            return ['success' => 'Thành công'];
//                        }
//                        return ['error_' => $response->mgs ?? 'Thất bại'];
//                        break;
//                }
            }

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
        $log = Facebook::where('id', $id)->whereIn('status', [0, 1, -1])->where(function ($q) use ($user) {
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
                if ($item->count_is_run >= $item->quantity) {
                    $item->status = 2;
                    $item->warranty = 1;
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
            $url = DOMAIN_TANG_LIKE_CHEO . '/api/history?provider=facebook&limit=100&id=' . $item->orders_id;
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
        $item = $log;
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

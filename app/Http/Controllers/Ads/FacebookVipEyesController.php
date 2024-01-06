<?php

namespace App\Http\Controllers\Ads;

use App\Http\Controllers\Controller;
use App\Models\Ads\FacebookVip\VipEyes;
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

class FacebookVipEyesController extends Controller
{
    //
    protected $menu_id = 62;


    public function index()
    {
        $user = Auth::user();
        $menu = Menu::find($this->menu_id);
        $package = PricesConfig::getPricesByLevel($this->menu_id, $user->level);
        return view('Ads.Facebook.VipEyes.index', ['menu' => $menu, 'package' => $package]);
    }

    public function history(Request $request)
    {
        $menu = Menu::find($this->menu_id);
        if (Auth::user()->role == 'admin') {
            $data = VipEyes::where(function ($q) use ($request) {
                $key = $request->key;
                if ($key) {
                    $q->where('username', 'LIKE', '%' . $key . '%');
                    $q->orWhere('client_username', 'LIKE', '%' . $key . '%');
                    $q->orWhere('object_id', 'LIKE', '%' . $key . '%');
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
            $data = VipEyes::where('user_id', Auth::user()->id)->where(function ($q) use ($request) {
                $key = $request->key;
                if ($key) {
//                    $q->where('username', 'LIKE', '%' . $key . '%');
                    $q->orWhere('client_username', 'LIKE', '%' . $key . '%');
                    $q->orWhere('object_id', 'LIKE', '%' . $key . '%');
                }
            })->orderBy('id', 'DESC')->paginate(100);
        }

        return view('Ads.Facebook.VipEyes.history', ['data' => $data, 'menu' => $menu]);
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
        $validate = Validator::make($request->all(),
            [
                'object_id' => 'required|max:190',
                'amount' => 'required|integer|min:50|max:2000',
                'minutes' => 'required|integer|min:30|max:600',
                'num_dates' => 'required|integer|min:7|max:90',
                'max_order_per_day' => 'required|integer|min:1',
                'max_order' => 'required|integer|min:1',
                'package_name' => ['required', Rule::in(
                    Prices::getPackageNameAllow($this->menu_id)
                )],
            ],
            [
                'facebook_id.required' => 'Đường dẫn bài viết không được để trống',
                'amount.required' => 'Số lượng cần tăng không được để trống',
                'amount.integer' => 'Số lượng cần tăng phải là dạng số',
                'minutes.required' => 'Số phút cần tăng không được để trống',
                'minutes.integer' => 'Số phút cần tăng phải là dạng số',
                'num_dates.integer' => 'Số ngày phải là dạng số ',
                'num_dates.required' => 'Số ngày không được để trống',
                'max_order_per_day.integer' => 'Số hóa đơn trong một ngày phải là dạng số ',
                'max_order_per_day.required' => 'Số hóa đơn trong một ngày không được để trống',
                'max_order.integer' => 'Số đơn hàng tối đa trong một ngày phải là dạng số ',
                'max_order.required' => 'Số đơn hàng tối đa trong một ngày không được để trống',
            ]);
        if ($validate->fails()) {
            return ['error' => $validate->errors()];
        }

        $quantity = abs(intval($request->quantity));

        $package_name = $request->package_name;
        $object_id = $request->object_id;
        $num_day = abs(intval($request->num_day));
        $quantity = abs(intval($request->quantity));
        $amount = intval(abs($request->amount));
        $num_dates = intval(abs($request->num_dates));
        $max_order = intval(abs($request->max_order));
        $max_order_per_day = intval(abs($request->max_order_per_day));
        $minutes = intval(abs($request->minutes));
        $params = $request->all();
        $pricesData = Prices::getPrices($package_name, $user);
        if (!isset($pricesData['error'])) {
            $prices = $pricesData['price'];
            $pricesMin = $pricesData['price_config'];
            switch ($prices->package_name) {
                default:
                    $check_out_coin = $pricesMin->prices * $amount * $num_dates * $max_order_per_day * $minutes;
                    $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                    if (!isset($checkCoinAndHandleCoin['error'])) {
                        $url = DOMAIN_BUFFVIEWER . "/api/profilelivestreamunit/add";
                        $form_data = array();
                        $form_data[0]['facebook_id'] = $object_id;
                        $form_data[0]['name'] = $user->username;
                        $form_data[0]['amount'] = $params['amount'];
                        $form_data[0]['num_dates'] = $params['num_dates'];
                        $form_data[0]['minutes'] = $params['minutes'];
                        $form_data[0]['type'] = 0;
                        $form_data[0]['note'] = !empty($params['note']) ? $params['note'] : "";
                        $form_data[0]['account_source'] = "system";
                        $form_data[0]['max_order_per_day'] = $params['max_order_per_day'];
                        $form_data[0]['max_order'] = $params['max_order'];
                        $res = $response = $this->buffViewerService->callApi($form_data, $url);
                        if ($res && isset($res->data[0]->id)) {
                            $ads = \request()->all();
                            $ads['orders_id'] = $res->data[0]->id ?? 0;
                            $ads['profile_id'] = $res->data[0]->id ?? $object_id;
                            $ads['object_id'] = $object_id;
                            $ads['max_order_per_day'] = $max_order_per_day;
                            $ads['number_of_lives'] = $max_order;
                            $ads['num_dates'] = $num_dates;
                            $ads['num_minutes'] = $minutes;
                            $ads['user_id'] = $user->id;
                            $ads['username'] = $user->username;
                            $ads['package_name'] = $prices->package_name;
                            $ads['prices'] = $check_out_coin;
                            $ads['price_per'] = $pricesMin->prices;
                            $ads['quantity'] = $params['amount'];
                            $ads['start_like'] = 0;
                            $ads['price_id'] = $prices->id;
                            $ads['menu_id'] = $prices->menu_id;
                            $ads['server'] = $prices->name;
                            $ads['note'] = !empty($params['note']) ? $params['note'] : "";
                            $ads['time_expired'] = ($this->addDaysWithDate(date('Y-m-d H:i:s'), $num_dates));
                            $ads = VipEyes::newAds($ads);
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
                                'result' => json_encode($res),
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
            }
//            return returnDataError($checkMinMax);
        }
        return returnDataError($pricesData);
    }
}

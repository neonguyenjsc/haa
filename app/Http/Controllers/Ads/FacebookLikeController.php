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

class FacebookLikeController extends Controller
{
    //

    protected $menu_id = 42;

    protected $package_mfb = [
        'like' => 'seeding_by_workers_like',
        'love' => 'seeding_by_workers_love',
        'care' => 'seeding_by_workers_care',
        'haha' => 'seeding_by_workers_haha',
        'wow' => 'seeding_by_workers_wow',
        'sad' => 'seeding_by_workers_sad',
        'angry' => 'seeding_by_workers_angry',
    ];

    public function index()
    {
        $user = Auth::user();
        $menu = Menu::find($this->menu_id);
        $package = PricesConfig::getPricesByLevel($this->menu_id, $user->level);
        return view('Ads.Facebook.Like.index', ['menu' => $menu, 'package' => $package]);
    }

    public function history(Request $request)
    {
        $menu = Menu::find($this->menu_id);
        $data = $this->getHistory('facebook', $this->menu_id, $request);
        return view('Ads.Facebook.Like.history', ['data' => $data, 'menu' => $menu]);
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
                    default:
                        $pricesMaster = $this->mfbService->getPricesMaster(69, $this->package_mfb[$prices->package_name_master]);
                        if ($pricesMaster) {
                            $check_out_coin = $quantity * $pricesMin->prices;
                            $checkCoinAndHandleCoin = $this->coinSerivce->checkCoinAndHandleCoin($user->id, $check_out_coin);
                            if (!isset($checkCoinAndHandleCoin['error'])) {
//                                $post_data = $request->except('client_id','client_username');
                                $post_data = [];
                                $post_data['quantity'] = $quantity;
                                $post_data['notes'] = $request->notes;
                                $post_data['object_id'] = $object_id;
                                $post_data['prices'] = $pricesMaster * $quantity;
                                $post_data['is_warranty'] = false;
                                $post_data['link'] = 'https://www.facebook.com/' . $object_id;
                                $post_data['time_expired'] = $this->addDaysWithDate(date('Y-m-d H:i:s'), 7);
                                $post_data['type'] = $prices->package_name_master;
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
                                        'client_id' => $request->get('client_id') ?? 0,'user_id_agency_lv2' => $request->get('user_id_agency_lv2') ?? 0,
                                        'client_username' => $request->get('client_username') ?? '','username_agency_lv2' => $request->get('username_agency_lv2') ?? '',
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
                                    ]);
                                    $data_orders = [
                                        //'username' => Auth::user()->username,
                                        'Tên DV' => 'LIKE',
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
                                            'client_id' => $request->get('client_id') ?? 0,'user_id_agency_lv2' => $request->get('user_id_agency_lv2') ?? 0,
                                            'client_username' => $request->get('client_username') ?? '','username_agency_lv2' => $request->get('username_agency_lv2') ?? '',
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
                }
            }
            return returnDataError($checkMinMax);
        }
        return returnDataError($pricesData);
    }
}

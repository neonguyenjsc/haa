<?php

namespace App\Console\Commands;

use App\Http\Controllers\Traits\Lib;
use App\Models\Ads\Facebook\Facebook;
use App\Models\Ads\Proxy\Proxy;
use App\Models\AutoVietcombankLogs;
use App\Models\LogPayment;
use App\Models\Logs;
use App\Models\PricesConfig;
use App\Models\User;
use App\Service\Telegram\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Exception;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '1';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    use Lib;

    /*
     *
     bình thường
--------------------18-02-2023 10:15:49
--------------------18-02-2023 10:16:21
 lấy id rồi where in theo id đó
    --------------------18-02-2023 10:16:55
--------------------18-02-2023 10:17:21

     */

    public function buyV2_($data)
    {
        $data = [
            'service_id' => $data['service_id'],
            'seeding_uid' => $data['seeding_uid'],
            'server_id' => $data['server_id'],
            'order_amount' => $data['order_amount'],
            'reaction_type' => $data['reaction_type'],
            'commend_need' => $data['commend_need'],
        ];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://sabommo.net/api/buff-order?access_token=' . getConfig('sabommo'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);
        dd($response);
        try {
            $t = new TelegramService();
            $t->sendMessGroupCardToBotTelegram("sabommo => " . $response);
        } catch (Exception $exception) {
        }
        curl_close($curl);
        return json_decode($response);
    }

    public function handle()
    {
        $data = [
            "service_id" => "6",
            "seeding_uid" => "4",
            "server_id" => "1",
            "order_amount" => 500,
            "reaction_type" => "",
            "commend_need" => ""
        ];
        $this->buyV2_($data);
        dd(1);
        dd(Facebook::where('id', '>', 4300000)->whereIn('package_name', [
            'facebook_like_page_sv9',
            'facebook_like_page_sv10',
            'facebook_like_page_sv11',
            'facebook_like_page',
            'facebook_follow_sv4',
            'facebook_follow',
            'facebook_follow_sv14',
            'facebook_follow_sv12',
            'facebook_follow',
            'facebook_like_v8',
            'facebook_like_v4',
            'facebook_like_v6',
            'facebook_like_v9',
            'facebook_like_v2',
            'facebook_like_v12',
            'facebook_comment_sv3',
            'facebook_comment_sv4',
            'facebook_comment_sv5',
            'facebook_mem_v8',

        ])->whereIn('status', [1,])->count());
        exit();
        dd(PricesConfig::where('menu_id', 89)->where('level_id', 6)->where('status', 1)->where('active', 1)->orderBy('sort', 'asc')->get());
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.myip.com/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
        exit();
//        $logs = Logs::where('user_id', 109423)->where('action', 'buy')->sum('coin');
//        $tiktok = TikTok::where('user_id', 109423)->get();
//        foreach ($tiktok as $item) {
//            $l = Logs::where('user_id', 109423)->where('orders_id', $item->id)->delete();
//        }
//        dd(1);
        //        foreach ($l as $item) {
//            $item->delete();
//        }
//        dd($l);
        $logs = Logs::where('user_id', 109423)->where('action', 'buy')->sum('coin');
        dd($logs);
        exit();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => DOMAIN_FARM . '/seller/account/info',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => ['token' => getConfig('key_farm')],
            CURLOPT_HTTPHEADER => array(
                'Cookie: ci_session=f2kpqh1kvt6m0sh8qf0n9crrgul84oqb'
            ),
        ));

        $response = curl_exec($curl);
        dd($response);
        curl_close($curl);

        $response = json_decode($response);
        $coin = number_format($response->result->account->balance ?? 0);
        return "farm-giau => " . $coin;

        $test = Cache::rememberForever('test_1', function () {
            return Facebook::find(1);
        });
        $test->count_is_run = 9999;
        $test->save();
        exit();
        ini_set('memory_limit', '2256M');
        $date = [
            '2022-09-05 10:51:00',
            '2022-12-30 23:59:59',
        ];
        $list = Facebook::where('package_name', 'facebook_share_sv2')->whereBetween('created_at', $date)->sum('quantity');
        //$list = TikTok::whereIn('package_name', [
//            'tiktok_follow_sv6'
        // 'tiktok_like_v5'
        //])->whereBetween('created_at', $date)->sum('quantity');

        dd($list);

        $user = User::where('api_key', null)->orderBy('id', 'desc')->get();
        foreach ($user as $item) {
            if (!$item->api_key || $item->api_key == '' || $item->api_key == null) {
                if ($item) {
                    $item->change_password_at = strtotime('now');
                    $item->api_key = base64_encode($item->id . strtolower('now') . str_rand('25')) . "Xc";
                    $item->save();
                }
            }
        }
        exit();
        dd("Không có cahe");
        exit();
//        $date = [
//            '2022-04-10 00:00:00',
//            '2022-04-21 00:00:00',
//        ];
//        $list = TikTok::whereIn('package_name', [
////            'tiktok_follow_sv6'
//            'tiktok_like_v3'
//        ])->whereBetween('created_at', $date)->sum('quantity');
        dd($list);
        exit();
        $l = Logs::where('post_data', 'LIKE', '%sv1_proxy%')->orderBy('id', 'DESC')->get();
        foreach ($l as $item) {
            $response = json_decode($item->result);
            try {
                $p = Proxy::where('ip', $response->data[0]->host)->where('port', $response->data[0]->port)->first();
                if ($p) {
                    if (isset($response->data[0]->username)) {

                        $p->proxy_username = $response->data[0]->username;
                    }
                    if (isset($response->data[0]->password)) {


                        $p->proxy_password = $response->data[0]->password;
                    }
                    $p->save();
                }
            } catch (\Exception $e) {
            }
        }
        exit();
        $p = LogPayment::find(126979);
        dd(json_encode($p->toArray()));
        $now = Carbon::now();
        $monthStartDate = $now->startOfMonth()->format('Y-m-d 00:00:00');
        $monthEndDate = $now->endOfMonth()->format('Y-m-d 23:59:59');
        $date = [
            $monthStartDate,
            $monthEndDate,
        ];
        $item_coin_in = Logs::where('action_coin', 'in')->whereBetween('created_at', $date)->sum('coin');
        $item_coin_out = Logs::where('action_coin', 'out')->whereBetween('created_at', $date)->sum('coin');
        echo number_format($item_coin_in);
        echo "\n";
        echo number_format($item_coin_out);
        exit();
        //tiktok_follow_sv6
//        $date = [
//            '2022-02-21 16:00:00',
//            '2022-03-03 20:00:00',
//        ];
//        $list = TikTok::whereIn('package_name', [
//            'tiktok_follow_sv6',
//        ])->whereBetween('created_at', $date)->sum('quantity');
//        dd($list);
//        exit();
//        $z = '{"object_id":"100008323604482","package_name":"facebook_follow_sv5","quantity":"5000"}';
//        $item = Logs::where('id', '>', 2472314)->where('action', 'buy')->get();
//        foreach ($item as $item) {
//            $post_data = json_decode($item->post_data);
//            if (isset($post_data->package_name) && $post_data->package_name == 'facebook_follow_sv5') {
//                try {
//                    $check_out_coin = $item->coin;
//                    $count_is_run = $post_data->quantity;
//                    $user_id = $item->user_id;
//                    $username = $item->username;
//                    $object_id = $item->object_id;
//                    Refund::newRefund([
//                        'user_id' => $user_id,
//                        'username' => $username,
//                        'client_id' => $item->client_id ?? -1,
//                        'client_username' => $item->client_username ?? -1,
//                        'object_id' => $object_id,
//                        'coin' => $check_out_coin,
//                        'quantity' => $count_is_run,
//                        'price_per_agency' => $post_data->price_per_agency ?? -1,
//                        'prices_agency' => $post_data->prices_agency ?? -1,
//                        'description' => "Hoàn tiền đơn " . $item->object_id . " ",
//                        'status' => 0,
//                        'category_id' => 1,
//                        'tool_name' => "S1 Follow Mới  [Done trong 24h - 48h] Độc quyền | BH 7 day",
//                        'package_name' => $post_data->package_name ?? '',
//                        'server' => "S1 Follow Mới  [Done trong 24h - 48h] Độc quyền | BH 7 day",
//                        'vat' => 0,
//                        'user_id_agency_lv2' => $post_data->user_id_agency_lv2 ?? -1,
//                        'prices_agency_lv2' => $post_data->prices_agency_lv2 ?? -1,
//                        'price_per_agency_lv2' => $post_data->price_per_agency_lv2 ?? -1,
//                        'price_per_remove' => 0,
//                        'orders_id' => 0,
//                        'table' => 'youtube',
//                        'quantity_buy' => $post_data->quantity,
//                        'price_per' => $item->price_per ?? -1,
//                        'username_agency_lv2' => $post_data->username_agency_lv2 ?? -1,
//                        'response' => '',
//                    ]);
//                } catch (\Exception $exception) {
//                    echo "\n";
//                    echo $exception->getMessage();
//                    echo $item->id;
//                    echo  "\n";
//                }
//            }
//        }

//        $list = Facebook::where('package_name', 'facebook_follow_sv11')->whereBetween('created_at', ['2022-02-23 06:00:00', '2022-02-23 23:59:00'])->get();
//        foreach ($list as $item) {
//            $item = $item;
//            $q = $count_is_run = $item->quantity;
//            $check_out_coin = ($item->prices);
//            $check_out_coin = $check_out_coin + ($check_out_coin * 10 / 100);
//            Refund::newRefund([
//                'user_id' => $item->user_id,
//                'username' => $item->username,
//                'client_id' => $item->client_id,
//                'client_username' => $item->client_username,
//                'object_id' => $item->object_id,
//                'coin' => $check_out_coin,
//                'quantity' => $count_is_run,
//                'price_per_agency' => $item->price_per_agency,
//                'prices_agency' => $item->prices_agency,
//                'description' => "ADM Báo Star  hoàn tiền cho bạn  id  " . $item->object_id . "  và + thêm 10 % giá trị của đơn như lời xin lỗi vì  không thể chạy được . Mong bạn thông cảm cho . ",
//                'status' => 0,
//                'category_id' => 1,
//                'tool_name' => $item->server,
//                'package_name' => $item->package_name,
//                'server' => $item->server,
//                'vat' => 0,
//                'user_id_agency_lv2' => $item->user_id_agency_lv2,
//                'prices_agency_lv2' => $item->prices_agency_lv2,
//                'price_per_agency_lv2' => $item->price_per_agency_lv2,
//                'price_per_remove' => 0,
//                'orders_id' => $item->id,
//                'table' => 'youtube',
//                'quantity_buy' => $item->quantity,
//                'price_per' => $item->price_per,
//                'username_agency_lv2' => $item->username_agency_lv2,
//                'response' => '',
//            ]);
//        }
//        $date = [
//
//            '2022-02-18 22:30:00',
//            '2022-02-20 12:26:00',
//        ];
//        $list = Facebook::whereIn('package_name', [
//            'facebook_follow_sv11',
//            'facebook_follow_sv4_',
//            'facebook_follow_sv7',
//            'facebook_follow_sv18',
//            'facebook_follow_sv16',
//        ])->whereBetween('created_at', $date)->sum('quantity');
//        dd($list);
//        $date = [
//            '2022-02-11 12:00:00',
//            '2022-02-21 21:21:00',
//        ];
//        $list = TikTok::whereIn('package_name', [
//            'tiktok_follow_sv6'
//        ])->whereBetween('created_at', $date)->sum('quantity');
//        dd($list);

//    case 'facebook_follow_sv6':
//                    case 'facebook_follow_sv4_':
//                    case 'facebook_follow_sv7':
//                    case 'facebook_follow_sv18':
//                    case 'facebook_follow_sv16':
//                    case 'facebook_follow_sv11':

//        $regex = '/target="_blank">([0-9]{8,})(<\/a>)/';
//        $filename = "D:\\Tenviet\\list_follow . txt";
//        $fp = fopen($filename, "r");//mở file ở chế độ đọc
//        $contents = fread($fp, filesize($filename));
//        $contents = str_replace("\n", " | ", $contents);//đọc file
//        preg_match_all($regex, $contents, $data);
//        fclose($fp);//đóng file
//        $str = '';
//        foreach (array_unique($data[1]) as $item) {
//            $str = $str . $item . "\n";
//        }
//        $fp = fopen('D:\\Tenviet\\list_follow1.txt', 'w');//mở file ở chế độ write-only
//        fwrite($fp, $str);
//        fclose($fp);
//
//        echo "File được ghi thành công!";
//        $date = [
//
//            '2022-02-12 00:00:00',
//            '2022-02-12 23:59:59',
//        ];
//        $list = Facebook::whereIn('package_name', [
//            'facebook_follow_sv6',
//            'facebook_follow_sv4_',
//            'facebook_follow_sv7',
//            'facebook_follow_sv18',
//            'facebook_follow_sv16',
//            'facebook_follow_sv11',
//            'facebook_follow_sv4',
//            'facebook_follow_sv13',
//        ])->whereBetween('created_at', $date)->sum('quantity');
//        dd($list);
//        $role = ['user', 'admin'];
//        foreach ($role as $item) {
//            $key_cache = 'category_v2_' . $item;
//            Cache::forget($key_cache);
//        }
        //        $url = 'google.com';
//        $data = ['a' => 1, 'b' => 2];
//        $url = sprintf(" % s ?%s", $url, http_build_query($data));
//        echo " < pre>";
//         print_r($value = $url);
//        echo " </pre > ";
//        exit();

        //        $items = Logs::where('id', '>', 750278)->where('id', '<', 750441)->get();

//        $str = "https://scontent.xx.fbcdn.net/v/t1.6435-1/cp0/p32x32/140945842_752359065387788_3286173316086993192_n.jpg?_nc_cat=106&ccb=1-3&_nc_sid=7206a8&_nc_ohc=T0Xran5pkhgAX8NE-rS&_nc_ad=z-m&_nc_cid=0&_nc_ht=scontent.xx&oh=9014a2c4488c8bae878c39f2cdca2b63&oe=612D9933";
//
//        $items = PaymentAutoMomo::where('id', '>', 35835)->get();
//        $count = count($items);
//        echo $count . "\n";
//        foreach ($items as $item) {
//            $user = User::where('id', $item->user_id)->first();
//            if ($user) {
//                $coin_user = $user->coin;
//                $coin = $item->amount;
//                $coin_handle = $user->coin - $coin;
//                if ($coin_handle < 0) {
//                    $user->coin = 0;
//                } else {
//                    $user->coin = $coin_handle;
//                }
//                $user->save();
//                try {
//                    Logs::newLogs([
//                        'user_id' => $user->id,
//                        'username' => $user->username,
//                        'client_user_id' => null,
//                        'client_username' => null,
//                        'action' => 'handle_coin',
//                        'action_coin' => 'in',
//                        'type' => 'add_coin',
//                        'description' => 'Admin trừ ' . $coin . ' đ .',
//                        'coin' => $coin,
//                        'old_coin' => $coin_user,
//                        'new_coin' => $user->coin,
//                        'price_id' => 0,
//                        'object_id' => null,
//                        'post_data' => json_encode($item),
//                        'result' => true,
//                        'ip' => '',
//                    ]);
//                } catch (\Exception $exception) {
//                    echo $exception->getMessage();
//                    exit();
//                }
//            }
//        }
    }

    public function ref()
    {
        //        $list = Facebook::where('package_name', 'facebook_like_page_sv17')->where('status', 0)->get();
//        foreach ($list as $log) {
//            if (!Refund::where('table', 'facebook')->where('orders_id', $log->id)->first()) {
//                Refund::newRefund([
//                    'user_id' => $log->user_id,
//                    'username' => $log->username,
//                    'client_id' => $log->client_id,
//                    'client_username' => $log->client_username,
//                    'object_id' => $log->object_id,
//                    'coin' => 0,
//                    'quantity' => 0,
//                    'price_per_agency' => $log->price_per_agency,
//                    'prices_agency' => $log->prices_agency,
//                    'description' => 'Đang xử lý',
//                    'status' => -1,
//                    'category_id' => 1,
//                    'tool_name' => $log->server,
//                    'package_name' => $log->package_name,
//                    'server' => $log->server,
//                    'vat' => 0,
//                    'user_id_agency_lv2' => $log->user_id_agency_lv2,
//                    'prices_agency_lv2' => $log->prices_agency_lv2,
//                    'price_per_agency_lv2' => $log->price_per_agency_lv2,
//                    'price_per_remove' => 1000,
//                    'orders_id' => $log->id,
//                    'table' => 'facebook',
//                ]);
//            }
//
//        }
//        exit();
//        $baostarService = new BaostarService();
//        $ads = Facebook::where('id', '>', 3778526)->where('status', '<>', 0)->where('package_name', 'facebook_follow_sv19')->orderBy('id', 'desc')->get();
//        foreach ($ads as $item) {
//            $check = Refund::where('orders_id', $item->id)->where('table', 'facebook')->first();
//            if ($check) {
//                continue;
//            } else {
//                $url = DOMAIN_BAOSTAR_TOOL . '/api/jobs-action/' . $item->orders_id;
//                $data = [
//                    'action' => 'remove'
//                ];
//                $response = $baostarService->actionJobs($url, $data);
//                if ($response && $response->status == 200) {
//                    $user = User::find($item->user_id);
//                    $item->status = 0;
//                    $item->save();
//                    Logs::newLogs([
//                        'user_id' => $item->user_id,
//                        'username' => $item->username,
//                        'client_id' => $item->client_id,
//                        'client_username' => $item->client_username,
//                        'action' => 'remove',
//                        'action_coin' => 'out',
//                        'type' => 'out',
//                        'description' => 'Hủy đơn thành công ' . $item->server . ' cho ' . $item->object_id,
//                        'coin' => 0,
//                        'old_coin' => $user->coin,
//                        'new_coin' => $user->coin - 0,
//                        'price_id' => $item->price_id,
//                        'object_id' => $item->object_id,
//                        'post_data' => json_encode([]) . "\n" . json_encode($user),
//                        'result' => json_encode($response ?? []),
//                        'ip' => '',
//                        'package_name' => $item->package_name ?? '',
//                        'orders_id' => $item->id ?? 0,
//                    ]);
//                    try {
//                        Refund::newRefund([
//                            'user_id' => $item->user_id,
//                            'username' => $item->username,
//                            'client_id' => $item->client_id,
//                            'client_username' => $item->client_username,
//                            'object_id' => $item->object_id,
//                            'coin' => 0,
//                            'quantity' => 0,
//                            'price_per_agency' => $item->price_per_agency,
//                            'prices_agency' => $item->prices_agency,
//                            'description' => 'Đang xử lý',
//                            'status' => -1,
//                            'category_id' => 1,
//                            'tool_name' => $item->server,
//                            'package_name' => $item->package_name,
//                            'server' => $item->server,
//                            'vat' => 0,
//                            'user_id_agency_lv2' => $item->user_id_agency_lv2,
//                            'prices_agency_lv2' => $item->prices_agency_lv2,
//                            'price_per_agency_lv2' => $item->price_per_agency_lv2,
//                            'price_per_remove' => 0,
//                            'orders_id' => $item->id,
//                            'table' => 'facebook',
//                        ]);
//                    } catch (\Exception $exception) {
//
//                    }
//                    echo_now("Hủy thành công id " . $item->id);
//                }
//            }
//
//        }
//
//        exit();
    }

    public
    function call247()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.autolike.cc/public-api/v1/agency/services/all',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
    "limit": 10000
    
}',
            CURLOPT_HTTPHEADER => array(
                'token: XWTE8T37V7TL4AGMUVWFVBKEDA46B3AJ',
                'agency-secret-key: c05a205d-a4c8-11eb-9dbc-d094668900ec',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
//        echo $response;
        return json_decode($response);


    }
}

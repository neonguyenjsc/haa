<?php

namespace App\Console\Commands;

use App\Http\Controllers\Traits\Lib;
use App\Models\Ads\Facebook\Facebook;
use App\Models\Ads\Instagram\Instagram;
use App\Models\Ads\TikTok\TikTok;
use App\Models\Refund;
use App\Models\User;
use App\Service\FarmApi\FarmService;
use App\Service\SaBomMoService;
use App\Service\ViewYT\ViewYTService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class AutoRefundOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto_refund_order';

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

    public function handle()
    {
        $key_cahe = $this->signature;
        if (Cache::has($key_cahe)) {
//            exit();
        }
        Cache::remember($key_cahe, 3600 * 2, function () {
            return true;
        });

        $time = time() - 1200;
        try {
            $list = Refund::where('id', '>', 104308)->where('status', -1)->where('time_create', '<', $time)->orderBy('id', 'desc')->get();
            foreach ($list as $item) {
                echo $item->id, "\n";
                try {
                    switch ($item->table) {
                        case 'instagram':
                            $modal = new Instagram();
                            break;
                        case 'tiktok':
                            $modal = new TikTok();
                            break;
                        case 'facebook':
                            $modal = new Facebook();
                            break;
                        default:
                            $item->description = "Chưa cài đặt hoặc không thể check đơn !!";
                            $item->status = -4;
                            $item->response = json_encode($response ?? []);
                            $item->save();
                            break;
                    }
                    $log = $modal->where('id', $item->orders_id)->whereIn('status', [-1, 6, 0])->first();

                    if ($log) {
                        $log->status = 3;
                        $log->save();
                        $item->quantity_buy = $log->quantity;
                        $item->price_per = $log->price_per;
                        $orders_id = $log->orders_id;
                        $object_id = $log->object_id;
                        $count_is_run = -1;
                        switch ($log->package_name) {
                            case 'facebook_follow_sv1'://tlc
                            case 'facebook_follow_sv15'://tlc
                            case 'facebook_like_v3'://tlc
                            case 'facebook_like_v7'://tlc
                            case 'facebook_like_v10'://tlc
                            case 'facebook_comment_sv1'://tlc
                            case 'facebook_comment_sv10'://tlc
                            case 'facebook_like_v13'://tlc
                            case 'facebook_like_page_sv17'://tlc
                                $url = DOMAIN_TANG_LIKE_CHEO . "/api/history?id=" . $log->orders_id;
                                $response = $this->curlToMrDark($url, [], 'GET');
                                foreach ($response->data as $item_response) {
                                    if ($item_response->_id == $log->orders_id) {
                                        $response = $item_response;
                                    }
                                }
                                $count_is_run = $response->count_is_run;
                                break;
                            case 'instagram_follow_sv1':
                            case 'instagram_like_sv1':
                            case 'instagram_like_sv5':
                            case 'instagram_follow_sv5':
                            case 'instagram_follow_sv9':
                                $object_id = $this->convertUidIgCommand($object_id);
                                $url = DOMAIN_TANG_LIKE_CHEO . "/api/history?provider=instagram&id=" . $log->orders_id;
                                $response = $this->curlToMrDark($url, [], 'GET');
                                foreach ($response->data as $item_response) {
                                    if ($item_response->_id == $log->orders_id) {
                                        $response = $item_response;
                                    }
                                }
                                $count_is_run = $response->count_is_run;
                                break;
                                break;
                            case '30_2'://like corona mfb
                            case '32_2'://corona
                            case 'facebook_like'://corona

                                $url = 'https://api.mfb.vn/api/advertising/list?orders_id=' . $orders_id;
                                $response = $this->callApiMfb([], $url);
                                if (isset($response->data[0])) {
                                    $count_is_run = $response->data[0]->count_is_run;
                                } else {
                                    $item->description = "Chưa cài đặt hoặc không thể check đơn này";
                                    $item->status = -2;
                                    $item->response = json_encode($response ?? []);
                                    $item->save();
                                }
                                break;
                            case '32_5'://sv3 mfbb
                            case '30_9'://like sv3 mfb
                            case '33_11'://like sv3 mfb
                            case 'facebook_like_v14'://like sv3 mfb
                                $url = 'https://api.mfb.vn/api/facebook-ads/list?orders_id=' . $orders_id;
                                $response = $this->callApiMfb([], $url);
                                if (isset($response->data[0])) {
                                    $count_is_run = $response->data[0]->count_is_run;
                                } else {
                                    $item->description = "Chưa cài đặt hoặc không thể check đơn này";
                                    $item->status = -2;
                                    $item->response = json_encode($response ?? []);
                                    $item->save();
                                }
                                break;
                            case 'instagram_follow_sv10'://mfb ig
                            case 'instagram_follow_sv2'://mfb ig
                            case 'instagram_like'://mfb ig
                            case 'instagram_like_sv6'://mfb ig
                                $url = 'https://api.mfb.vn/api/instagram-ads/list?orders_id=' . $orders_id;
                                $response = $this->callApiMfb([], $url);
                                if (isset($response->data[0])) {
                                    $count_is_run = $response->data[0]->count_is_run;
                                } else {
                                    $item->description = "Chưa cài đặt hoặc không thể check đơn này";
                                    $item->status = -2;
                                    $item->response = json_encode($response ?? []);
                                    $item->save();
                                }
                                break;
                            case '61_1':
                            case '61_2': //mfb instagram
                                $url = 'https://api.mfb.vn/api/advertising/instagram/list?orders_id=' . $orders_id;
                                $response = $this->callApiMfb([], $url);
                                $count_is_run = $response->data[0]->count_is_run;
                                break;
                            case '80_1':
                            case '81_1'://mfb tiktok
                                ///
                                $url = 'https://api.mfb.vn/api/advertising/tiktok/list?orders_id=' . $orders_id;
                                $response = $this->callApiMfb([], $url);
                                if (isset($response->data[0])) {
                                    $count_is_run = $response->data[0]->count_is_run;
                                } else {
                                    $item->description = "Chưa cài đặt hoặc không thể check đơn này";
                                    $item->status = -2;
                                    $item->response = json_encode($response ?? []);
                                    $item->save();
                                }
                                break;
                            case '33_13':
                            case '33_19'://sabommo
                                $service = new SaBomMoService();
                                $form_data = [
                                    'action' => 'process-orders-buff',
                                    'id' => $orders_id,
                                ];
                                $url = DOMAIN_SA_BOM_MO . '/api/index.php';
                                $response = $service->callApi($form_data, $url);
                                if (!isset($response->data[0]->seeding_num)) {
                                    $item->description = "Chưa cài đặt hoặc không thể check đơn này";
                                    $item->status = -2;
                                    $item->response = json_encode($response ?? []);
                                    $item->save();
                                } else {
                                    $count_is_run = $response->data[0]->seeding_num;
                                }
                                break;
                            case '33_4'://sbook
                                $data = $this->curl('https://sbooks.me/api/get_sub/?uid=' . $item->object_id);
                                $response = json_decode($data);
                                $count_is_run = -1;
                                if ($response->da_tang) {
                                    $count_is_run = $response->da_tang;
                                } else {
                                    $item->description = "Chưa cài đặt hoặc không thể check đơn này";
                                    $item->status = -2;
                                    $item->response = json_encode($response ?? []);
                                    $item->save();
                                }
                                break;
                            case 'facebook_like_page_sv9':
                            case 'facebook_like_page_sv10':
                            case 'facebook_like_page_sv11':
                            case 'facebook_like_page':
                            case 'facebook_follow_sv4':
                            case 'facebook_follow_sv14':
                            case 'facebook_follow_sv12':
                            case 'facebook_follow':
                            case 'facebook_like_v8':
                            case 'facebook_like_v4':
                            case 'facebook_like_v6':
                            case 'facebook_like_v9':
                            case 'facebook_like_v2':
                            case 'facebook_like_v12':
                            case 'facebook_comment_sv3':
                            case 'facebook_comment_sv4':
                            case 'facebook_comment_sv5':
                            case 'facebook_mem_v8':
                                $farmService = new FarmService();
                                $response = $farmService->checkOrder(['orders_id' => $log->orders_id]);
                                if (isset($response->result->order)) {
                                    $count_is_run = $response->result->order->job_success;
                                }
                                if ($count_is_run == -1) {
                                    $item->description = "Chưa cài đặt hoặc không thể check đơn này";
                                    $item->status = -2;
                                    $item->response = json_encode($response ?? []);
                                    $item->save();
                                }
                                break;
                            case'facebook_like_page_sv14':
                            case 'facebook_like_page_sv15':
                            case 'facebook_follow_sv13':
                            case 'facebook_mem_no_avatar':
                                $service = new SaBomMoService\SaBomMoService();
                                $response = $service->checkOrderV2($log->orders_id);
                                if (!isset($response->data[0]->seeding_num)) {
                                    $item->description = "Chưa cài đặt hoặc không thể check đơn này";
                                    $item->status = -2;
                                    $item->response = json_encode($response ?? []);
                                    $item->save();
                                } else {
                                    $count_is_run = $response->data[0]->seeding_num;
                                }
                                break;
                            case 'instagram_like_sv2':
                            case 'instagram_follow_sv3':
                            case 'instagram_like_sv3':
                            case 'instagram_like_sv4':
                            case 'instagram_view_99':
                            case 'instagram_story_view_253':
                            case 'instagram_view_story_sv0':
                            case 'instagram_view_impression_100':
                            case 'instagram_view_impression_101':
                            case 'instagram_view_sv0':
                                $data = [
                                    'order' => $log->orders_id,
                                    'action' => 'status',
                                ];
                                $viewyt = new ViewYTService();
                                $response = $viewyt->checkOrder($data);
                                $o = $log->orders_id;
                                if ($response && isset($response->start_count)) {
                                    $count_is_run = $log->quantity - $response->remains;
                                } else {
                                    $item->description = "Chưa cài đặt hoặc không thể check đơn này. Không có data!";
                                    $item->status = -2;
                                    $item->response = json_encode($response ?? []);
                                    $item->save();
                                }
                                break;
                            case 'facebook_comment_sv7':
                            case 'facebook_follow_sv19':
                            case 'facebook_like_page_sv16':
                            case 'facebook_mem_v10':
                            case 'facebook_like_v15':
                            case 'facebook_share_sv8':
                            case 'facebook_view_21':
                            case 'facebook_view_story_v4':
                                $response = $this->callApiBaostar($log->orders_id);
                                if ($response && isset($response->data[0]->count_is_run)) {
                                    $count_is_run = $response->data[0]->count_is_run;
                                }
                                if ($count_is_run == -1) {
                                    $item->description = "Chưa cài đặt hoặc không thể check đơn này";
                                    $item->status = -2;
                                    $item->response = json_encode($response ?? []);
                                    $item->save();
                                }
                                break;
                            default:
                                $item->description = "Chưa hỗ trợ hoàn tiền cho gói này";
                                $item->status = -4;
                                $item->response = json_encode($response ?? []);
                                $item->save();
                                break;
                        }
                        if ($count_is_run < 0) {
                            $item->description = "Chưa cài đặt hoặc không thể check đơn này!!";
                            $item->status = -2;
                            $item->response = json_encode($response) . "|" . $count_is_run;
                            $item->save();
                            continue;
                        }
                        $log->count_is_run = $count_is_run;
                        $log->save();
                        $q = $log->quantity - $count_is_run;
                        $check_out_coin = ($q * $log->price_per) - $item->price_per_remove;
                        if ($check_out_coin > 0) {
                            $user = User::find($log->user_id);
                            $item->user_id = $log->user_id;
                            $item->username = $log->username;
                            $item->client_id = $log->client_id;
                            $item->client_username = $log->client_username;
                            $item->price_per = $log->price_per;
                            $item->quantity = $q;
                            $item->description = "Hệ thống hoàn tiền cho bạn " . number_format($check_out_coin) . " tương ứng " . number_format($q) . " lượt tương tác cho uid " . $object_id . " và " . $item->price_per_remove . ' phí dịch vụ';
                            $item->status = 0;
                            $item->category_id = $log->category_id;
                            $item->tool_name = $log->server ?? '';
                            $item->package_name = $log->package_name;
                            $item->server = $log->server;
                            $item->prices_agency = $log->prices_agency;
                            $item->price_per_agency = $log->price_per_agency;
                            $item->vat = 1;
                            $item->user_id_agency_lv2 = $log->user_id_agency_lv2;
                            $item->prices_agency_lv2 = $log->prices_agency_lv2;
                            $item->price_per_agency_lv2 = $log->price_per_agency_lv2;
                            $item->username_agency_lv2 = $log->username_agency_lv2;
                            $item->quantity_buy = $log->quantity;
                            $item->coin = $check_out_coin;
                            $item->response = json_encode($response);
                            $item->save();
                        } else {
                            $item->status = -2;
                            $item->quantity = $q;
                            $item->coin = $check_out_coin;
                            $item->description = "Số tiền quá nhỏ không thể hoàn " . $check_out_coin;
                            $item->response = json_encode($response);
                            $item->save();
                        }
                        echo $log->package_name . "=>" . $count_is_run;
                        echo "\n";
                    }
                } catch (\Exception $exception) {

                    $item->status = -5;
                    $item->description = "Lỗi server => " . $exception->getMessage() . " => line " . $exception->getLine() . " file =>" . $exception->getFile();
                    $item->response = json_encode($response ?? []);
                    $item->save();
                }
            }
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }

        Cache::forget($key_cahe);
    }

    public function callApiBaostar($id)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => DOMAIN_BAOSTAR_TOOL . '/api/list?id=' . $id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
//            CURLOPT_POSTFIELDS => ,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    public function callAutoLikeCC1($data)
    {
        $data_config_token = getConfig('key_autolike_v2');
        $data_config_token = json_decode($data_config_token);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.autolike.cc/public-api/v1/agency/services/all-by-codes',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'authority: api-autolike.congaubeo.us',
                'accept: application/json',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36',
                'token: ' . $data_config_token->token ?? '',
                'agency-secret-key: ' . $data_config_token->agency_secret_key ?? '',
                'content-type: application/json',
                'origin: https://www.mottrieu.com',
                'sec-fetch-site: cross-site',
                'sec-fetch-mode: cors',
                'sec-fetch-dest: empty',
                'referer: https://www.mottrieu.com/',
                'accept-language: vi',
                'Cookie: __cfduid=d6f82b0ecbbd5fcdc3d82712dfa53082e1609122016'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    public function convertUidIgCommand($link)
    {
        $data = explode('/', $link);
        if (isset($data[3])) {
            if ($data[3] == 'p') {
                return $data[4] ?? false;
            } else {
                return $data[3];
            }
        }
        return $link;
    }
}

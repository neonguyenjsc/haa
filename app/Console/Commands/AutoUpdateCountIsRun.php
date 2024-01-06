<?php

namespace App\Console\Commands;

use App\Models\Ads\Facebook\Facebook;
use App\Models\Ads\Instagram\Instagram;
use App\Models\Ads\TikTok\TikTok;
use App\Models\Config;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AutoUpdateCountIsRun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_count_is_run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    protected $limit = 5000;

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
     * @return mixed
     */
    public function handle()
    {
        echo "
                  _       _                                _      _
  _   _ _ __   __| | __ _| |_ ___     ___ ___  _   _ _ __ | |_   (_)___    _ __ _   _ _ __
 | | | | '_ \ / _` |/ _` | __/ _ \   / __/ _ \| | | | '_ \| __|  | / __|  | '__| | | | '_ \
 | |_| | |_) | (_| | (_| | ||  __/  | (_| (_) | |_| | | | | |_   | \__ \  | |  | |_| | | | |
  \__,_| .__/ \__,_|\__,_|\__\___|   \___\___/ \__,_|_| |_|\__|  |_|___/  |_|   \__,_|_| |_|
       |_|
\n";
        $keyCacheAutoUpdateCountIsRun = build_key_cache(['keyCacheAutoUpdateCountIsRun']);
        //$this->sendMessToBotTelegram("AUTO UPDATE COUNT IS RUN AGENCY LV2");
        if (Cache::has($keyCacheAutoUpdateCountIsRun)) {
            echo_now("Cron này đang được chạy vui lòng quay lại sau ít phút");
            exit();
        }
        Cache::remember($keyCacheAutoUpdateCountIsRun, 3600, function () {
            return true;
        });
        try {
            $this->mfb();
        } catch (\Exception $exception) {
            Cache::forget($keyCacheAutoUpdateCountIsRun);
            echo $exception->getMessage();
        }
        Cache::forget($keyCacheAutoUpdateCountIsRun);
        exit();
    }

    public function mfb()
    {
        $array = [
            'facebook' => [
                'url' => 'https://api.mfb.vn/api/advertising/list?limit=' . 200,
                'provider' => 'facebook',
            ],
            'facebook1' => [
                'url' => 'https://api.mfb.vn/api/facebook-ads/list?limit=' . 9999,
                'provider' => 'facebook1',
            ],
            'facebook2' => [
                'url' => 'https://api.mfb.vn/api/advertising/list?limit=' . 200 . '&type=like',
                'provider' => 'facebook2',
            ],
            'instagram' => [
                'url' => 'https://api.mfb.vn/api/advertising/instagram/list?limit=' . 200,
                'provider' => 'instagram',
            ],
            'instagram1' => [
                'url' => 'https://api.mfb.vn/api/advertising/instagram/list?limit=' . 200 . '&type=follow',
                'provider' => 'instagram1',
            ],
            'instagram2' => [
                'url' => 'https://api.mfb.vn/api/instagram-ads/list?limit=500&type=instagram_like_sv3',
                'provider' => 'instagram1',
            ],
            'tiktok' => [
                'url' => 'https://api.mfb.vn/api/advertising/tiktok/list?limit=200&type=like',
                'provider' => 'tiktok',
            ],
            'tiktok1' => [
                'url' => 'https://api.mfb.vn/api/advertising/tiktok/list?limit=200&type=follow',
                'provider' => 'tiktok1',
            ],
            'tiktok2' => [
                'url' => 'https://api.mfb.vn/api/advertising/tiktok/list?limit=500',
                'provider' => 'tiktok2',
            ],
        ];
        echo_now("-----------------------------------------------");
        foreach ($array as $item_provider) {
            echo_now("Bắt đầu đếm " . $item_provider['provider']);
            $response = $this->callApiMfb($item_provider['url'] . "&page=1");
            if ($response && isset($response->status) && $response->status == 200) {
                foreach ($response->data as $item_rs) {
                    if ($item_provider['provider'] == 'facebook' || $item_provider['provider'] == 'facebook1' || $item_provider['provider'] == 'facebook2') {
                        $log_buff = Facebook::where('orders_id', $item_rs->orders_id ?? $item_rs->id)->where('object_id', $item_rs->object_id)->where('status', 1)->first();
                        echo "\n";
                        if ($log_buff) {
                            echo_now($log_buff->id);
                            $log_buff->count_is_run = $item_rs->count_is_run;
                            $log_buff->start_like = $item_rs->start_like;
                            if ($log_buff->count_is_run >= $log_buff->quantity) {
                                $log_buff->status = 2;
                            }
                            if ($item_rs->is_refund == 1) {
                                $log_buff->status = -1;
                            }
                            $log_buff->save();
                            echo_now("Cập nhật thành công đơn " . ($item_rs->orders_id ?? $item_rs->id));
                        }
                    } elseif (in_array($item_provider['provider'], ['tiktok', 'tiktok1', 'tiktok2'])) {
                        $log_buff = TikTok::where('orders_id', $item_rs->id)->where('status', 1)->orderBy('id', 'desc')->first();
                        if ($log_buff) {
                            $log_buff->count_is_run = $item_rs->count_is_run;
                            if ($log_buff->count_is_run >= $log_buff->quantity) {
                                $log_buff->status = 2;
                            }
                            if ($item_rs->is_refund == 1) {
                                $log_buff->status = -1;
                            }
                            $log_buff->start_like = $item_rs->start_like;
                            $log_buff->save();
                            echo_now("Cập nhật thành công đơn " . ($item_rs->orders_id ?? $item_rs->id));
                        }
                    } else {
                        $log_buff = Instagram::where('orders_id', $item_rs->id)->whereIn('package_name', ['instagram_like_sv6', 'instagram_follow_sv10'])->where('status', 1)->first();
                        if ($log_buff) {
                            echo "\n";
                            echo $item_rs->package_name;
                            $log_buff->count_is_run = $item_rs->count_is_run;
                            if ($log_buff->count_is_run >= $log_buff->quantity) {
                                $log_buff->status = 2;
                            }
                            if ($item_rs->is_refund == 1) {
                                $log_buff->status = -1;
                            }
                            $log_buff->save();
                            echo_now("Cập nhật thành công đơn " . ($item_rs->orders_id ?? $item_rs->id));
                        }
                    }
                    echo_now("---------------------------------------------------------------");
                }
            } else {
                echo_now("Cập nhật api key");
            }

        }
    }

    public function callApiMfb($url)
    {
        $config = DB::table('config')->where('alias', 'key_mfb')->first();
        if ($config->value) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Connection: keep-alive",
                    "Pragma: no-cache",
                    "Cache-Control: no-cache",
                    "Accept: application/json, text/plain, */*",
                    "Authorization: Bearer " . $config->value,
                    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.106 Safari/537.36",
                    "Origin: http://hackfb.info",
                    "Sec-Fetch-Site: cross-site",
                    "Sec-Fetch-Mode: cors",
                    "Sec-Fetch-Dest: empty",
                    "Referer: http://hackfb.info/facebook-like",
                    "Accept-Language: en-US,en;q=0.9"
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            return json_decode($response);
        } else {
            return false;
        }
    }

    public function getItem($url)
    {
        $config = Config::where('alias', 'api_key_mfb')->first();
        if ($config->value) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Connection: keep-alive",
                    "Pragma: no-cache",
                    "Cache-Control: no-cache",
                    "Accept: application/json, text/plain, */*",
                    "Authorization: Bearer " . $config->value,
                    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.106 Safari/537.36",
                    "Origin: http://hackfb.info",
                    "Sec-Fetch-Site: cross-site",
                    "Sec-Fetch-Mode: cors",
                    "Sec-Fetch-Dest: empty",
                    "Referer: http://hackfb.info/facebook-like",
                    "Accept-Language: en-US,en;q=0.9"
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            return json_decode($response);
        } else {
            return false;
        }

    }

}

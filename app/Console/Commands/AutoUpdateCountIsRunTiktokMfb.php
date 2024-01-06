<?php

namespace App\Console\Commands;

use App\Models\Ads\TikTok\TikTok;
use App\Models\Config;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class AutoUpdateCountIsRunTiktokMfb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_count_is_run_tiktok';
    protected $limit = 500;

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
    public function handle()
    {
        exit();
        echo "
                  _       _                                _      _                         
  _   _ _ __   __| | __ _| |_ ___     ___ ___  _   _ _ __ | |_   (_)___    _ __ _   _ _ __  
 | | | | '_ \ / _` |/ _` | __/ _ \   / __/ _ \| | | | '_ \| __|  | / __|  | '__| | | | '_ \ 
 | |_| | |_) | (_| | (_| | ||  __/  | (_| (_) | |_| | | | | |_   | \__ \  | |  | |_| | | | |
  \__,_| .__/ \__,_|\__,_|\__\___|   \___\___/ \__,_|_| |_|\__|  |_|___/  |_|   \__,_|_| |_|
       |_|                                                                                  
\n";
        $keyCacheAutoUpdateCountIsRun = build_key_cache(['keyCacheAutoUpdateCountIsRunTiktok']);
        //$this->sendMessToBotTelegram("AUTO UPDATE COUNT IS RUN AGENCY LV2");
        if (Cache::has($keyCacheAutoUpdateCountIsRun)) {
            echo_now("Cron này đang được chạy vui lòng quay lại sau ít phút");
            exit();
        }
        Cache::remember($keyCacheAutoUpdateCountIsRun, 3600, function () {
            return true;
        });
        try {
            $array = [
//                'facebook' => [
//                    'url' => 'https://api.mfb.vn/api/advertising/list?limit=' . $this->limit,
//                    'provider' => 'facebook',
//                ],
                'instagram' => [
                    'url' => 'https://api.mfb.vn/api/advertising/tiktok/list?limit=' . $this->limit,
                    'provider' => 'instagram',
                ],
            ];
            echo_now("-----------------------------------------------");
            foreach ($array as $item_provider) {
                echo_now("Bắt đầu đếm " . $item_provider['provider']);
                $response = $this->getItem($item_provider['url']);
                if ($response && isset($response->status) && $response->status == 200) {
                    foreach ($response->data as $item_rs) {
                        $log_buff = TikTok::where('orders_id', $item_rs->orders_id ?? $item_rs->id)->first();
                        if ($log_buff) {
                            $log_buff->count_is_run = $item_rs->count_is_run;
                            $log_buff->start_like = $item_rs->start_like;
                            $log_buff->save();
                            echo_now("Cập nhật thành công đơn " . ($item_rs->orders_id ?? $item_rs->id));
                        }
                        echo_now("---------------------------------------------------------------");
                    }
                } else {
                    echo_now("Cập nhật api key");
                }
            }
            echo_now("--------------------Xong " . $item_provider['provider']);
        } catch (\Exception $exception) {
            Cache::forget($keyCacheAutoUpdateCountIsRun);
            echo $exception->getMessage();
        }
        Cache::forget($keyCacheAutoUpdateCountIsRun);
        exit();
    }

    public function getItem($url)
    {
        $config = Config::where('alias', 'key_mfb')->first();
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

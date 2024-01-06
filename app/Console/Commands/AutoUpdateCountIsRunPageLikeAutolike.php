<?php

namespace App\Console\Commands;

use App\Models\Ads\Facebook\Facebook;
use App\Models\Ads\TikTok\TikTok;
use App\Models\Config;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AutoUpdateCountIsRunPageLikeAutolike extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_count_is_run__page_like_autolike';
    protected $limit = 500;
    protected $package_name = ['facebook_like_page_sv2'];

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
        $keyCacheAutoUpdateCountIsRun = build_key_cache(['keyCacheAutoUpdateCountIsRunAutolike']);
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
                'facebook_page_like' => [
                    'url' => 'https://api.autolike.cc/public-api/v1/users/services/all',
                    'provider' => 'Fakebook Page Like',
                ],
            ];
            echo_now("-----------------------------------------------");
            foreach ($array as $item_provider) {
                $ads_page_like = DB::table('ads_facebook')->where('orders_id','<>','0')->where('status', 1)->where(function ($q) {
                    $q->whereNull('count_is_run')->orWhereColumn('count_is_run', '<', 'quantity');
                })->WhereIn('package_name', $this->package_name)->get();
//                echo "<pre>";
//                 print_r($value = 1);
//                echo "</pre>";
//                exit();
                if (!empty($ads_page_like)) {
                    foreach ($ads_page_like as $ads) {
                        $post_data = [
                            'service_code' => $ads->orders_id,

                        ];
                        $response = $this->getItem($item_provider['url'], $post_data);
                        if ($response && isset($response->code) && $response->code == 200 && isset($response->data->data)) {
                            $ads_autolike = $response->data->data;
                            if (isset($ads_autolike[0]) && $ads_autolike[0]->service_code == $ads->orders_id) {
                                $ads_to_save = Facebook::find($ads->id);
                                $ads_to_save->count_is_run = $ads_autolike[0]->number_success_int;
                                $ads_to_save->save();
                                echo_now("Cập nhật thành công đơn " . ($ads->orders_id));
                                echo_now("---------------------------------------------------------------");
                            }
                        }
                    }
                }
                echo_now("--------------------Xong " . $item_provider['provider']);
            }
        } catch (\Exception $exception) {
            Cache::forget($keyCacheAutoUpdateCountIsRun);
            echo $exception->getMessage();
        }
        Cache::forget($keyCacheAutoUpdateCountIsRun);
        exit();
    }

    public function getItem($url, $data = [])
    {
        $config = Config::where('alias', 'key_autolike')->first();
        if (isset($config->value)) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    'token: ' . $config->value,
                    'Content-Type: application/json',
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

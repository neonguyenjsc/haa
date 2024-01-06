<?php

namespace App\Console\Commands;

use App\Models\Ads\Facebook\Facebook;
use App\Models\Ads\Shopee\Shopee;
use Illuminate\Console\Command;

class UpdateCountIsRunAutofbPro extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_count_is_run_auto_fb_pro';

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
        $key_cahe = $this->signature;
        $key_cron = $key_cahe;
        if (startCron($key_cron)) {

            try {
                $list_url = [
                    'shopee' => 'https://autofb.pro/api/shopee?shopee_type=shopee&type=0&limit=0',
                    'shopee1' => 'https://autofb.pro/api/shopee?shopee_type=shopeetim&type=0&limit=0',
                    'facebook' => 'https://autofb.pro/api/facebook_buff/list/?type_api=buff_sub_sale&limit=0',
                    'facebook1' => 'https://autofb.pro/api/facebook_buff/list/?type_api=buffgroup&limit=0',
                ];
                foreach ($list_url as $provider => $url) {
                    $response = $this->callApiAutofbGetList($url);
                    if (isset($response->data)) {
                        foreach ($response->data as $item) {
                            try {
                                if (in_array($provider, ['shopee', 'shopee1'])) {
                                    $ads = Shopee::where('orders_id', $item->id)->first();
                                    if ($ads) {
                                        if (isset($item->follower_count)) {
                                            $ads->start_like = $item->follower_count;
                                        }
                                        if (isset($item->liked_count)) {
                                            $ads->start_like = $item->liked_count;
                                        }
                                        $ads->count_is_run = $item->dachay;
                                        $ads->save();
                                    }
                                }
                                if (in_array($provider, ['facebook', 'facebook1'])) {
                                    //facebook_follow_sv3
                                    //facebook_follow_sv11
                                    $ads = Facebook::where('orders_id', $item->id)->whereIn('package_name', [
                                        'facebook_follow_sv3',
                                        'facebook_follow_sv11',
                                        'facebook_mem_avatar',
                                        'facebook_mem_v2',
                                        'facebook_mem_avatar',
                                        'facebook_mem_no_avatar',
                                    ])->first();
                                    if ($ads) {
                                        if ($ads->menu_id == 47) {
                                            $ads->start_like = $item->start_like;
                                            $ads->count_is_run = $item->count_success;
                                            $ads->save();
                                        } else {
                                            if (isset($item->subscribers)) {
                                                $ads->start_like = $item->subscribers;
                                                $ads->count_is_run = $item->count_success;
                                                $ads->save();
                                            }
                                        }
                                    }
                                }
                            } catch (\Exception $exception) {
                                echo $exception->getMessage();
                                print_r($ads);
                                dd($item);
                            }
                        }
                    }
                }
            } catch (\Exception $exception) {
                echo $exception->getMessage() . "\n" . $exception->getLine();
            }
        }

        endCron($key_cron);
    }

    public function callApiAutofbGetList($url)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'authority: autofb.pro',
                'sec-ch-ua: " Not A;Brand";v="99", "Chromium";v="96", "Google Chrome";v="96"',
                'accept: application/json, text/plain, */*',
                'sec-ch-ua-mobile: ?0',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36',
                'ht-token: ' . getConfig('key_ctvsubvn'),
                'sec-ch-ua-platform: "Windows"',
                'sec-fetch-site: same-origin',
                'sec-fetch-mode: cors',
                'sec-fetch-dest: empty',
                'referer: https://autofb.pro/tool/shopee',
                'accept-language: en-US,en;q=0.9',
                'cookie: _ga=GA1.2.1846962226.1636820556; _gid=GA1.2.665812834.1640891340; _gat_gtag_UA_129870968_1=1; SL_GWPT_Show_Hide_tmp=1; SL_wptGlobTipTmp=1',
                'if-none-match: W/"18-io+YwShJfk2KaRC/1b0Huj4aGJ8"'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }
}

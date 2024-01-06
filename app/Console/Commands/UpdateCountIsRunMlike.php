<?php

namespace App\Console\Commands;

use App\Models\Ads\TikTok\TikTok;
use Illuminate\Console\Command;

class UpdateCountIsRunMlike extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_count_is_run_mlike';

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
//        dd(Facebook::whereNotIn('status', [3, 2, 4, 6, 7, 8])->where('orders_id', 14993)->whereIn('package_name', ['46_17', '46_19'])->first());
        $key_cahe = $this->signature . '_3';
        $key_cron = $this->signature;
        if (startCron($key_cron)) {
            try {
                $list = $this->getListTiktok();
                foreach ($list->data as $item) {
                    echo_now(json_encode($item));
                    $ads = TikTok::where('orders_id', $item->id_order)->whereIn('package_name', ['tiktok_like_v11', 'tiktok_view_s15'])->whereIn('status', [1, -1])->first();
                    if ($ads) {
                        echo_now("update id " . $ads->id);
                        $ads->start_like = intval($item->start);
                        $d = $item->done;
                        if ($item->done == "die") {
                            $d = 0;
                        }
                        $ads->count_is_run = $d;
                        if ($ads->count_is_run >= $ads->quantity) {
                            $ads->status = 3;
                        }
                        $ads->save();
                        echo_now($item->id);
                    }
                }
//            $list = $this->getList();
//            foreach ($list->data as $item) {
//                $ads = Facebook::whereNotIn('status', [3, 2, 4, 6, 7, 8])->where('orders_id', $item->id_order)->whereIn('package_name', ['46_17', '46_19'])->first();
//                if ($ads) {
//                    $ads->start_like = intval($item->start);
//                    $d = $item->done;
//                    if ($item->done == "die") {
//                        $d = 0;
//                    }
//                    $ads->count_is_run = $d;
//                    if ($ads->count_is_run >= $ads->quantity) {
//                        $ads->status = 3;
//                    }
//                    $ads->save();
//                    echo_now($item->id);
//                }
//            }
            } catch (\Exception $exception) {
                echo $exception->getMessage() . json_encode($item);
            }
        }
        endCron($key_cron);
        exit();
    }

    public function getList()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => DOMAIN_MLIKE . '/api/buy/facebook/view.php?act=history',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'token=' . getConfig('key_mlike_v2') . '&limit=10000',
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function getListTiktok()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => DOMAIN_MLIKE . '/api/buy/tiktok/like_v2.php?act=history',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'token=' . getConfig('key_mlike_v2') . '&limit=10000',
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
                'Cookie: PHPSESSID=7htc2ds0ei69doqagt5laip6te'
            ),
        ));

        $response = curl_exec($curl);
        echo_now($response);
        curl_close($curl);
        return json_decode($response);
    }
}

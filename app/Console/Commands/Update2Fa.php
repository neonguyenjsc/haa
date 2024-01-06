<?php

namespace App\Console\Commands;

use App\Models\Ads\Facebook\Facebook;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class Update2Fa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_2fa';

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
        set_time_limit(0);
        ini_set('memory_limit', '2048M');
        $key_cahe = $this->signature;
        if (Cache::has($key_cahe)) {
            exit();
        }
        Cache::remember($key_cahe, 3600, function () {
            return true;
        });
        try {
            $this->update2fa();
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
        Cache::forget($key_cahe);
    }

    public function update2fa()
    {
        $response = $this->callApi();
        foreach ($response->data->content as $item) {
            $log = Facebook::where('orders_id', $item->id)->where('package_name', 'facebook_follow_sv27')->first();
            if ($log) {
                $log->start_like = $item->alreadyHave;
//                if ($item->fulfillment == 1) {
//                    $log->count_is_run = $log->quantity;
//                    $log->status = 2;
//                }
                $log->save();
            }
        }
    }

    public function callApi()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://2fa.shop/api/buy-history?page=0&size=2000&fulfillment=',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'authority: 2fa.shop',
                'accept: application/json, text/plain, */*',
                'accept-language: vi,en-US;q=0.9,en;q=0.8',
                'cookie: SL_G_WPT_TO=en; SL_GWPT_Show_Hide_tmp=1; SL_wptGlobTipTmp=1; TK=eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJodXluaHF1b2NkYWkiLCJzY29wZXMiOiJbUk9MRV9VU0VSLCBST0xFX1dIT0xFU0FMRV0iLCJpYXQiOjE2NzE1MjQ1OTMsImV4cCI6MTY3NDExNjU5M30.8LttW7ABqxptKuETmnhDpL-qR3yHeRvuP9tIlSPXryyLI8m__RX60POeWN7FW732nOnpJYxgnWBZa0iGsM1H_w; JSESSIONID=483892CCB32E109238271BCB4352C856',
                'referer: https://2fa.shop/',
                'sec-ch-ua: "Not?A_Brand";v="8", "Chromium";v="108", "Google Chrome";v="108"',
                'sec-ch-ua-mobile: ?0',
                'sec-ch-ua-platform: "Windows"',
                'sec-fetch-dest: empty',
                'sec-fetch-mode: cors',
                'sec-fetch-site: same-origin',
                'tk: ' . getConfig('key_2fa'),
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }
}

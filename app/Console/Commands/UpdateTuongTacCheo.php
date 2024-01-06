<?php

namespace App\Console\Commands;

use App\Models\Ads\Facebook\Facebook;
use App\Models\Ads\TikTok\TikTok;
use App\Service\TuongTacCheoService\TuongTacCheoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class UpdateTuongTacCheo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_tuong_tac_cheo';

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
        set_time_limit(0);
        ini_set('memory_limit', '2048M');
        $key_cahe = $this->signature;
        if (Cache::has($key_cahe)) {
            exit();
        }
        $data_link = [
            'https://tuongtaccheo.com/tangcamxuc/fetch.php',
            'https://tuongtaccheo.com/tanglike/fetch.php',
            'https://tuongtaccheo.com/tangsub/fetch.php',
//
            'https://tuongtaccheo.com/tiktok/tanglike/fetch.php',
            'https://tuongtaccheo.com/tiktok/tangcmt/fetch.php',
            'https://tuongtaccheo.com/tiktok/tangsub/fetch.php',
        ];
        foreach ($data_link as $i => $link) {
            try {
                if (in_array($i, [0, 1, 2])) {
                    for ($i = 0; $i <= 10; $i++) {
                        $data = $this->callApi($link, $i);
                        foreach ($data as $item) {
                            echo_now(json_encode($item) . " => " . $i);
                            $log = Facebook::where('orders_id', ($item->maghinho ?? -1))->first();
                            if ($log) {
                                $quantity = $log->quantity; //20
                                $chenhlech = intval(abs($item->sldat - $quantity)); //120
                                $count_is_run = $item->dalen - $chenhlech;
                                if ($count_is_run < 0) {
                                    $count_is_run = 0;
                                }
                                $log->count_is_run = $count_is_run;
                                $log->save();
                            } else {
//                            echo_now(json_encode($item));
                            }
                        }
                        sleep(10);
                    }
                } else {
                    for ($i = 0; $i <= 5; $i++) {
                        $data = $this->callApi($link, $i);
                        foreach ($data as $item) {
                            $log = TikTok::where('orders_id', ($item->maghinho ?? -1))->first();
                            if ($log) {
                                $quantity = $log->quantity; //20
                                $chenhlech = $item->sldat - $quantity; //120
                                $count_is_run = $item->dalen - $chenhlech;
                                if ($count_is_run < 0) {
                                    $count_is_run = 0;
                                }
                                $log->count_is_run = $count_is_run;
                                $log->save();
                            }
                        }
                        sleep(60);
                    }
                }
            } catch (\Exception $exception) {
            }
        }

        Cache::forget($key_cahe);
    }

    public function callApi($link, $page)
    {
        $s = new TuongTacCheoService();
        $s->login();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $link,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'page=' . $page . '&id=',
            CURLOPT_HTTPHEADER => array(
                'authority: tuongtaccheo.com',
                'cache-control: max-age=0',
                'upgrade-insecure-requests: 1',
                'origin: https://tuongtaccheo.com',
                'content-type: application/x-www-form-urlencoded',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.71 Safari/537.36',
                'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'sec-fetch-site: same-origin',
                'sec-fetch-mode: navigate',
                'sec-fetch-user: ?1',
                'sec-fetch-dest: document',
                'referer: https://tuongtaccheo.com/index.php',
                'accept-language: vi,en-US;q=0.9,en;q=0.8',
                'cookie: _gid=GA1.2.886006062.1669650627; PHPSESSID=d3go1i49kr4d2q308jv6pfl2g5; _ga_6RNPVXD039=GS1.1.1669728573.2.1.1669728599.0.0.0; _ga=GA1.2.1000427561.1669396124'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);

    }
}

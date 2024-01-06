<?php

namespace App\Console\Commands;

use App\Models\Ads\Facebook\Facebook;
use App\Models\Ads\TikTok\TikTok;
use App\Service\TraoDoiSub\TraoDoiSubService;
use Illuminate\Console\Command;

class UpdateTDS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_tds';

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
        if (startCron($key_cahe)) {
            $data_url = [
                'https://traodoisub.com/mua/viewstr/fetch.php',
                'https://traodoisub.com/mua/follow/fetch.php',
                'https://traodoisub.com/mua/likegiare/fetch.php',
                'https://traodoisub.com/mua/tiktok_follow/fetch.php',
                'https://traodoisub.com/mua/tiktok_like/fetch.php',
                'https://traodoisub.com/mua/tiktok_comment/fetch.php',
//            'https://traodoisub.com/mua/viewstr/fetch.php',
            ];
            foreach ($data_url as $i_u => $url) {
                if (in_array($i_u, [0, 1, 2])) {
                    continue;
                    for ($i = 0; $i <= 5; $i++) {
                        $data = $this->callApi($i, $url);
                        if (isset($data->data)) {
                            foreach ($data->data as $item) {
//                        dd($item);
                                echo_now(json_encode($item) . " => " . $i);
                                $log = Facebook::where('orders_id', ($item->note ?? -1))->where('status', '<>', 2)->first();
                                if ($log) {
                                    $quantity = $log->quantity; //20
                                    $chenhlech = $item->sl - $quantity; //120
                                    $count_is_run = $item->datang - $chenhlech;
                                    if ($count_is_run < 0) {
                                        $count_is_run = 0;
                                    }
                                    $log->count_is_run = $count_is_run;
                                    if ($log->count_is_run >= $log->quantity) {
                                        $log->status = 2;
                                    }
//                            $log->start_like =
//                            $log->save();
                                }
                            }
//
                            sleep(15);
                        }
                    }
                } else {
                    for ($i = 1; $i <= 50; $i++) {
                        $data = $this->callApi($i, $url);
                        if (!isset($data->data)) {
                            sleep(30);
                            continue;
                        }
                        foreach ($data->data as $item) {
//                        echo_now(json_encode($item));
                            $log = TikTok::where('orders_id', ($item->note ?? -1))->where('status', '<>', 2)->first();
                            if ($log) {
                                $quantity = $log->quantity; //20
                                $chenhlech = $item->sl - $quantity; //120
                                $count_is_run = $item->datang - $chenhlech;
                                if ($count_is_run < 0) {
                                    $count_is_run = 0;
                                }
                                $log->count_is_run = $count_is_run;
                                if ($log->count_is_run >= $log->quantity) {
                                    $log->status = 2;
                                }
                                $log->start_like = $item->start ?? 0;
                                $log->save();
                                echo_now($log->id);
                            }
                        }
                        sleep(30);
                    }
                }
            }
        }
        endCron($key_cahe);
    }

    public
    function callApi($page, $url)
    {
        $s = new TraoDoiSubService();
        $s->login();

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
            CURLOPT_POSTFIELDS => 'page=' . $page . '&query=',
            CURLOPT_HTTPHEADER => array(
                'authority: traodoisub.com',
                'accept: */*',
                'accept-language: en-US,en;q=0.9',
                'content-type: application/x-www-form-urlencoded; charset=UTF-8',
                'cookie: ' . $s->cookie,
                'origin: https://traodoisub.com',
                'referer: https://traodoisub.com/mua/viewstr/',
                'sec-ch-ua: "Chromium";v="110", "Not A(Brand";v="24", "Microsoft Edge";v="110"',
                'sec-ch-ua-mobile: ?0',
                'sec-ch-ua-platform: "Windows"',
                'sec-fetch-dest: empty',
                'sec-fetch-mode: cors',
                'sec-fetch-site: same-origin',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36 Edg/110.0.1587.50',
                'x-requested-with: XMLHttpRequest'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo_now($response);
        return json_decode($response);

    }
}

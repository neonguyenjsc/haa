<?php

namespace App\Console\Commands;

use App\Models\Ads\Facebook\Facebook;
use App\Models\Ads\Instagram\Instagram;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

//use App\Models\Facebook\FacebookLikeComment;
//use App\Models\V2\Ads\Instagram;

class UpdateCountIsRunTLC extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_count_is_run_tlc {type}';

    /**facebook_follow_sv24
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
        $page = $this->argument('type');
        $key_cahe = $this->signature . "_$page";
        //update_count_is_run_tlc
//        if (Cache::has($key_cahe)) {
//            exit();
//        }
//        Cache::remember($key_cahe, 3600 * 2, function () {
//            return true;
//        });

        $key_cron = 'update_count_is_run_tlc';
        if (startCron($key_cron)) {
            try {
                $this->tangLikeCheo();
            } catch (\Exception $exception) {
            }
        }

        endCron($key_cron);
        exit();
    }

    public function tangLikeCheo()
    {
        $model = new Facebook();
        $tlc = $model->tlc;
        $id = 4200000;
        do {
            for ($i = 0; $i <= 10; $i++) {
//            $facebook = Facebook::whereIn('package_name', $tlc)->where('status', 1)->orderBy('id', 'desc')->skip(5000 * $i)->take(5000)->pluck('orders_id')->toArray();
                $facebook = Facebook::where('id', '>', $id)->whereIn('package_name', $tlc)->where('status', 1)->skip($i * 5000)->take(5000)->orderBy('id', 'desc')->pluck('orders_id')->toArray();
                if (count($facebook) < 1) {
                    continue;
                }
                echo_now(count($facebook));
                $list = implode(",", $facebook);
                echo_now("page " . $i);
                $url = 'https://agency.tanglikecheo.com/api/history';
                $response = $this->callApiTLC($url, ['id' => $list, 'provider' => 'facebook', 'limit' => 5000]);
                if (isset($response->data)) {
//                    echo_now(count($response->data));
                    foreach ($response->data as $item) {
                        $log = Facebook::where('orders_id', $item->_id)->first();
                        if ($log) {
                            if ($log->status == 2 || $log->status == 3) {
                                continue;
                            }
                            echo_now($log->id);
                            if ($log) {
                                $log->count_is_run = $item->count_is_run;
                                if ($item->is_remove && $item->is_refund) {
                                    $log->status = 0;
                                }
                                if ($log->count_is_run >= $log->quantity) {
                                    $log->status = 2;
                                }
                                if ($item->is_hidden) {
                                    $log->status = -1;
                                }
                                $log->save();
                            } else {
//                        $t = new TelegramService();
//                        $t->sendToTelegramDebugCheckOrder([
//                            'Không tìm thấy đơn này facebook',
//                            'Id' => $item->_id
//                        ]);
                            }
                        }

                    }
                }

            }
            //ig
            $instagram = Instagram::where('id', '>', 130000)->whereIn('package_name', $tlc)->where('status', 1)->orderBy('id', 'desc')->skip(5000 * $i)->take(5000)->pluck('orders_id')->toArray();
            $list = implode(",", $instagram);
            echo_now("page " . $i);
            $url = 'https://agency.tanglikecheo.com/api/history';
            $response = $this->callApiTLC($url, ['id' => $list, 'provider' => 'instagram', 'limit' => 5000]);
            if (isset($response->data)) {
                echo_now(count($response->data));
                foreach ($response->data as $item) {
                    if (!$item->is_refund && (($item->quantity < 100) && ($item->quantity > $item->count_is_run))) {
                        continue;
                    }
                    $log = Instagram::where('orders_id', $item->_id)->first();
                    if ($log) {
                        if ($log->status == 2 || $log->status == 3) {
                            continue;
                        }
                        echo_now($log->id);
                        if ($log) {
                            $log->count_is_run = $item->count_is_run;
                            if ($item->is_remove && $item->is_refund) {
                                $log->status = 0;
                            }
                            if ($log->count_is_run >= $log->quantity) {
                                $log->status = 2;
                            }
                            if ($item->is_hidden) {
                                $log->status = -1;
                            }
                            $log->save();
                        } else {
//                        $t = new TelegramService();
//                        $t->sendToTelegramDebugCheckOrder([
//                            'Không tìm thấy đơn này facebook',
//                            'Id' => $item->_id
//                        ]);
                        }
                    }
                }
            }

        } while (1);


//        $page = $this->argument('type');
//        if ($page == '1') {
//            for ($i = 1; $i <= 10; $i++) {
//                $url = 'https://agency.tanglikecheo.com/api/history?limit=999&page=' . $i;
//                $data = $this->callApiTLC($url);
//
//            }
//        } else {
//            $url = 'https://agency.tanglikecheo.com/api/history?limit=5000&provider=instagram';
//            $data = $this->callApiTLC($url);
//            foreach ($data->data as $item) {
//                $log = Instagram::where('orders_id', $item->_id)->first();
//                if ($log) {
//                    if (in_array($log->status, [2, 4])) {
//                        continue;
//                    }
//                    $log->count_is_run = $item->count_is_run;
//                    $log->start_like = $item->start;
//                    if ($item->is_remove && $item->is_refund) {
//                        $log->status = 0;
//                    }
//                    if ($log->count_is_run >= $log->quantity) {
//                        $log->status = 2;
//                    }
//                    if ($item->is_hidden) {
//                        $log->status = -1;
//                    }
//                    $log->save();
//                } else {
////                    $t = new TelegramService();
////                    $t->sendToTelegramDebugCheckOrder([
////                        'Không tìm thấy đơn này instagram',
////                        'Id' => $item->_id
////                    ]);
//                }
//            }
//        }

    }

    public function callApiTLC($url, $data)
    {
        $token = DB::table('config')->where('alias', 'key_tanglikecheo')->first();
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
                'Authorization: Bearer ' . $token->value,
                't: ' . time(),
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }
}

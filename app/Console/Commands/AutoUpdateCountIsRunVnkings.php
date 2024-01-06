<?php

namespace App\Console\Commands;

use App\Models\Ads\Facebook\Facebook;
use App\Models\Ads\TikTok\TikTok;
use App\Models\Ads\Youtube\Youtube;
use App\Models\Config;
use App\Models\Prices;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AutoUpdateCountIsRunVnkings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_count_is_run_vnkings';
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
        $keyCacheAutoUpdateCountIsRun = build_key_cache(['keyCacheAutoUpdateCountIsRunVnkings']);
        //$this->sendMessToBotTelegram("AUTO UPDATE COUNT IS RUN AGENCY LV2");
        if (Cache::has($keyCacheAutoUpdateCountIsRun)) {
            echo_now("Cron này đang được chạy vui lòng quay lại sau ít phút");
            exit();
        }
        Cache::remember($keyCacheAutoUpdateCountIsRun, 3600, function () {
            return true;
        });
        $status = [
            'in_progress' => 'Đang xử lý',
            'pending' => 'Đang chờ',
            'completed' => 'Đã hoàn thành',
            'processing' => 'Đã phân tích',
            'canceled' => 'Đã hủy',
        ];

        $package_name = Prices::where('description','vnkings')->get()->pluck('package_name')->toArray();
        if(isset($package_name[0])){
            try {
                $order_ids =  Youtube::whereIn('package_name', $package_name)->where('status',1)->orderBy('created_at', 'DESC')
                ->pluck('orders_id')->toArray();
                if(isset($order_ids[1])){
                    $orders = Youtube::whereIn('orders_id', $order_ids)->orderBy('created_at', 'DESC')->get()->keyBy('orders_id');
                    $order_ids = array_chunk($order_ids,100);
                    foreach ($order_ids as $index=>$order_id_chunk){
                        $order_id_list = implode(',',$order_id_chunk);
                        $rs = $this->getItem($order_id_list);
                        if (!empty($rs)) {
                            foreach ($rs as $order_id => $order) {
                                if (isset($rs->$order_id->status)) {
                                    $ads = Youtube::where('orders_id',$order_id)->first();
                                    if($ads){
                                        $ads->status_source = $status[Str::slug($order->status, '_')] ?? $order->status;
                                        $ads->count_is_run = $orders[$order_id]['quantity'] - $order->remains;
                                        if($ads->count_is_run < 0){
                                            $ads->count_is_run = 0;
                                        }
                                        $ads->save();
//                                        if($ads->orders_id == 59272){
//                                            print_r($ads);
//                                            exit();
//                                        }
                                        echo_now("Cập nhật thành công đơn " . ($order_id));
                                        echo_now("---------------------------------------------------------------");
                                    }
                                }
                            }
                        }
                    }
                }
                echo_now("--------------------Xong ");
            } catch (\Exception $exception) {
                Cache::forget($keyCacheAutoUpdateCountIsRun);
                echo $exception->getMessage();
            }
            Cache::forget($keyCacheAutoUpdateCountIsRun);
        }
        exit();
    }

    public function getItem($order_ids)
    {
        $url = 'https://vnkings.net/api/v2';
        $config = DB::table('config')->where('alias', 'key_vnkings')->first();
        if (isset($config->value)) {
            $curl = curl_init();
            $data['key'] = $config->value ?? '';
            $data['action'] = 'status';
            $data['orders'] = $order_ids;
            $header = array(
                "content-type:application/json"
            );


            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => $header,
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                return false;
            } else {
                return json_decode($response);
            }
        } else {
            return false;
        }

    }
}

<?php

namespace App\Console\Commands;

use App\Http\Controllers\Traits\Lib;
use App\Models\Ads\Facebook\Facebook;
use App\Models\Logs;
use App\Models\Refund;
use App\Service\FarmApi\FarmService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class UpdateCountIsRunLike8810990 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_count_is_run_like88_10990 {page}';

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
    use Lib;

    public function handle()
    {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');
//        $key_cahe = $this->signature . $this->argument('page');
//        if (Cache::has($key_cahe)) {
//            exit();
//        }
//        $this->sendMessGroupCardToBotTelegram($key_cahe);
//        Cache::remember($key_cahe, 3600 * 2, function () {
//            return true;
//        });
        $key_cron = 'update_count_is_run_like88_10990';
        if (startCron($key_cron)) {
            try {
                $this->like88();
            } catch (\Exception $exception) {
            }
//            do {
//
//            } while (0);
        }
        endCron($key_cron);
        exit();
    }

    public function like88()
    {
        $farmService = new FarmService();
        for ($i = 0; $i <= 100; $i++) {
            $model = new Facebook();
            $ads = $model::where('user_id', 10990)->where('id', '>', 4200000)->whereIn('package_name', $model->farm)->whereIn('status', [1, -1])->orderBy('id', 'desc')->skip(50 * $i)->take(50)->pluck('orders_id')->toArray();
            $list_id = implode(",", $ads);
            $response = $this->callApi($list_id);
            foreach ($response->result->orders as $item) {
//                if (($item->status != 'pause') && (($item->quantity < 500 && $item->quantity > $item->job_success))) {
//                    echo_now("skip đơn " . $item->order_id);
//                    continue;
//                }
                try {
                    $ads = Cache::remember('farm_' . $item->order_id, 60 * 60 * 24 * 14, function () use ($item, $model) {
                        return Facebook::where('orders_id', $item->order_id)->whereIn('package_name', $model->farm)->first();
                    });
                } catch (\Exception $e) {
                }
                if ($ads) {
                    echo_now("update đơn " . $ads->id . "|" . $ads->quantity);
                    try {
                        $start_like = 0;
                        if ($ads->menu_id == 45) {//follow
                            $ads->start_like = str_replace(".", "", $item->initial_interaction->profile_follow ?? 0);
                        }
                        if ($ads->menu_id == 41) {//like
                            $ads->start_like = str_replace(".", "", $item->initial_interaction->like ?? 0);
                        }
                        if ($ads->menu_id == 46) { // 46 page
                            $ads->start_like = str_replace(".", "", $item->initial_interaction->page_like ?? 0);
                        }
                        if ($ads->menu_id == 47) { // 46 page
                            $ads->start_like = str_replace(".", "", $item->initial_interaction->members_group ?? 0);
                        }
                        $ads->count_is_run = $item->job_success ?? 0;
                        $ads->start_like = intval($ads->start_like);
                        if ($item->status == 'wait_warranty') {
                            $ads->warranty = 1;
                        }
                        if ($ads->count_is_run >= $ads->quantity) {
                            $ads->status = 2;
                        }
                        if ($item->status == 'pause') {
                            $ads->status = -1;
                            $response = $farmService->actionOrder(['orders_id' => $ads->orders_id, 'action' => 'remove']);
                            if ($response && $response->status == 200) {
                                $check = Refund::where('orders_id', $ads->id)->where('table', 'facebook')->first();
                                if (!$check) {
                                    $ads->status = 0;
                                    $ads->save();
                                    Logs::newLogs([
                                        'user_id' => $ads->user_id,
                                        'username' => $ads->username,
                                        'client_id' => null,
                                        'client_username' => null,
                                        'action' => 'remove',
                                        'action_coin' => 'out',
                                        'type' => 'out',
                                        'description' => 'Hủy đơn thành công ' . $ads->server . ' cho ' . $ads->object_id,
                                        'coin' => 0,
                                        'old_coin' => 0,
                                        'new_coin' => 0,
                                        'price_id' => $ads->price_id,
                                        'object_id' => $ads->object_id,
                                        'post_data' => 'cron',
                                        'result' => json_encode($response ?? []),
                                        'ip' => 'local',
                                        'package_name' => $ads->package_name ?? '',
                                        'orders_id' => $ads->id ?? 0,
                                    ]);
                                    try {
                                        Refund::newRefund([
                                            'user_id' => $ads->user_id,
                                            'username' => $ads->username,
                                            'client_id' => $ads->client_id,
                                            'client_username' => $ads->client_username,
                                            'object_id' => $ads->object_id,
                                            'coin' => 0,
                                            'quantity' => 0,
                                            'price_per_agency' => $ads->price_per_agency,
                                            'prices_agency' => $ads->prices_agency,
                                            'description' => 'Đang xử lý',
                                            'status' => -1,
                                            'category_id' => 1,
                                            'tool_name' => $ads->server,
                                            'package_name' => $ads->package_name,
                                            'server' => $ads->server,
                                            'vat' => 0,
                                            'user_id_agency_lv2' => $ads->user_id_agency_lv2,
                                            'prices_agency_lv2' => $ads->prices_agency_lv2,
                                            'price_per_agency_lv2' => $ads->price_per_agency_lv2,
                                            'price_per_remove' => 0,
                                            'orders_id' => $ads->id,
                                            'table' => 'facebook',
                                        ]);
                                    } catch (\Exception $exception) {
                                        dd($exception->getMessage());

                                    }
                                }

                            }
                        }
                        $ads->save();
                    } catch (\Exception $exception) {
                    }
                }
            }
        }

    }

    public function callApi($id)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://farm-api.giau.xyz/seller/order/list',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('token' => getConfig('key_farm'), 'order_id' => $id),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }
}

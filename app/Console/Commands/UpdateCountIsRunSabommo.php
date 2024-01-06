<?php

namespace App\Console\Commands;

use App\Models\Ads\Facebook\Facebook;
use App\Models\Refund;
use App\Service\SaBomMoService;
use Illuminate\Console\Command;

class UpdateCountIsRunSabommo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_count_is_run_sabomo {page}';

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
        echo_now(date('Y-m-d H:i:s'));
        $key_cahe = $this->signature . $this->argument('page');
        $key_cron = 'update_count_is_run_sabomo';
        if (startCron($key_cron)) {
            try {
                $this->sabommo();
            } catch (\Exception $exception) {

            }
        }
        endCron($key_cron);
        exit();
    }

    public function sabommo()
    {
        $page = $this->argument('page');
        $limit = 500;
        if ($page == 1) {
            $facebook = new Facebook();
            do {
                for ($i = 0; $i < 10; $i++) {
                    echo $i . " ";
                    foreach ($facebook->sabommo as $package_name) {
                        $logs = Facebook::where('id', '>', 3794454)->whereIn('status', [1, -1])->where('package_name', $package_name)->where('orders_id', '<>', 0)->skip(100 * $i)->take(100)->orderBy('id', 'desc')->pluck('orders_id')->toArray();
                        $list_id = implode(",", $logs);
                        $responses = $this->callSabommo('process-orders-buff', $list_id);
                        if (!isset($responses->data)) {
                            continue;
                        }
                        foreach ($responses->data as $response) {
                            $item = Facebook::where('orders_id', $response->id)->where('package_name', $package_name)->orderBy('id', 'desc')->first();
                            if ($item) {
                                echo $item->id . " bt sabommo \n";
                                $item->count_is_run = $response->seeding_num;
                                $item->start_like = $response->start_num;
                                if ($response->status != 'RUNNING') {
                                    $item->status = 0;
                                }
                                if ($response->status != 'AUTO_PAUSED') {
                                    $item->status = -1;

                                }
                                if ($response->status == 'RUNNING') {
                                    $item->status = 1;
                                }

                                if ($item->count_is_run >= $item->quantity) {
                                    $item->status = 2;
                                    $item->warranty = 1;
                                }
                                if ($response->status == 'REFUND') {
                                    if (!Refund::where('orders_id', $item->id)->where('table', 'facebook')->first()) {
                                        try {
                                            Refund::newRefund([
                                                'user_id' => $item->user_id,
                                                'username' => $item->username,
                                                'client_id' => $item->client_id,
                                                'client_username' => $item->client_username,
                                                'object_id' => $item->object_id,
                                                'coin' => 0,
                                                'quantity' => 0,
                                                'price_per_agency' => $item->price_per_agency,
                                                'prices_agency' => $item->prices_agency,
                                                'description' => 'Đang xử lý',
                                                'status' => -1,
                                                'category_id' => 1,
                                                'tool_name' => $item->server,
                                                'package_name' => $item->package_name,
                                                'server' => $item->server,
                                                'vat' => 0,
                                                'user_id_agency_lv2' => $item->user_id_agency_lv2,
                                                'prices_agency_lv2' => $item->prices_agency_lv2,
                                                'price_per_agency_lv2' => $item->price_per_agency_lv2,
                                                'price_per_remove' => 0,
                                                'orders_id' => $item->id,
                                                'table' => 'facebook',
                                            ]);
                                        } catch (\Exception $exception) {

                                        }
                                    }

                                }
                                $item->save();
                            }
                        }
                    }

                }
            } while (1);

        } else {

//
//            $array = [
//                'tiktok_comment_sv3',
//                'tiktok_follow_sv9',
//            ];
//            foreach ($array as $item_a) {
//                $logs = TikTok::where('package_name', $item_a)->whereIn('status', [1])->where('orders_id', '<>', null)->take(1000)->orderBy('id', 'desc')->pluck('orders_id')->toArray();
//                echo_now(count($logs));
//                $list_id = implode(",", $logs);
//                $responses = $this->callSabommo('process-orders-buff', $list_id);
//                foreach ($responses->data as $response) {
//                    $item = TikTok::where('orders_id', $response->id)->where('package_name', $item_a)->first();
//                    echo_now($item->id);
//                    if ($item) {
//                        $item->count_is_run = $response->seeding_num;
//                        $item->start_like = $response->start_num;
//                        if ($response->status != 'RUNNING') {
//                            $item->status = 0;
//                        }
//                        if ($response->status != 'AUTO_PAUSED') {
//                            $item->status = -1;
//
//                        }
//                        if ($response->status == 'RUNNING') {
//                            $item->status = 1;
//                        }
//                        if ($item->count_is_run >= $item->quantity) {
//                            $item->status = 2;
//                            $item->warranty = 1;
//                        }
//                        $item->save();
//                    }
//
//                }
//
//            }
        }
    }

    public function callSabommo($a, $id)
    {
        $service = new SaBomMoService\SaBomMoService();
        $form_data = [
            'action' => $a,
            'id' => $id,
        ];
        $url = DOMAIN_SA_BOM_MO . '/api/index.php';
        return $service->checkOrderV2($id);
    }
}

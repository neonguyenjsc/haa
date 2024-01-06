<?php

namespace App\Console\Commands;

use App\Http\Controllers\Traits\Lib;
use App\Models\Ads\Facebook\Facebook;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class UpdateCountIsRunTrumVn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_count_is_run_trum_vn';

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
    use Lib;

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
        Cache::remember($key_cahe, 3600 * 2, function () {
            return true;
        });
        try {
            $this->trumVn();
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
        Cache::forget($key_cahe);
    }

    public function trumVn()
    {
        $response = $this->callApi();
        $data = array_reverse($response->data, 1);
        foreach ($data as $item) {
            $ads = Facebook::where('orders_id', $item->order_id)->first();
            if ($ads) {
                if ($item->status == "stop") {
                    $ads->status = -1;
                }
//                $ads->start_like = $item->count_start;
                $ads->count_is_run = $item->count_success;
                $ads->save();
            }
        }
    }

    public function callApi()
    {
//        $proxy = $this->getProxy();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://trum.vn/api/services/subspeed/list',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('key' => getConfig('key_trumvb')),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Ads\Facebook\Facebook;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class UpdateOrderBaostar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_count_is_run_baostar';

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
        if (Cache::has($key_cahe)) {
            exit();
        }
        Cache::remember($key_cahe, 3600 * 2, function () {
            return true;
        });
        $list = $this->getList();
        $facebook = new Facebook();
        foreach ($list->data as $item) {
            $ads = $facebook->where('orders_id', $item->id)->whereIn('package_name', $facebook->baostar)->first();
            if ($ads) {
                echo_now($ads->id);
                $ads->count_is_run = $item->count_is_run;
                if ($item->status == 0) {
                    $ads->status = -1;
                }
                if ($ads->count_is_run == $ads->quantity) {
                    $ads->status = 2;
                }
                $ads->save();
            }
        }
        Cache::forget($key_cahe);
    }

    public function getList()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => DOMAIN_BAOSTAR_TOOL . '/api/list',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }
}

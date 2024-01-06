<?php

namespace App\Console\Commands;

use App\Models\Ads\Facebook\Facebook;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class UpdateCountIsRunVNFB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_count_is_run_vnfb';

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
        $key_cahe = $this->signature;
        if (Cache::has($key_cahe)) {
            exit();
        }
        Cache::remember($key_cahe, 3600 * 2, function () {
            return true;
        });
        try {
            for ($i = 0; $i < 50; $i++) {
                $list = Facebook::whereIn('package_name', ['facebook_follow_sv16', 'facebook_follow_sv17'])->orderBy('id', 'desc')->take(50)->skip($i * 50)->get();
                $list_id = implode(",", $list->pluck('orders_id')->toArray());
                $response = $this->callApi($list_id);

                if (isset($response->data)) {
                    foreach ($response->data as $item) {
                        $ads = $list->where('orders_id', $item->id)->first();
                        $ads->count_is_run = $item->run_count;
                        $ads->start_like = $item->first_count;
                        $ads->save();
                    }
                }
            }
        } catch (\Exception $exception) {

            echo $exception->getMessage();
        }
        Cache::forget($key_cahe);
    }

    public function callApi($id)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://vietnamfb.com/api?mc=covid_sub&site=get_info_order&username=huynhquocdai&api_key=db32823315469f689bfb6dbbc83b7d0fa6fa8066a529dfa4edd2b63dd2e93df6a0a69be8560a2a262ff39179f2be23bbef4b97c86fccac6486cd671823ae5937",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "ids=" . $id,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/x-www-form-urlencoded"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }
}

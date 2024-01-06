<?php

namespace App\Console\Commands;

use App\Models\Ads\Facebook\Facebook;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class UpdateCountIsRunSbook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_count_is_run_sbook';

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
            $this->sbook();
        } catch (\Exception $exception) {

            echo $exception->getMessage();
        }
        Cache::forget($key_cahe);
    }


    public function sbook()
    {
        $z = [
            'https://sbooks.me/api/get_like/?user=19HKyNLcz8PP2cqfl5iX&start=0&limit=100',
            'https://sbooks.me/api/get_sub/?user=19HKyNLcz8PP2cqfl5iX&start=0&limit=100',
        ];
        //Follow s3 via [ Chất lượng số 1 t
        foreach ($z as $url) {
            $data = $this->callSbook($url);
            foreach ($data as $item) {
//                $uid = Facebook::where('orders_id', $item->id)->whereIn('package_name', ['facebook_follow_sv2', 'facebook_like_page_sv4'])->orderBy('id', 'DESC')->first();
                $uid = Facebook::where('object_id', $item->uid)->whereIn('package_name', ['facebook_follow_sv2', 'facebook_like_page_sv4'])->orderBy('id', 'DESC')->first();
                if ($uid) {
                    echo $uid->id . "\n";
//                    if (in_array($uid->status, [2, 4, 7])) {
//                        continue;
//                    }
//                    if ($item->status == 'destroy') {
//                        $uid->status = 6;
//                    }
                    $uid->count_is_run = $item->da_tang;
                    $uid->start_like = $item->subscribers;

                    $uid->save();
                }
            }
        }
    }

    public function callSbook($url)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Cookie: PHPSESSID=64bf7e87ac4ef43d6de52967f0dc8ddb'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }
}

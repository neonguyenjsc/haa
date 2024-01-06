<?php

namespace App\Console\Commands;

use App\Models\Ads\Facebook\Facebook;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class UpdateOrderThanhLike extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_thanhlike';

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
        $list = Facebook::whereIn('package_name', ['facebook_follow_sv16', 'facebook_follow_sv7'])->take(500)->orderBy('id', 'desc')->get();
        foreach ($list as $item) {
            $response = $this->callApi($item->orders_id);
            $item->start_like = $response->data[0]->totalStart ?? 0;
            $item->count_is_run = $response->data[0]->totalRun ?? 0;
            $item->save();
        }
        Cache::forget($key_cahe);
    }

    public function callApi($id)
    {
        sleep(5);
        $dataPost = array(
            "token" => getConfig('key_thanh_like'), //token từ hệ thống
            "orderCode" => $id
        );
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => DOMAIN_THANHLIKE . "/api/list",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($dataPost),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "accept: application/json")
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
}

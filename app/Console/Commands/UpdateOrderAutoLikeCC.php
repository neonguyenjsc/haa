<?php

namespace App\Console\Commands;

use App\Http\Controllers\Traits\Lib;
use App\Models\Config;
use App\Models\Ads\Facebook\Facebook;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class UpdateOrderAutoLikeCC extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    use Lib;
    protected $signature = 'update_order_v2_autolike_cc';

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
        Cache::remember($key_cahe, 3600 * 2, function () {
            return true;
        });
        try {
            $this->autocc();
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
        Cache::forget($key_cahe);
    }

    public function autocc()
    {
        $logs = Facebook::whereIn('package_name', [
            'facebook_follow_sv4',
            'facebook_follow_sv5',
            'facebook_follow_sv6',
            'facebook_follow_sv7',
            'facebook_follow_sv8',
            'facebook_follow_sv9',
            'facebook_follow_sv10',
            'facebook_follow_sv13',
            'facebook_follow_sv14',
            'facebook_follow_sv15',
            'facebook_like_v2',
            'facebook_like_v11',
            'facebook_like_v8',
            'facebook_like_page_sv2',
            'facebook_like_page_sv2',
            'facebook_like_page_sv5',
            'facebook_like_page_sv6',
            'facebook_like_page_sv7',
            'facebook_like_page_sv8',
            'facebook_like_page_sv8_',
            'facebook_like_page_sv9',
        ])->orderBy('id', 'DESC')->take(1000)->get();
//        $this->sendError500ToTelegram("có " . count($logs) . " autolike cc");
        foreach ($logs as $item) {
            $response = $this->callAutoLikeCC1(['service_codes' => [$item->orders_id]]);
            if (isset($response->data[0]->number_success_int)) {
                $item->count_is_run = $response->data[0]->number_success_int;
            }
            if (isset($response->data[0]->follows_start)) {
                $item->start_like = $response->data[0]->follows_start;
            }
//            echo $item->id . " => " . $response->data[0]->number_success_int . "\n";
//            if ($response->data[0]->status != 'Active') {
//                $item->status = 6;
//            }
//            if ($response->data[0]->status == 'Report') {
//                $item->status = 5;
//            }
//            if ($response->data[0]->status == 'Active') {
//                $item->status = 1;
//            }
//            if ($item->count_is_run >= $item->quantity) {
//                $item->status = 3;
//            }
            $item->save();
        }
    }

    public function callAutoLikeCC1($data)
    {
        $config_token = Config::where('alias', 'key_autolike_v2')->first();
        $data_config_token = json_decode($config_token->value);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.autolike.cc/public-api/v1/agency/services/all-by-codes',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'authority: api-autolike.congaubeo.us',
                'accept: application/json',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36',
                'token: ' . $data_config_token->token ?? '',
                'agency-secret-key: ' . $data_config_token->agency_secret_key ?? '',
                'content-type: application/json',
                'origin: https://www.mottrieu.com',
                'sec-fetch-site: cross-site',
                'sec-fetch-mode: cors',
                'sec-fetch-dest: empty',
                'referer: https://www.mottrieu.com/',
                'accept-language: vi',
                'Cookie: __cfduid=d6f82b0ecbbd5fcdc3d82712dfa53082e1609122016'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
}

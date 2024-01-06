<?php

namespace App\Console\Commands;

use App\Http\Controllers\Traits\Lib;
use App\Models\Ads\Facebook\Facebook;
use App\Models\Logs;
use App\Models\Prices;
use App\Service\Shop2Fa\Shop2FaService;
use App\Service\VietNamFb\VietNamFbService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CountQuantityPhamTuan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'count_quantity';

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
        exit();
        $shop2FaService = new  Shop2FaService();
        $shop2FaService->login2Fa();
        $id = 398445;
//        $this->callCuongOcCho();
        $now = Carbon::now();
        $monthStartDate = date('2021-11-01 00:00:00');
        $monthEndDate = date('Y-m-d H:i:s');
        $date = [
            $monthStartDate,
            $monthEndDate,
        ];
        $total = Facebook::whereIn('price_id', [
            46,
        ])->whereBetween('created_at', $date)->get();
        $this->sendMessGroupCardToBotTelegram("Thống kê FRAMER từ ngày $monthStartDate đến hiện tại \n Tổng => " . number_format($total->sum('prices')) . "\n Số lượng" . (number_format($total->sum('quantity'))));

        $time_mem = [
            date('2021-10-01 12:40:00'),
            date('2021-11-01 14:52:00'),
        ];
        $count_men_no_avatar = number_format(Facebook::where('package_name', 'facebook_mem_no_avatar')->whereBetween('created_at', $time_mem)->sum('quantity'));
        $count_facebook_mem_v2 = number_format(Facebook::where('package_name', 'facebook_mem_v2')->whereBetween('created_at', $time_mem)->sum('quantity'));
        $count_facebook_mem_v3 = number_format(Facebook::where('package_name', 'facebook_mem_v3')->whereBetween('created_at', $time_mem)->sum('quantity'));

        $this->sendMessGroupCardToBotTelegram("Thống kê MEM => " . implode(" đến ", $time_mem) . "
        S1 => $count_men_no_avatar
        S2 => $count_facebook_mem_v2
        S3 => $count_facebook_mem_v3
        ");

//        $this->sendMessGroupCardToBotTelegram("Thống kê mem từ ngày 16-4-2021 đến hiện tại \n Mem không avatar => $count_men_no_avatar \n Men avatar => $count_men_avatar");
//        $this->sendMessGroupCardToBotTelegram("Thống kê FOLLOW => " . number_format($follow_sv12));

        $follow_sv6 = Facebook::where('package_name', 'facebook_follow_sv5')->where('price_per', 6)->whereBetween('created_at', $date)->get();
        $q = $follow_sv6->sum('quantity');
        $str = "6đ => " . number_format($q) . " => " . number_format($q * 6) . "\n";

        $follow_sv6 = Facebook::where('price_per', 6.5)->whereBetween('created_at', $date)->get();
        $q = $follow_sv6->sum('quantity');
        $str = $str . "6.5đ => " . number_format($q) . " => " . number_format($q * 6.5) . "\n";

        $follow_sv6 = Facebook::where('price_per', 6.8)->whereBetween('created_at', $date)->get();
        $q = $follow_sv6->sum('quantity');
        $str = $str . "6.8đ => " . number_format($q) . " => " . number_format($q * 6.8) . "\n";

        $follow_sv6 = Facebook::where('package_name', 'facebook_follow_sv5')->where('price_per', 7)->whereBetween('created_at', $date)->get();
        $q = $follow_sv6->sum('quantity');
        $str = $str . "7đ => " . number_format($q) . " => " . number_format($q * 7) . "\n";

        $follow_sv6 = Facebook::where('package_name', 'facebook_follow_sv5')->where('price_per', 8)->whereBetween('created_at', $date)->get();
        $q = $follow_sv6->sum('quantity');
        $str = $str . "8đ => " . number_format($q) . " => " . number_format($q * 8) . "\n";

        $this->sendMessGroupCardToBotTelegram("Thống kê FOLLOW => \n" . $str);

        $vnfb = new VietNamFbService();
        $post_data = [
            'id' => '4',
            'amount' => '10',
            'channel_number' => 1,
            'buff_speed' => false,
            'buff_speed_second' => 5,
            'reaction' => 'LIKE',
            'force_buff' => 0,
        ];
        $vnfb->buy($post_data, 'https://vietnamfb.com/?mc=covid_sub&site=add_new');
        exit();
    }

    public function getPricePer($package_name)
    {
        switch ($package_name) {
            case 'facebook_comment_sv3';
                return 2000;
                break;
            case 'facebook_comment_sv4';
                return 3000;
                break;
            case 'facebook_comment_sv5';
                return 7500;
                break;
            case 'facebook_comment_sv6';
                return 15000;
                break;
            case 'facebook_comment_sv7';
                return 30000;
                break;
            case 'facebook_comment_sv8';
                return 45000;
                break;
        }
    }

    public function curlAdminAmaiTeam()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://admin.amaiteam.com/home?draw=1&columns%255B0%255D%255Bdata%255D=id&columns%255B0%255D%255Bname%255D=id&columns%255B0%255D%255Bsearchable%255D=true&columns%255B0%255D%255Borderable%255D=false&columns%255B0%255D%255Bsearch%255D%255Bvalue%255D=&columns%255B0%255D%255Bsearch%255D%255Bregex%255D=false&columns%255B1%255D%255Bdata%255D=post_id&columns%255B1%255D%255Bname%255D=post_id&columns%255B1%255D%255Bsearchable%255D=true&columns%255B1%255D%255Borderable%255D=false&columns%255B1%255D%255Bsearch%255D%255Bvalue%255D=&columns%255B1%255D%255Bsearch%255D%255Bregex%255D=false&columns%255B2%255D%255Bdata%255D=expire_date&columns%255B2%255D%255Bname%255D=expire_date&columns%255B2%255D%255Bsearchable%255D=true&columns%255B2%255D%255Borderable%255D=false&columns%255B2%255D%255Bsearch%255D%255Bvalue%255D=&columns%255B2%255D%255Bsearch%255D%255Bregex%255D=false&columns%255B3%255D%255Bdata%255D=seeding_type&columns%255B3%255D%255Bname%255D=seeding_type&columns%255B3%255D%255Bsearchable%255D=true&columns%255B3%255D%255Borderable%255D=false&columns%255B3%255D%255Bsearch%255D%255Bvalue%255D=&columns%255B3%255D%255Bsearch%255D%255Bregex%255D=false&columns%255B4%255D%255Bdata%255D=num_seeding_need&columns%255B4%255D%255Bname%255D=num_seeding_need&columns%255B4%255D%255Bsearchable%255D=true&columns%255B4%255D%255Borderable%255D=false&columns%255B4%255D%255Bsearch%255D%255Bvalue%255D=&columns%255B4%255D%255Bsearch%255D%255Bregex%255D=false&columns%255B5%255D%255Bdata%255D=num_seeding_gain&columns%255B5%255D%255Bname%255D=num_seeding_gain&columns%255B5%255D%255Bsearchable%255D=true&columns%255B5%255D%255Borderable%255D=false&columns%255B5%255D%255Bsearch%255D%255Bvalue%255D=&columns%255B5%255D%255Bsearch%255D%255Bregex%255D=false&columns%255B6%255D%255Bdata%255D=need_gain&columns%255B6%255D%255Bname%255D=need_gain&columns%255B6%255D%255Bsearchable%255D=true&columns%255B6%255D%255Borderable%255D=false&columns%255B6%255D%255Bsearch%255D%255Bvalue%255D=&columns%255B6%255D%255Bsearch%255D%255Bregex%255D=false&columns%255B7%255D%255Bdata%255D=priority&columns%255B7%255D%255Bname%255D=priority&columns%255B7%255D%255Bsearchable%255D=false&columns%255B7%255D%255Borderable%255D=false&columns%255B7%255D%255Bsearch%255D%255Bvalue%255D=&columns%255B7%255D%255Bsearch%255D%255Bregex%255D=false&columns%255B8%255D%255Bdata%255D=action&columns%255B8%255D%255Bname%255D=action&columns%255B8%255D%255Bsearchable%255D=false&columns%255B8%255D%255Borderable%255D=false&columns%255B8%255D%255Bsearch%255D%255Bvalue%255D=&columns%255B8%255D%255Bsearch%255D%255Bregex%255D=false&start=0&length=10&search%255Bvalue%255D=&search%255Bregex%255D=false&day=3&_=1622515644505',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 1,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'authority: admin.amaiteam.com',
                'sec-ch-ua: " Not A;Brand";v="99", "Chromium";v="90", "Google Chrome";v="90"',
                'accept: application/json, text/javascript, */*; q=0.01',
                'x-requested-with: XMLHttpRequest',
                'sec-ch-ua-mobile: ?0',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.212 Safari/537.36',
                'sec-fetch-site: same-origin',
                'sec-fetch-mode: cors',
                'sec-fetch-dest: empty',
                'referer: https://admin.amaiteam.com/home',
                'accept-language: en-US,en;q=0.9',
                'cookie: SL_GWPT_Show_Hide_tmp=1; SL_wptGlobTipTmp=1; XSRF-TOKEN=eyJpdiI6IlpFdndLR1gzYkRBUFNIWkNPNnJXbmc9PSIsInZhbHVlIjoiSjZqdlFhOFROem82YThkcHdwa2Rqc21rT3NHVVFGRzNkMXk5WUgyRzNzaDIyYjNoaVo2MUNxNHFPRzY0cE5XTCIsIm1hYyI6ImI3MGI5NjUxODg0ZTNhYTdkMThlNWM0ODBlNGJkZmVmNjBjOGE3ZTJmYzQwOTk5MTRjZWU0Njk0OTliMjNmYTMifQ%3D%3D; amai_session=eyJpdiI6Iis5cGhZRGc0bnVtMTdXN0c4Sjhjemc9PSIsInZhbHVlIjoiQXorcmxUcm9NZHFnZngxWitpdWFtUE1JZmRNcWpxa0t0d3JjazVPUUhLWUR1RmZNaHRIeU1YQzh0dUR6M3hJayIsIm1hYyI6IjliNDdiMWZiOTI4Yzc2YWE3NmMwNjA0OTllM2U5NTU4NDczYzc3MmZjZTYyOGM2YzNlYzJmM2ExNDY4OGU4NTQifQ%3D%3D'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
    }

    public function callCuongOcCho()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://hovancuong.xyz/mo-mo',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'authority: hovancuong.xyz',
                'cache-control: max-age=0',
                'sec-ch-ua: " Not;A Brand";v="99", "Google Chrome";v="91", "Chromium";v="91"',
                'sec-ch-ua-mobile: ?0',
                'upgrade-insecure-requests: 1',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'sec-fetch-site: same-origin',
                'sec-fetch-mode: navigate',
                'sec-fetch-user: ?1',
                'sec-fetch-dest: document',
                'referer: https://hovancuong.xyz/',
                'accept-language: en-US,en;q=0.9',
                'cookie: PHPSESSID=260d682eb8c7c3ea9222e25ce737427e'
            ),
        ));
    }
}

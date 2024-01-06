<?php

namespace App\Console\Commands;

use App\Models\Ads\Facebook\Facebook;
use App\Service\SubReVn\SubReVnService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class UpdateCountIsRunSubGiare extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_count_is_run_subgiare';

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
//            $url = DOMAIN_SUBGIARE . '/api/service/facebook/sub-vip/list';
//            $subgiare = new SubReVnService();
//            $list = Facebook::whereIn('package_name', ['facebook_follow_sv17'])->take(1000)->orderBy('id', 'desc')->pluck('orders_id')->toArray();
//            $response = $subgiare->callApiSubGiaRe($url, ['code_orders' => implode(",", $list)]);
//            if (isset($response->data)) {
//                foreach ($response->data as $item) {
//                    $ads = Facebook::whereIn('package_name', ['facebook_follow_sv17'])->where('orders_id', $item->code_order)->first();
//                    $ads->start_like = $item->start;
//                    if ($item->status == 'Success') {
//                        $ads->count_is_run = $ads->quantity;
//                        $ads->status = 1;
//                    }
//                    $ads->save();
//                }
//            }

            $url = DOMAIN_SUBGIARE . '/api/service/facebook/like-page-quality/list';
            $subgiare = new SubReVnService();
            $list = Facebook::where('id', '>', 1856720)->whereIn('package_name', ['facebook_like_page_sv13'])->take(1000)->orderBy('id', 'desc')->pluck('orders_id')->toArray();

            $response = $subgiare->callApiSubGiaRe($url, ['code_orders' => implode(",", $list)]);
            if (isset($response->data)) {
                foreach ($response->data as $item) {
                    $ads = Facebook::whereIn('package_name', ['facebook_like_page_sv13'])->where('orders_id', $item->code_order)->first();
                    $ads->start_like = $item->start;
                    if ($item->status == 'Success') {
                        $ads->count_is_run = $item->buff;
                        $ads->status = 1;
                    }
                    $ads->save();
                }
            }

            $url = DOMAIN_SUBGIARE . '/api/service/facebook/sub-quality/list';
            $subgiare = new SubReVnService();
            $list = Facebook::where('id', '>', 1856720)->whereIn('package_name', ['facebook_follow_sv3', 'facebook_follow_sv17'])->take(1000)->orderBy('id', 'desc')->pluck('orders_id')->toArray();
            $response = $subgiare->callApiSubGiaRe($url, ['code_orders' => implode(",", $list)]);
            if (isset($response->data)) {
                foreach ($response->data as $item) {
                    $ads = Facebook::whereIn('package_name', ['facebook_follow_sv3', 'facebook_follow_sv17'])->where('orders_id', $item->code_order)->first();
                    $ads->start_like = $item->start;
                    if ($item->status == 'Success') {
                        $ads->count_is_run = $item->buff;
                        $ads->status = 1;
                    }
                    $ads->save();
                }
            }

            $url = DOMAIN_SUBGIARE . '/api/service/facebook/sub-sale/list';
            $subgiare = new SubReVnService();
            $list = Facebook::where('id', '>', 1856720)->whereIn('package_name', ['facebook_follow_sv26'])->take(1000)->orderBy('id', 'desc')->pluck('orders_id')->toArray();
            $response = $subgiare->callApiSubGiaRe($url, ['code_orders' => implode(",", $list)]);
            if (isset($response->data)) {
                foreach ($response->data as $item) {
                    $ads = Facebook::whereIn('package_name', ['facebook_follow_sv3', 'facebook_follow_sv17'])->where('orders_id', $item->code_order)->first();
                    $ads->start_like = $item->start;
                    if ($item->status == 'Success') {
                        $ads->count_is_run = $item->buff;
                        $ads->status = 1;
                    }
                    $ads->save();
                }
            }
//
            $url = DOMAIN_SUBGIARE . '/api/service/facebook/member-group/list';
            $subgiare = new SubReVnService();
            $list = Facebook::whereIn('package_name', ['facebook_mem_v6', 'facebook_mem_v9'])->take(500)->orderBy('id', 'desc')->pluck('orders_id')->toArray();
            $response = $subgiare->callApiSubGiaRe($url, ['code_orders' => implode(",", $list)]);
            if (isset($response->data)) {
                foreach ($response->data as $item) {
                    $ads = Facebook::whereIn('package_name', ['facebook_mem_v6', 'facebook_mem_v9'])->where('orders_id', $item->code_order)->first();
                    $ads->start_like = $item->start;
                    if ($item->status == 'Success') {
                        $ads->count_is_run = $ads->quantity;
                        $ads->status = 1;
                    }
                    $ads->save();
                }
            }
        } catch (\Exception $exception) {
            dd($exception);
            Cache::forget($key_cahe);
        }

    }
}

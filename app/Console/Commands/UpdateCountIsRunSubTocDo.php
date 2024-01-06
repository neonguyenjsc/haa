<?php

namespace App\Console\Commands;

use App\Models\Ads\Facebook\Facebook;
use App\Service\TrumSub\TrumSubService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class UpdateCountIsRunSubTocDo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_count_is_run_subtocdo';

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
            $this->SubToDo();
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
        Cache::forget($key_cahe);
    }

    public function SubToDo()
    {
        $take = 500;
        for ($i = 0; $i <= 2; $i++) {
            $list = Facebook::where('id', '>', 1169003)->whereIn('package_name', ['facebook_follow_sv12'])->skip($take * $i)->take($take)->pluck('orders_id')->toArray();
            $list = implode(",", $list);
            $trumsub = new TrumSubService();
            $url = 'https://subtocdo.net/check-like-sub-views-share-eye-checkin/v2';
            $data = ['list' => $list];
            $data = $trumsub->callGetList($url, $data);
            foreach ($data as $item) {
                $ads = Facebook::where('orders_id', $item->codeoder)->first();
                if ($ads) {
                    $ads->count_is_run = $item->dachay;
                    $ads->save();
                }
            }
            sleep(5 * 60);
        }
    }
}

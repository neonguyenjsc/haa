<?php

namespace App\Console\Commands;

use App\Models\Ads\Facebook\Facebook;
use App\Models\Logs;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class AutoRefundOrderUser10990 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto_refund_user_10990';

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
            $this->action();
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
        Cache::forget($key_cahe);
    }

    public function action()
    {
        $ads = Facebook::where('user_id', 10990)->where('status', -1)->get();
        $user = User::find(10990);
        foreach ($ads as $log) {
            $log->status = 0;
            $log->save();
            if (Refund::where('orders_id', $log->id)->where('table', 'facebook')->first()) {
                continue;
            } else {
                $prices_remove = $log->price_per_remove;
                Logs::newLogs([
                    'user_id' => $log->user_id,
                    'username' => $log->username,
                    'client_id' => 0,
                    'client_username' => null,
                    'action' => 'remove',
                    'action_coin' => 'out',
                    'type' => 'out',
                    'description' => 'Hủy đơn tự động thành công ' . $log->server . ' cho ' . $log->object_id,
                    'coin' => 0,
                    'old_coin' => $user->coin,
                    'new_coin' => $user->coin - 0,
                    'price_id' => $log->price_id,
                    'object_id' => $log->object_id,
                    'post_data' => json_encode([]) . "\n" . json_encode($user),
                    'result' => json_encode($response ?? []),
                    'ip' => '',
                    'package_name' => $log->package_name ?? '',
                    'orders_id' => $log->id ?? 0,
                ]);
                try {
                    Refund::newRefund([
                        'user_id' => $log->user_id,
                        'username' => $log->username,
                        'client_id' => $log->client_id,
                        'client_username' => $log->client_username,
                        'object_id' => $log->object_id,
                        'coin' => 0,
                        'quantity' => 0,
                        'price_per_agency' => $log->price_per_agency,
                        'prices_agency' => $log->prices_agency,
                        'description' => 'Đang xử lý',
                        'status' => -1,
                        'category_id' => 1,
                        'tool_name' => $log->server,
                        'package_name' => $log->package_name,
                        'server' => $log->server,
                        'vat' => 0,
                        'user_id_agency_lv2' => $log->user_id_agency_lv2,
                        'prices_agency_lv2' => $log->prices_agency_lv2,
                        'price_per_agency_lv2' => $log->price_per_agency_lv2,
                        'price_per_remove' => $prices_remove,
                        'orders_id' => $log->id,
                        'table' => 'facebook',
                    ]);
                } catch (\Exception $exception) {

                }
            }
        }
    }
}

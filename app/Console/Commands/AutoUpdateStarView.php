<?php

namespace App\Console\Commands;

use App\Http\Controllers\Traits\Lib;
use App\Models\Ads\Instagram\Instagram;
use App\Models\Ads\Telegram\PostView;
use App\Models\Ads\TikTok\TikTok;
use App\Models\Ads\Youtube\Youtube;
use App\Models\Config;
use App\Models\Refund;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class AutoUpdateStarView extends Command
{
    use Lib;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_start_view';

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
//        $this->sendError500ToTelegram("Bắt đầu udate view yt");
        $key_cahe = $this->signature;
        if (Cache::has($key_cahe)) {
            exit();
        }
        Cache::remember($key_cahe, 3600 * 2, function () {
            return true;
        });
        try {
            $this->viewYt();
        } catch (\Exception $exception) {
            echo $exception->getMessage() . "=>" . $exception->getLine();
        }
        Cache::forget($key_cahe);
    }

    public function viewYt()
    {

        $list = PostView::where('orders_id', '>', 0)->orderBy('id', 'DESC')->take(15000)->pluck('orders_id')->toArray();
        $list = implode(",", $list);
        $data = [
            'orders' => $list,
            'action' => 'status',
        ];
        $response = $this->callApiViewYT($data);
        if ($response) {
            foreach ($response as $i => $res) {
                $item = PostView::where('orders_id', $i)->orderBy('id', 'desc')->first();
                if ($item->status && in_array($item->status, [2, 4])) {
                    continue;
                }
                echo_now(json_encode($res));
                if ($item && !isset($res->error)) {
                    $item->start_like = $res->start_count ?? 0;
                    $conlai = $res->remains;
                    $item->count_is_run = $count_is_run = $item->quantity - ($conlai);
                    echo_now("update telegram " . $item->id);
                    if ($res->status == 'Canceled' && $item->status != 0 && !in_array($item->package_name, ["tiktok_view_s6"])) {
                        if (!Refund::where('orders_id', $item->id)->where('table', 'telegram')->first()) {
                            $item->status = 0;
                            $item->save();
                            $log = $item;
                            $q = $log->quantity - $count_is_run;
                            $check_out_coin = ($q * $log->price_per);
                            Refund::newRefund([
                                'user_id' => $log->user_id,
                                'username' => $log->username,
                                'client_id' => $log->client_id,
                                'client_username' => $log->client_username,
                                'object_id' => $log->object_id,
                                'coin' => $check_out_coin,
                                'quantity' => $count_is_run,
                                'price_per_agency' => $log->price_per_agency,
                                'prices_agency' => $log->prices_agency,
                                'description' => "Hệ thống hoàn tiền cho bạn " . number_format($check_out_coin) . " tương ứng " . number_format($q) . " lượt tương tác cho uid " . $item->object_id . " và " . $item->price_per_remove . ' phí dịch vụ',
                                'status' => 0,
                                'category_id' => 1,
                                'tool_name' => $log->server,
                                'package_name' => $log->package_name,
                                'server' => $log->server,
                                'vat' => 0,
                                'user_id_agency_lv2' => $log->user_id_agency_lv2,
                                'prices_agency_lv2' => $log->prices_agency_lv2,
                                'price_per_agency_lv2' => $log->price_per_agency_lv2,
                                'price_per_remove' => 0,
                                'orders_id' => $log->id,
                                'table' => 'telegram',
                                'quantity_buy' => $log->quantity,
                                'username_agency_lv2' => $log->username_agency_lv2,
                                'price_per' => $log->price_per,
                                'response' => json_encode($res) . "|" . $i,
                            ]);
                        }

                    }
                    $item->save();
                }
            }
        }
//        exit();

        $list = TikTok::where('status', '<>', 0)->take(15000)->orderBy('id', 'DESC')->pluck('orders_id')->toArray();
        $list = implode(",", $list);
        $data = [
            'orders' => $list,
            'action' => 'status',
        ];
        $response = $this->callApiViewYT($data);
        if ($response) {
            foreach ($response as $i => $res) {
                $item = TikTok::where('orders_id', $i)->orderBy('id', 'desc')->first();
                if ($item && !isset($res->error)) {
                    if (in_array($item->status, [2, 4])) {
                        continue;
                    }
                    $item->start_like = $res->start_count ?? 0;
                    $conlai = $res->remains;
                    $item->count_is_run = $count_is_run = $item->quantity - ($conlai);
                    if ($res->status == 'Canceled' && $item->status != 0 && !in_array($item->package_name, ["tiktok_view_s6"])) {
                        if (!Refund::where('orders_id', $item->id)->where('table', 'tiktok')->first()) {
                            $item->status = 0;
                            $item->save();
                            $log = $item;
                            $q = $log->quantity - $count_is_run;
                            $check_out_coin = ($q * $log->price_per);
                            Refund::newRefund([
                                'user_id' => $log->user_id,
                                'username' => $log->username,
                                'client_id' => $log->client_id,
                                'client_username' => $log->client_username,
                                'object_id' => $log->object_id,
                                'coin' => $check_out_coin,
                                'quantity' => $count_is_run,
                                'price_per_agency' => $log->price_per_agency,
                                'prices_agency' => $log->prices_agency,
                                'description' => "Hệ thống hoàn tiền cho bạn " . number_format($check_out_coin) . " tương ứng " . number_format($q) . " lượt tương tác cho uid " . $item->object_id . " và " . $item->price_per_remove . ' phí dịch vụ',
                                'status' => 0,
                                'category_id' => 1,
                                'tool_name' => $log->server,
                                'package_name' => $log->package_name,
                                'server' => $log->server,
                                'vat' => 0,
                                'user_id_agency_lv2' => $log->user_id_agency_lv2,
                                'prices_agency_lv2' => $log->prices_agency_lv2,
                                'price_per_agency_lv2' => $log->price_per_agency_lv2,
                                'price_per_remove' => 0,
                                'orders_id' => $log->id,
                                'table' => 'tiktok',
                                'quantity_buy' => $log->quantity,
                                'username_agency_lv2' => $log->username_agency_lv2,
                                'price_per' => $log->price_per,
                                'response' => json_encode($res) . "|" . $i,
                            ]);
                        }

                    }
                    $item->save();
                }
            }
        }

        $list = Youtube::whereIn('package_name', ['youtube_view_normal', 'youtube_time_view_4k'])->where('status', '<>', 0)->orderBy('id', 'DESC')->pluck('orders_id')->take(500)->toArray();
        $list = implode(",", $list);
        $data = [
            'orders' => $list,
            'action' => 'status',
        ];
        $response = $this->callApiViewYT($data);
        if ($response) {
            foreach ($response as $i => $res) {
                $item = Youtube::where('orders_id', $i)->first();

                if ($item) {
                    $item->start_like = $res->start_count;
                    if (in_array($item->status, [2, 4])) {
                        continue;
                    }
                    $item->count_is_run = $count_is_run = $item->quantity - $res->remains;
                    if ($res->status == 'Canceled' && $item->status == 0) {
                        if (!Refund::where('orders_id', $item->id)->where('table', 'youtube')->first()) {
                            $item->status = 0;
                            $item->save();
                            $log = $item;
                            $q = $log->quantity - $count_is_run;
                            $check_out_coin = ($q * $log->price_per);
                            Refund::newRefund([
                                'user_id' => $log->user_id,
                                'username' => $log->username,
                                'client_id' => $log->client_id,
                                'client_username' => $log->client_username,
                                'object_id' => $log->object_id,
                                'coin' => $check_out_coin,
                                'quantity' => $count_is_run,
                                'price_per_agency' => $log->price_per_agency,
                                'prices_agency' => $log->prices_agency,
                                'description' => "Hệ thống hoàn tiền cho bạn " . number_format($check_out_coin) . " tương ứng " . number_format($q) . " lượt tương tác cho uid " . $item->object_id . " và " . $item->price_per_remove . ' phí dịch vụ',
                                'status' => 0,
                                'category_id' => 1,
                                'tool_name' => $log->server,
                                'package_name' => $log->package_name,
                                'server' => $log->server,
                                'vat' => 0,
                                'user_id_agency_lv2' => $log->user_id_agency_lv2,
                                'prices_agency_lv2' => $log->prices_agency_lv2,
                                'price_per_agency_lv2' => $log->price_per_agency_lv2,
                                'price_per_remove' => 0,
                                'orders_id' => $log->id,
                                'table' => 'youtube',
                                'quantity_buy' => $log->quantity,
                                'price_per' => $log->price_per,
                                'username_agency_lv2' => $log->username_agency_lv2,
                                'response' => json_encode($res) . "|" . $i,
                            ]);
                        }

                    }
                    $item->save();
                }
            }
        }

        $ig = new Instagram();
        $list = $ig::where('id', 137718)->whereIn('package_name', $ig->viewyt)->where('status', '<>', 0)->orderBy('id', 'DESC')->pluck('orders_id')->take(500)->toArray();
        $list = implode(",", $list);
        $data = [
            'orders' => $list,
            'action' => 'status',
        ];
        $response = $this->callApiViewYT($data);
        if ($response) {
            foreach ($response as $i => $res) {
                $item = Instagram::where('orders_id', $i)->orderBy('id', 'desc')->first();
                if ($item) {
                    if (in_array($item->status, [2, 4])) {
                        continue;
                    }
                    echo_now("update đơn instagram " . $item->id);
                    $item->start_like = $res->start_count ?? 0;
                    $item->count_is_run = $count_is_run = $item->quantity - $res->remains;
                    if ($res->status == 'Canceled' && $item->status == 0) {
                        if (!Refund::where('orders_id', $item->id)->where('table', 'instagram')->first()) {
                            $item->status = 0;
                            $item->save();
                            $log = $item;
                            $q = $log->quantity - $count_is_run;
                            $check_out_coin = ($q * $log->price_per);
                            Refund::newRefund([
                                'user_id' => $log->user_id,
                                'username' => $log->username,
                                'client_id' => $log->client_id,
                                'client_username' => $log->client_username,
                                'object_id' => $log->object_id,
                                'coin' => $check_out_coin,
                                'quantity' => $count_is_run,
                                'price_per_agency' => $log->price_per_agency,
                                'prices_agency' => $log->prices_agency,
                                'description' => "Hệ thống hoàn tiền cho bạn " . number_format($check_out_coin) . " tương ứng " . number_format($q) . " lượt tương tác cho uid " . $item->object_id . " và " . $item->price_per_remove . ' phí dịch vụ',
                                'status' => 0,
                                'category_id' => 1,
                                'tool_name' => $log->server,
                                'package_name' => $log->package_name,
                                'server' => $log->server,
                                'vat' => 0,
                                'user_id_agency_lv2' => $log->user_id_agency_lv2,
                                'prices_agency_lv2' => $log->prices_agency_lv2,
                                'price_per_agency_lv2' => $log->price_per_agency_lv2,
                                'price_per_remove' => 0,
                                'orders_id' => $log->id,
                                'table' => 'instagram',
                                'quantity_buy' => $log->quantity,
                                'username_agency_lv2' => $log->username_agency_lv2,
                                'price_per' => $log->price_per,
                                'response' => json_encode($res) . "|" . $i,
                            ]);
                        }

                    }
                    $item->save();
                }
            }
        }

//        $list = TikTok::where('orders_id', '>', 0)->where('status', '<>', 0)->orderBy('id', 'DESC')->pluck('orders_id')->take(500)->toArray();
//        $list = implode(",", $list);
//        $data = [
//            'orders' => $list,
//            'action' => 'status',
//        ];
//        $response = $this->callApiViewYT($data);
//        if ($response) {
//            foreach ($response as $i => $res) {
//                $item = TikTok::where('orders_id', $i)->orderBy('id', 'desc')->first();
//                if (in_array($item->status, [2, 4])) {
//                    continue;
//                }
//                if ($item && isset($res->start_count)) {
//                    $item->start_like = $res->start_count;
//                    $item->count_is_run = $count_is_run = $item->quantity - $res->remains;
//                    if ($res->status == 'Canceled' && $item->status != 0 && !in_array($item->package_name, ["tiktok_view_s6"])) {
//                        $item->status = 0;
//                        $item->save();
//                        $log = $item;
//                        $q = $log->quantity - $count_is_run;
//                        $check_out_coin = ($q * $log->price_per);
//                        Refund::newRefund([
//                            'user_id' => $log->user_id,
//                            'username' => $log->username,
//                            'client_id' => $log->client_id,
//                            'client_username' => $log->client_username,
//                            'object_id' => $log->object_id,
//                            'coin' => $check_out_coin,
//                            'quantity' => $count_is_run,
//                            'price_per_agency' => $log->price_per_agency,
//                            'prices_agency' => $log->prices_agency,
//                            'description' => "Hệ thống hoàn tiền cho bạn " . number_format($check_out_coin) . " tương ứng " . number_format($q) . " lượt tương tác cho uid " . $item->object_id . " và " . $item->price_per_remove . ' phí dịch vụ',
//                            'status' => 0,
//                            'category_id' => 1,
//                            'tool_name' => $log->server,
//                            'package_name' => $log->package_name,
//                            'server' => $log->server,
//                            'vat' => 0,
//                            'user_id_agency_lv2' => $log->user_id_agency_lv2,
//                            'prices_agency_lv2' => $log->prices_agency_lv2,
//                            'price_per_agency_lv2' => $log->price_per_agency_lv2,
//                            'price_per_remove' => 0,
//                            'orders_id' => $log->id,
//                            'table' => 'youtube',
//                            'quantity_buy' => $log->quantity,
//                            'username_agency_lv2' => $log->username_agency_lv2,
//                            'price_per' => $log->price_per,
//                            'response' => json_encode($res) . "|" . $i,
//                        ]);
//                    }
//                    $item->save();
//                }
//            }
//        }
//
//
//        $list = Telegram\PostView::where('orders_id', '>', 0)->where('status', '<>', 0)->orderBy('id', 'DESC')->pluck('orders_id')->take(500)->toArray();
//        $list = implode(",", $list);
//        $data = [
//            'orders' => $list,
//            'action' => 'status',
//        ];
//        $response = $this->callApiViewYT($data);
//        if ($response) {
//            foreach ($response as $i => $res) {
//                $item = Telegram\PostView::where('orders_id', $i)->first();
//                if (in_array($item->status, [2, 4])) {
//                    continue;
//                }
//                if ($item && isset($res->start_count)) {
//                    $item->start_like = $res->start_count;
//                    $item->count_is_run = $count_is_run = $item->quantity - $res->remains;
//                    if ($res->status == 'Canceled' && $item->status == 0) {
//                        $item->status = 0;
//                        $item->save();
//                        $log = $item;
//                        $q = $log->quantity - $count_is_run;
//                        $check_out_coin = ($q * $log->price_per);
//                        Refund::newRefund([
//                            'user_id' => $log->user_id,
//                            'username' => $log->username,
//                            'client_id' => $log->client_id,
//                            'client_username' => $log->client_username,
//                            'object_id' => $log->object_id,
//                            'coin' => $check_out_coin,
//                            'quantity' => $count_is_run,
//                            'price_per_agency' => $log->price_per_agency,
//                            'prices_agency' => $log->prices_agency,
//                            'description' => "Hệ thống hoàn tiền cho bạn " . number_format($check_out_coin) . " tương ứng " . number_format($q) . " lượt tương tác cho uid " . $item->object_id . " và " . $item->price_per_remove . ' phí dịch vụ',
//                            'status' => 0,
//                            'category_id' => 1,
//                            'tool_name' => $log->server,
//                            'package_name' => $log->package_name,
//                            'server' => $log->server,
//                            'vat' => 0,
//                            'user_id_agency_lv2' => $log->user_id_agency_lv2,
//                            'prices_agency_lv2' => $log->prices_agency_lv2,
//                            'price_per_agency_lv2' => $log->price_per_agency_lv2,
//                            'price_per_remove' => 0,
//                            'orders_id' => $log->id,
//                            'table' => 'youtube',
//                            'quantity_buy' => $log->quantity,
//                            'username_agency_lv2' => $log->username_agency_lv2,
//                            'price_per' => $log->price_per,
//                            'response' => json_encode($res) . "|" . $i,
//                        ]);
//                    }
//                    $item->save();
//                }
//            }
//        }
    }

    public function callApiViewYT($data)
    {
        sleep(1);
        $key = Config::where('alias', 'key_viewyt')->first();
        $data['key'] = $key->value ?? '';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://viewyt.com/api/v2",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
}

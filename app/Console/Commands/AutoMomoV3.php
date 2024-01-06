<?php

namespace App\Console\Commands;

use App\Http\Controllers\Traits\Lib;
use App\Models\Logs;
use App\Models\PaymentAutoMomo;
use App\Models\PaymentMonth;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class AutoMomoV3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto_momo_v3';

    /**
     * The console command description.
     *
     * @var string
     */
    use Lib;
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
     * web 2m
     * @return int
     */
    public function handle()
    {
        $key_cahe = $this->signature;
        if (Cache::has($key_cahe)) {
            echo_now("đang chay");
            exit();
        }
        Cache::remember($key_cahe, 60 * 5, function () {
            return true;
        });
        $cu_phap = "chucmung username";
        $list = $this->callApi();
        $data = $list->momoMsg->tranList ?? [];
        foreach ($data as $i => $item) {
            try {
                if ($item->io != 1) {
                    continue;
                }
//                if ($item->clientTime <= 1667438354189) {
//                    continue;
//                }
                $i = $i + 1;
                $item->trans_id = $item->tranId;
                echo_now($item->trans_id);
                $item->syntax = $item->comment ?? '';
                $item->date = $item->finishTime ?? '';
                $item->phone = $item->user ?? '';
                $item->response = json_encode($item);
                if (!PaymentAutoMomo::where('trans_id', $item->trans_id)->first()) {

                    $item->status = 0;
                    $item->description = 'Đang check';
                    $payment = PaymentAutoMomo::newPayment((array)$item);
                    $username = str_replace(str_replace("username", "", $cu_phap), "", strtolower($item->syntax));
                    $user = User::where('username', $username)->first();
                    if ($user) {
                        $coin = intval($payment->amount);
//                    $coin = 0;
                        if ($coin && $coin > 0) {
                            /*
                             * Kiểm tra khuyến mãi*/
                            $message_promo = '';
                            $check_promo = Promotion::checkPromo();
                            if ($check_promo && $check_promo > 0) {
                                $promotion = $check_promo;
                                $coin = $coin + ($coin / 100 * $promotion);
                                $message_promo = '. Khuyến mãi thêm ' . $check_promo . '%';
                            }
                            /*end*/
                            if ($this->sumCoin($user->id, abs(intval($coin)))) {
                                $payment->status = 1;
                                $payment->description = "Cộng tiền thành công";
                                $payment->user_id = $user->id;
                                $payment->username = $user->username;
                                $payment->save();
                                try {

                                    $user->level = PaymentMonth::getLevelMonth($user->id, $coin);
                                    $user->total_recharge = intval($user->total_recharge) + abs(intval($coin));
                                    $user->save();
                                    Logs::newLogs([
                                        'user_id' => $user->id,
                                        'username' => $user->username,
                                        'client_user_id' => null,
                                        'client_username' => null,
                                        'action' => 'add_coin',
                                        'action_coin' => 'in',
                                        'type' => 'add_coin',
                                        'description' => "Hệ thống Momo tự động cộng cho bạn " . number_format($coin) . $message_promo,
                                        'coin' => $coin,
                                        'old_coin' => $user->coin,
                                        'new_coin' => $user->coin + $coin,
                                        'price_id' => 0,
                                        'object_id' => null,
                                        'post_data' => json_encode($item),
                                        'result' => true,
                                        'ip' => '',
                                    ]);
                                    PaymentMonth::addCoin($user->id, $coin);
                                } catch (\Exception $e) {
                                    $this->handleCoinUser($user->id, $coin);
                                    continue;
                                }//$this->sendMessToBotTelegram("Cộng tiền thành công cho user_id " . $user->id . " với số tiền " . number_format($request->get('coin')));
                            } else {
                                $payment->status = -1;
                                $payment->description = "Cộng tiền thất bại";
                                $payment->save();
                                continue;
                                //$this->sendMessToBotAutoPaymentTelegram("Cộng tiền thất bại \n Mã giao dịch " . $item->SoThamChieu);
                            }
                        } else {
                            $payment->status = -1;
                            $payment->description = "Số tiền quá nhỏ";
                            $payment->save();
                        }
                    } else {
                        $payment->status = -1;
                        $payment->description = "Không tìm thấy user này";
                        $payment->save();
                    }
                } else {
                    continue;
                }
            } catch (\Exception $exception) {
//                $this->sendMessGroupCardToBotTelegram("auto_momo_v3 " . $exception->getMessage() . " line " . $exception->getLine());
            }
        }

        Cache::forget($key_cahe);
    }


    public function callApi()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.web2m.com/historyapimomo/f2a19986eb0bec0c6d3a50-afeb-b15a-8017-940c2a56f360',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Cookie: PHPSESSID=rl4jpkr8r0as1cb72jai2k9vkb'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo_now($response);
        return json_decode($response);
    }
}

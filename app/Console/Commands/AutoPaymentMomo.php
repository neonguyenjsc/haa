<?php

namespace App\Console\Commands;

use App\Http\Controllers\Traits\Lib;
use App\Models\Logs;
use App\Models\PaymentAutoMomo;
use App\Models\PaymentMonth;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Console\Command;

class AutoPaymentMomo extends Command
{
    use Lib;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto_momo';

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
        $array = [
            'http://momo.fb-api.com/api/momo/histories?user=0364075877&day=3&pass=e4a8234c-b51f-49ec-a721-d0cc18830be0',
            'http://momo.fb-api.com/api/momo/histories?user=0372504046&day=3&pass=e4a8234c-b51f-49ec-a721-d0cc18830be0'
        ];
        foreach ($array as $url) {

            $list = $this->getLogs($url);
            $cu_phap = 'chucmung username';
            $i = 0;
            $data = array_reverse($list->data);
            foreach ($data as $i__ => $item) {
                echo "<pre>";
                print_r($value = $item->tranId);
                echo "\n";
                echo "</pre>";
                //1620375192736
                if ($item->io != 1) {
                    continue;
                }
                if ($item->lastUpdate > 1630052915360) {

                    $i = $i + 1;
                    if ($i == 50) {
                        exit();
                    }
                    $item->trans_id = $item->tranId;
                    $item->syntax = $item->comment ?? '';
                    $item->date = $item->finishTime ?? '';
                    $item->phone = $item->user ?? '';
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
                } else {
                    continue;
                }
            }
        }
        echo "xong\n";
        exit();
    }

    public function getLogs($url)
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
//            CURLOPT_PROXY => '103.161.17.111:49357',
//            CURLOPT_PROXYUSERPWD => 'user49357:1Jq1dagKJS',
        ));


        $a = $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response);
        if (!isset($response->data)) {
            echo "<pre>";
            print_r($value = $a);
            echo "</pre>";
            exit();
        }
        return $response;
    }
}

<?php

namespace App\Console\Commands;

use App\Http\Controllers\Traits\Lib;
use App\Models\Logs;
use App\Models\LogsCoin;
use App\Models\NotificationUser;
use App\Models\PaymentAuto;
use App\Models\PaymentMonth;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class AutoPayment extends Command
{
    use Lib;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto_payment';
    protected $url = 'http://vcb.fb-api.com/api/v2/agency-payment?password=Ngocthu999&SoTaiKhoan=0281000599711&limit=500';

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
        $cache = 'auto_payment';
        if (Cache::has($cache)) {
            echo_now("Cron này đang được chạy vui lòng quay lại sau ít phút");
            exit();
        }
        Cache::remember($cache, 3600, function () {
            return true;
        });
        $url = [
            'http://vcb.fb-api.com/api/v2/agency-payment?password=Ngocthu999&SoTaiKhoan=1833399999&limit=100',
//            'http://vcb.fb-api.com/api/v2/agency-payment?password=Ngocthu999&SoTaiKhoan=0281000599711&limit=500'
        ];
        foreach ($url as $item1) {
            $list = $this->getList($item1);
            $data = $list->data;
            echo_now("Có " . count($data));
            foreach ($data as $item) {
                try {
                    $SoThamChieu = $item->SoThamChieu;
                    $array_so_tham_chieu = explode(" - ", $SoThamChieu);
                    $trans_id_start = $array_so_tham_chieu[0];
                    $trans_id_end = intval($array_so_tham_chieu[1]);
                    $coin = intval(str_replace(",", "", $item->SoTienGhiCo));
                    $p = PaymentAuto::where('trans_id_start', $trans_id_start)->where('trans_id_end', $trans_id_end)->orWhere('trans_id', $SoThamChieu)->first();
                    if (!$p) {
                        $code = strtolower($item->MoTa);
                        //(autofb88\s)([A-Za-z0-9_]*)
                        preg_match('/(chucmung\s)([A-Za-z0-9_]*)/', $code, $data);
                        if (isset($data[2])) {
                            $username = $data[2] ?? '!@#!@#!@#';
                            if ($username) {
                                $user = User::where('username', $username)->first();
                                if ($user) {
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
                                            try {
                                                /*
                                                 * Kiểm tra lên đại lý
                                                 * */
                                                $user->level = PaymentMonth::getLevelMonth($user->id, $coin);
                                                $user->total_recharge = intval($user->total_recharge) + abs(intval($coin));
                                                $user->save();
                                                $form_data = [
                                                    'trans_id' => $item->SoThamChieu,
                                                    'username' => $user->username,
                                                    'user_id' => $user->id,
                                                    'coin' => $coin,
                                                    'status' => 1,
                                                    'description' => "Hệ thống VIETCOMBANK tự động cộng cho bạn " . number_format($coin) . $message_promo,
                                                    'date' => $item->NgayGiaoDich,
                                                    '_id' => $item->_id,
                                                    'post_data' => json_encode($item),
                                                    'trans_id_start' => $trans_id_start,
                                                    'trans_id_end' => $trans_id_end,
                                                ];
                                                PaymentAuto::newPayment($form_data);
                                                Logs::newLogs([
                                                    'user_id' => $user->id,
                                                    'username' => $user->username,
                                                    'client_user_id' => null,
                                                    'client_username' => null,
                                                    'action' => 'add_coin',
                                                    'action_coin' => 'in',
                                                    'type' => 'add_coin',
                                                    'description' => "Hệ thống VIETCOMBANK tự động cộng cho bạn " . number_format($coin) . $message_promo,
                                                    'coin' => $coin,
                                                    'old_coin' => $user->coin,
                                                    'new_coin' => $user->coin + $coin,
                                                    'price_id' => 0,
                                                    'object_id' => null,
                                                    'post_data' => json_encode($item),
                                                    'result' => true,
                                                    'ip' => '',
                                                ]);
                                                try {
                                                    PaymentMonth::addCoin($user->id, $coin);
                                                    $this->senToTelegramAutoPayment(" + Tiền thành công \n username => $username \n Số tiền => $coin \n Cú pháp => $code \n Mã giao dịch => " . $item->SoThamChieu);

                                                } catch (\Exception $exception) {

                                                }
                                            } catch (\Exception $e) {
                                                $this->handleCoinUser($user->id, $coin);
                                                continue;
                                            }
                                        } else {
                                            $form_data = [
                                                'trans_id' => $item->SoThamChieu,
                                                'username' => $user->username,
                                                'user_id' => $user->id,
                                                'coin' => $coin,
                                                'status' => 0,
                                                'description' => "Cộng tiền thất bại",
                                                'date' => $item->NgayGiaoDich,
                                                '_id' => $item->_id,
                                                'post_data' => json_encode($item),
                                                'trans_id_start' => $trans_id_start,
                                                'trans_id_end' => $trans_id_end,
                                            ];
                                            PaymentAuto::newPayment($form_data);
                                            continue;
                                            //$this->sendMessToBotAutoPaymentTelegram("Cộng tiền thất bại \n Mã giao dịch " . $item->SoThamChieu);
                                        }
                                    } else {
                                        $form_data = [
                                            'trans_id' => $item->SoThamChieu,
                                            'username' => $user->username,
                                            'user_id' => $user->id,
                                            'coin' => $coin,
                                            'status' => 0,
                                            'description' => "Chưa nhập số tiền hoặc số tiền cộng phải lớn hơn 0",
                                            'date' => $item->NgayGiaoDich,
                                            '_id' => $item->_id,
                                            'post_data' => json_encode($item),
                                            'trans_id_start' => $trans_id_start,
                                            'trans_id_end' => $trans_id_end,
                                        ];
                                        PaymentAuto::newPayment($form_data);
                                        continue;
                                        //$this->sendMessToBotAutoPaymentTelegram("Chưa nhập số tiền hoặc số tiền cộng phải lớn hơn 0 \n Mã giao dịch " . $item->SoThamChieu);
                                        $this->res['message'] = " Chưa nhập số tiền hoặc số tiền cộng phải lớn hơn 0";
                                    }
                                } else {
                                    $form_data = [
                                        'trans_id' => $item->SoThamChieu,
                                        'username' => $user->username ?? '',
                                        'user_id' => $user->id ?? 0,
                                        'coin' => $coin,
                                        'status' => 0,
                                        'description' => "Không tìm thấy username " . $username . " sai cú pháp",
                                        'date' => $item->NgayGiaoDich,
                                        '_id' => $item->_id,
                                        'post_data' => json_encode($item),
                                        'trans_id_start' => $trans_id_start,
                                        'trans_id_end' => $trans_id_end,
                                    ];
                                    PaymentAuto::newPayment($form_data);
                                    continue;
                                    // $this->sendMessToBotAutoPaymentTelegram("Không tìm thấy username " . $username . " sai cú pháp \n Mã giao dịch " . $item->SoThamChieu);
                                }
                            } else {
                                $form_data = [
                                    'trans_id' => $item->SoThamChieu,
                                    'username' => $user->username ?? '0',
                                    'user_id' => $user->id ?? '0',
                                    'coin' => $coin,
                                    'status' => 0,
                                    'description' => " sai cú pháp",
                                    'date' => $item->NgayGiaoDich,
                                    '_id' => $item->_id,
                                    'post_data' => json_encode($item),
                                    'trans_id_start' => $trans_id_start,
                                    'trans_id_end' => $trans_id_end,
                                ];
                                PaymentAuto::newPayment($form_data);
                                continue;
                                //$this->sendMessToBotAutoPaymentTelegram("Sai cú pháp ..\n Mã giao dịch " . $item->SoThamChieu);
                            }
                        } else {
                            $form_data = [
                                'trans_id' => $item->SoThamChieu,
                                'username' => $user->username ?? '0',
                                'user_id' => $user->id ?? '0',
                                'coin' => $coin,
                                'status' => 0,
                                'description' => "Cộng tiền thất bại không tìm thấy mã code",
                                'date' => $item->NgayGiaoDich,
                                '_id' => $item->_id,
                                'post_data' => json_encode($item),
                                'trans_id_start' => $trans_id_start,
                                'trans_id_end' => $trans_id_end,
                            ];
                            PaymentAuto::newPayment($form_data);
                            continue;
                            // $this->sendMessToBotAutoPaymentTelegram("Cộng tiền thất bại không tìm thấy mã code \n Mã giao dịch " . $item->SoThamChieu);
                        }
                    } else {
                        $p->trans_id_start = $trans_id_start;
                        $p->trans_id_end = $trans_id_end;
                        $p->save();
                        continue;
                        //$this->sendMessToBotAutoPaymentTelegram("Đã cộng tiền ");
                    }
                } catch (\Exception $exception) {
                    continue;
                }
            }
        }
        Cache::forget($cache);
        exit();
    }

    public function getList($url)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Cookie: __cfduid=d2259ea45278b9de885b64a2019e72fea1600058099"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function senToTelegramAutoPayment($mess)
    {
        $curl = $this->curl('https://api.telegram.org/bot1604398885:AAEki2LWrnbqRDz8Hvezb5K6E6eiuJPypJw/sendMessage?chat_id=-540281235&text=' . urlencode($mess));
        return $curl;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Config;
use App\Models\ConfigCard;
use App\Models\LogPayment;
use App\Models\LogPaymentError;
use App\Models\Logs;
use App\Models\Payment;
use App\Models\PaymentMonth;
use App\Models\Promotion;
use App\Models\User;
use App\Models\UsersCoin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    //

    public function index(Request $request)
    {
        $payment = Payment::all();
        $logs_card = LogPayment::where(function ($q) {
            if (Auth::user()->role != 'admin') {
                $q->where('user_id', Auth::user()->id);
            }
        })->orderBy('id', 'desc')->paginate(100);
        $card = ConfigCard::where('status', 1)->get();
        return view('Payment.index',
            [
                'data' => $payment,
                'card' => $card,
                'logs_card' => $logs_card
            ]
        );
    }

    public function card(Request $request)
    {
        return $this->returnActionWeb($this->cardAction($request, Auth::user()));
    }

    public function cardApi(Request $request)
    {
        return $this->returnActionApi($this->cardAction($request, $request->user));
    }


    public function cardAction($request, $user)
    {
        $valida = Validator::make($request->all(), [
            'Network' => ['required', Rule::in('VTT', 'VNP', 'VMS', 'VNM')],
            'CardValue' => ['required', Rule::in(10000, 20000, 30000, 50000, 100000, 200000, 300000, 500000, 1000000)],
            'CardCode' => ['required'],
            'CardSeri' => ['required'],
        ], [
            'Network.required' => 'Bạn chưa chọn nhà mạng',
            'CardValue.required' => 'Bạn chưa chọn mệnh giá',
            'CardCode.required' => 'Bạn chưa nhập mã thẻ',
            'CardSeri.required' => 'Bạn chưa nhập số serial',
        ]);
        if ($valida->fails()) {
            return ['error' => $valida->errors()];
        }
        $data = $request->only('Network', 'CardValue', 'CardCode', 'CardSeri');
        $data['URLCallback'] = 'http://dichvu.baostar.pro/api/callback-nap-the-cao';
        $data['TrxID'] = $user->username . '|' . strtotime(date("Y-m-d H:i:s"));
        $partner_id_config = Config::where('alias', 'APIKeyTSR')->first();
        if (!$partner_id_config) {
            return ['error_' => 'Không thể nạp thẻ vui lòng liên hệ admin'];
        }
        $Network = ConfigCard::where('alias', $request->get('Network'))->where('status', 1)->first();
        if (!$Network) {
            return ['error_' => 'Thẻ này đang bảo trì'];
        }
        if (LogPayment::where('serial', $request->get('CardSeri'))->first()) {
            return ['error_' => 'Thẻ này đã được nạp. Vui lòng không thực hiện lại tránh khóa tài khoản'];
        }
        $data['APIKey'] = $partner_id_config->value;
        $data['request_id'] = $data['TrxID'];
        if ($Network->auto == 1) {
            $response = $this->callApiGachTheCao($data);
            if (isset($response) && $response->status && $response->status == 200) {
                $data['card'] = $request->get('Network');
                $data['amount'] = $request->get('CardValue');
                $data['code'] = $request->get('CardCode');
                $data['serial'] = $request->get('CardSeri');
                $data['username'] = $user->username;
                $data['user_id'] = $user->id;
                $data['post_data'] = json_encode($request->all());
                $data['result'] = json_encode($response);
                $data['status'] = 1;
                $data['description'] = $response->message ?? '';
                $data['charge'] = $Network->charge;
                $data['type'] = 'auto';
                $data['client_user_id'] = $request->client_user_id;
                $data['client_username'] = $request->client_username;
                $data['rollback_url_agency'] = $request->rollback_url_agency;
                $data['user_id_agency_lv2'] = $request->user_id_agency_lv2;
                $data['username_agency_lv2'] = $request->username_agency_lv2;
                $data['site_id_lv2'] = $request->site_id_lv2;
                $data['site_id'] = $request->site_id;
                LogPayment::newLog($data);
                return ['success' => $response->message ?? 'Nạp thành công  hệ thống sẽ kiểm tra và cộng tiền tự động cho bạn'];

            } else {

                $data['card'] = $request->get('Network');
                $data['amount'] = $request->get('CardValue');
                $data['code'] = $request->get('CardCode');
                $data['serial'] = $request->get('CardSeri');
                $data['username'] = $user->username;
                $data['user_id'] = $user->id;
                $data['post_data'] = json_encode($request->all());
                $data['result'] = json_encode($response);
                $data['status'] = 1;
                $data['description'] = $response->message;
                $data['charge'] = $Network->charge;
                $data['client_user_id'] = $request->client_user_id;
                $data['client_username'] = $request->client_username;
                $data['rollback_url_agency'] = $request->rollback_url_agency;
                $data['site_id'] = $request->site_id;
                $data['user_id_agency_lv2'] = $request->user_id_agency_lv2;
                $data['username_agency_lv2'] = $request->username_agency_lv2;
                $data['site_id_lv2'] = $request->site_id_lv2;
                LogPaymentError::newLogError($data);
                return ['error_' => $response->message ?? 'Nạp không thành công vui lòng liên hệ admin'];
            }
        } else {
            $data['card'] = $request->get('Network');
            $data['amount'] = $request->get('CardValue');
            $data['code'] = $request->get('CardCode');
            $data['serial'] = $request->get('CardSeri');
            $data['username'] = Auth::user()->username;
            $data['user_id'] = Auth::user()->id;
            $data['post_data'] = json_encode($request->all());
            $data['result'] = json_encode([]);
            $data['status'] = 1;
            $data['description'] = 'Đang chờ duyệt vui lòng đợi ít phút';
            $data['charge'] = $Network->charge;
            $data['type'] = 'handmade';
            $card = LogPayment::newLog($data);
            $data_send_telegram = [
                'Nhà mạng' => '--------------------' . $card->card . '-----------',
                'id' => $card->id,
                'serial' => $card->serial,
                'Mã thẻ' => $card->code,
                'Mệnh giá' => number_format($card->amount) . ' đ',
                'Tỷ lệ' => $card->charge . ' %'
            ];
            $txt = '';
            foreach ($data_send_telegram as $i => $item) {
                $txt = $txt . " $i : $item \n";
            }
            $this->sendMessGroupCardToBotTelegram('*100*' . $card->code . '#');
            $this->sendMessGroupCardToBotTelegram($txt);
            $this->sendMessGroupCardToBotTelegram('id_card|' . $card->id);
            return ['success' => $response->Message ?? 'Nạp thành công hệ thống đang duyệt thẻ cho bạn vui lòng đợi ít phút'];
        }
    }


    public function callApiTheSieuRe($data)
    {
        $url = 'http://tichhop247.com/API/NapThe';
        $curl = curl_init();
        $url = sprintf("%s?%s", $url, http_build_query($data));
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function callApiGachTheCao($data)
    {
        $post_data = [
            "network" => $this->convertKeyGachTheCao($data['Network']),
            "amount" => $data['CardValue'],
            "code" => $data['CardCode'],
            "seri" => $data['CardSeri'],
            "url_call_back" => "http://45.76.188.173/api/callback-nap-the-cao-v2",
            "request_id" => $data['request_id']
        ];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://139.180.135.59/api/transaction-card-api',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($post_data),
            CURLOPT_HTTPHEADER => array(
                'api-key: eW5RdE5qeU9XbDE2NTkxMDUyNjQ=',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    public function convertKeyGachTheCao($v)
    {
        switch ($v) {
            case 'VTT':
                return 1;
                break;
            case 'VNP':
                return 2;
            case 'VMS':
                return 3;
            case 'VNM':
                return 4;
                break;
            default:
                return 0;
                break;
        }
    }

    public function callBack(Request $request)
    {
//        $this->curl('https://api.telegram.org/bot914685080:AAEKWiw4x4M-ZWvNfX73_SRkbLG0LNULbqs/sendMessage?chat_id=983738766&text=' . urlencode("NAPTHE BAOSTAR \n" . json_encode($request->all())));
        $this->curl('https://api.telegram.org/bot914685080:AAEKWiw4x4M-ZWvNfX73_SRkbLG0LNULbqs/sendMessage?chat_id=983738766&text=' . urlencode("NAPTHE BAOSTAR \n" . $request->ip()));
        try {
            $payment = LogPayment::where('request_id', $request->TrxID)->where('serial', $request->CardSeri)->where('status', 1)->first();
            if ($payment) {
                if ($request->Code == "1") {
                    $dataRequestId = explode('|', $payment->TrxID);
                    if (isset($dataRequestId[0])) {
                        $user = User::find($payment->user_id);
                        if ($user) {
                            $coin = abs(intval($request->CardValue - ($request->CardValue * $payment->charge / 100)));
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
                            if ($this->sumCoin($user->id, $coin)) {
                                PaymentMonth::addCoin($user->id, $coin);
                                $user->level = PaymentMonth::getLevelMonth($user->id, $coin);
                                $user->total_recharge = $user->total_recharge + $coin;
                                $user->save();
                                $payment->description = 'Nạp tự động thành công';
                                $payment->status = 2;
                                $payment->real_coin = $coin;
                                $payment->save();
                                Logs::newLogs([
                                    'user_id' => $user->id,
                                    'username' => $user->username,
                                    'client_user_id' => null,
                                    'client_username' => null,
                                    'action' => 'add_coin',
                                    'action_coin' => 'in',
                                    'type' => 'add_coin',
                                    'description' => 'Hệ thống nạp thẻ tự động cộng cho bạn  ' . $coin . $message_promo,
                                    'coin' => $coin,
                                    'old_coin' => $user->coin,
                                    'new_coin' => $user->coin + $coin,
                                    'price_id' => 0,
                                    'object_id' => null,
                                    'post_data' => json_encode($request),
                                    'result' => true,
                                    'ip' => '',
                                ]);
                            }
                            PaymentMonth::addCoin($user->id, $coin);
                            $rs = curlApi('https://tangseeding.net/api/payment/callback', ['data' => $payment]);
                            try {
                                UsersCoin::newUserCoin($user, $coin, 'in');
                            } catch (\Exception $exception) {
                                $this->sendMessGroupCardToBotTelegram($exception->getMessage() . "\n" . $request->url() . "\n" . json_encode($request->all()));
                            }
                            return response()->json(['success' => true, 'message' => 'ok'], 200);
                        } else {
                            $payment->description = 'Không tìm thấy user!!';
                            $payment->status = 0;
                            $payment->save();
                            return response()->json(['success' => false, 'message' => 'Không tìm thấy user'], 400);
                        }
                    } else {
                        $payment->description = 'Không tìm thấy user';
                        $payment->status = 0;
                        $payment->save();
                        return response()->json(['success' => false, 'message' => 'Không tìm thấy user'], 400);
                    }
                } else {
                    if ($request->Code == "2" || $request->Code == "3") {
                        $payment->description = "Sai mệnh giá";
                    } elseif ($request->Code == "5") {
                        $payment->description = "Thẻ sai";
                    } elseif ($request->Code == "99") {
                        $payment->description = "Sever gạch thẻ bảo trì";
                    } else {
                        $payment->description = "Vui lòng liện hệ admin kiểm tra";
                    }
                    $payment->status = 0;
                    $payment->save();
                }
                return response()->json(['success' => true, 'message' => 'Đã cập nhật'], 200);
            } else {
                return response()->json(['success' => false, 'message' => ' Không tồn tại đơn này'], 400);
            }
        } catch (\Exception $exception) {
            $this->curl('https://api.telegram.org/bot914685080:AAEKWiw4x4M-ZWvNfX73_SRkbLG0LNULbqs/sendMessage?chat_id=983738766&text=' . $exception->getMessage() . "\n" . $exception->getLine() . "\n" . $exception->getFile());

        }
    }

    public function callBackV2(Request $request)
    {
//        $this->curl('https://api.telegram.org/bot914685080:AAEKWiw4x4M-ZWvNfX73_SRkbLG0LNULbqs/sendMessage?chat_id=983738766&text=' . urlencode("NAPTHE BAOSTAR \n" . json_encode($request->all())));
        $this->curl('https://api.telegram.org/bot914685080:AAEKWiw4x4M-ZWvNfX73_SRkbLG0LNULbqs/sendMessage?chat_id=983738766&text=' . urlencode("NAPTHE BAOSTAR \n" . $request->ip()));
        try {
            $payment = LogPayment::where('request_id', $request->transaction_code)->where('serial', $request->seri)->where('status', 1)->first();
            if ($payment) {
                if ($request->success && $request->success == true) {
                    $dataRequestId = explode('|', $payment->TrxID);
                    if (isset($dataRequestId[0])) {
                        $user = User::find($payment->user_id);
                        if ($user) {
                            $coin = abs(intval($request->amount - ($request->amount * $payment->charge / 100)));
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
                            if ($this->sumCoin($user->id, $coin)) {
                                PaymentMonth::addCoin($user->id, $coin);
                                $user->level = PaymentMonth::getLevelMonth($user->id, $coin);
                                $user->total_recharge = $user->total_recharge + $coin;
                                $user->save();
                                $payment->description = 'Nạp tự động thành công';
                                $payment->status = 2;
                                $payment->real_coin = $coin;
                                $payment->save();
                                Logs::newLogs([
                                    'user_id' => $user->id,
                                    'username' => $user->username,
                                    'client_user_id' => null,
                                    'client_username' => null,
                                    'action' => 'add_coin',
                                    'action_coin' => 'in',
                                    'type' => 'add_coin',
                                    'description' => 'Hệ thống nạp thẻ tự động cộng cho bạn  ' . $coin . $message_promo,
                                    'coin' => $coin,
                                    'old_coin' => $user->coin,
                                    'new_coin' => $user->coin + $coin,
                                    'price_id' => 0,
                                    'object_id' => null,
                                    'post_data' => json_encode($request->all()),
                                    'result' => true,
                                    'ip' => '',
                                ]);
                            }
                            PaymentMonth::addCoin($user->id, $coin);
                            $rs = curlApi('https://tangseeding.net/api/payment/callback', ['data' => $payment]);
                            try {
                                UsersCoin::newUserCoin($user, $coin, 'in');
                            } catch (\Exception $exception) {
                                $this->sendMessGroupCardToBotTelegram($exception->getMessage() . "\n" . $request->url() . "\n" . json_encode($request->all()));
                            }
                            return response()->json(['success' => true, 'message' => 'ok'], 200);
                        } else {
                            $payment->description = 'Không tìm thấy user!!';
                            $payment->status = 0;
                            $payment->save();
                            return response()->json(['success' => false, 'message' => 'Không tìm thấy user'], 400);
                        }
                    } else {
                        $payment->description = 'Không tìm thấy user';
                        $payment->status = 0;
                        $payment->save();
                        return response()->json(['success' => false, 'message' => 'Không tìm thấy user'], 400);
                    }
                } else {
                    $payment->description = "Thẻ sai";
                    $payment->status = 0;
                    $payment->save();
                }
                return response()->json(['success' => true, 'message' => 'Đã cập nhật'], 200);
            } else {
                return response()->json(['success' => false, 'message' => ' Không tồn tại đơn này'], 400);
            }
        } catch (\Exception $exception) {
            $this->curl('https://api.telegram.org/bot914685080:AAEKWiw4x4M-ZWvNfX73_SRkbLG0LNULbqs/sendMessage?chat_id=983738766&text=' . $exception->getMessage() . "\n" . $exception->getLine() . "\n" . $exception->getFile());

        }
    }
}

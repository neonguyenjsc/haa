<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use App\Models\PaymentAuto;
use App\Models\PaymentMonth;
use App\Models\Promotion;
use App\Models\User;
use App\Models\UsersCoin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WebhookController extends Controller
{
    //
    public function main(Request $request)
    {
        $app = $request->app;
        $coin = $request->coin;
        $syntax = $request->syntax;
        switch ($app) {
            case 'vcb':
            case 'momo':
                try {
                    $this->telegramService->senToTelegramAutoPayment("
                    --------------$app
                    Số tiền $coin
                    Cú pháp $syntax
                    ");

                } catch (\Exception $exception) {

                }
                $request->trans_id = $request->trans_id . $app . 'v2';
                $logs = PaymentAuto::where('id', '>', 34971)->where('trans_id', $request->trans_id)->first();
                if (!$logs) {
                    $cu_phap = 'chucmung username';
                    $logs = PaymentAuto::newPayment([
                        'trans_id' => $request->trans_id,
                        'username' => '',
                        'user_id' => 0,
                        'coin' => $request->coin,
                        'status' => 0,
                        'description' => 'Đang kiểm tra',
                        'date' => strtotime('now'),
                        '_id' => $request->trans_id,
                        'post_data' => json_encode($request->all()),
                        'trans_id_start' => $request->trans_id,
                        'trans_id_end' => $request->trans_id,
                    ]);
                    if ($coin > 0) {
                        $username = str_replace(str_replace("username", "", $cu_phap), "", strtolower($syntax));
                        $user = User::where('username', $username)->first();

                        $key_catch_duli_payment = 'auto_payment' . $username . $coin;
                        if (Cache::has($key_catch_duli_payment)) {
                            $logs->description = "Giao dịch này đã cộng tay trước đó ";
                            $logs->save();
                            return "Đã cộng tay trước đó";
                        }
                        if ($user) {
                            $message_promo = '';
                            $check_promo = Promotion::checkPromo();
                            if ($check_promo && $check_promo > 0) {
                                $promotion = $check_promo;
                                $coin = $coin + ($coin / 100 * $promotion);
                                $message_promo = '. Khuyến mãi thêm ' . $check_promo . '%';
                            }
                            if ($this->sumCoin($user->id, $coin)) {
                                try {
                                    $user->level = PaymentMonth::getLevelMonth($user->id, $coin);
                                    $user->total_recharge = intval($user->total_recharge) + abs(intval($coin));
                                    $user->save();
                                    $logs->username = $username;
                                    $logs->user_id = $user->id;
                                    $logs->status = 1;
                                    $logs->description = "Hệ thống " . $app . " nạp tiền thành công " . " với số tiền " . $coin . " " . $message_promo;
                                    $logs->save();
                                    Logs::newLogs([
                                        'user_id' => $user->id,
                                        'username' => $user->username,
                                        'client_user_id' => null,
                                        'client_username' => null,
                                        'action' => 'add_coin',
                                        'action_coin' => 'in',
                                        'type' => 'add_coin',
                                        'description' => "Hệ thống " . $app . " tự động cộng cho bạn " . number_format($coin) . $message_promo,
                                        'coin' => $coin,
                                        'old_coin' => $user->coin,
                                        'new_coin' => $user->coin + $coin,
                                        'price_id' => 0,
                                        'object_id' => null,
                                        'post_data' => json_encode($request->all()),
                                        'result' => true,
                                        'ip' => '',
                                    ]);
                                    PaymentMonth::addCoin($user->id, $coin);
                                    try {
                                        UsersCoin::newUserCoin($user, $coin, 'in');
                                    } catch (\Exception $exception) {
                                        $this->sendMessGroupCardToBotTelegram($exception->getMessage() . "\n" . $request->url() . "\n" . json_encode($request->all()));
                                    }
                                    return $this->setResponse($this->res);
                                } catch (\Exception $exception) {
                                    $this->handleCoinUser($user->id, $coin);
                                    $logs->description = "Lỗi cộng tiền " . $exception->getMessage() . " Line " . $exception->getLine();
                                    $logs->save();
                                    $this->res['message'] = $logs->description;
                                    return $this->setResponse($this->res);
                                }
                            } else {
                                $logs->description = "Lỗi cộng tiền" . $coin;
                                $logs->save();
                                $this->res['message'] = $logs->description;
                                return $this->setResponse($this->res);
                            }
                        } else {
                            $logs->description = "Không tìm thấy username" . $coin;
                            $logs->save();
                            $this->res['message'] = $logs->description;
                            return $this->setResponse($this->res);
                        }
                    } else {
                        $logs->description = "Số tiền quá nhỏ . " . $coin;
                        $logs->save();
                        $this->res['message'] = $logs->description;
                        return $this->setResponse($this->res);
                    }

                } else {
                    PaymentAuto::newPayment([
                        'trans_id' => $request->trans_id,
                        'username' => '',
                        'user_id' => 0,
                        'coin' => $request->coin,
                        'status' => 0,
                        'description' => 'Tồn tại giao dịch',
                        'date' => strtotime('now'),
                        '_id' => $request->trans_id,
                        'post_data' => json_encode($request->all()),
                        'trans_id_start' => $request->trans_id,
                        'trans_id_end' => $request->trans_id,
                    ]);
                    $this->res['message'] = $logs->description;
                    return $this->setResponse($this->res);
                }
            default:
                $this->res['message'] = "Chưa cài đăt";
                return $this->setResponse($this->res);
                break;
        }
    }

    public function webHookTelegram(Request $request)
    {
        $this->sendMessGroupCardToBotTelegram($request->ip());
        $key = $reply = $request->message['text'] ?? '';
        $uid = $request->message['from']['id'] ?? '';
        $zz = "-- ";
        if ($this->checkIsKey($key)) {//bot hỏi
            Cache::forget($uid);
            $key = Cache::rememberForever($uid, function () use ($key) {
                return $key;
            });
            switch ($key) {
                case '/help':
                    $mess = dataToText($this->listKeyTelegram());
                    break;
                case '/start':
                    $this->sendPhoTo($uid);
                    $mess = "Hãy ấn copy key kích hoạt như ảnh trên và gửi vào đây để xác minh tài khoản";
                    break;
                case '/reset':
                    $mess = dataToText($this->listKeyTelegram());
                    break;
                case '/kich_hoat_tai_khoan':
                    $this->sendPhoTo($uid);
                    $mess = "Hãy ấn copy api_key như ảnh trên và gửi vào đây để xác minh tài khoản";
                    break;
                case '/thong_tin_tai_khoan':
                    $user = $this->getUser($uid);
                    if ($user) {
                        $data_send = [
                            'Tên ' => $user->name,
                            'Số tiền hiện tại' => number_format($user->coin)
                        ];
                        $mess = dataToText($data_send);
                        break;
                    } else {
                        $mess = "Không tìm thấy thông tin nào cả vui lòng cả vui lòng gõ /kich_hoat_tai_khoan để cập nhật lại";
                    }
                    break;
                default;
                    $mess = "Gõ /help để hiển thị các chức năng";
                    break;
            }
        } else {//user trả lời
            if (Cache::has($uid)) {
                $key = Cache::get($uid);
            }
            switch ($key) {
                case '/start':
//                    $reply = str_replace("$", "M", $reply);
                    $user = User::where('api_key', $reply)->first();
                    if (!$user) {
                        $mess = "Sai token vui lòng gửi lại !";
                        break;
                    } else {
                        $a = User::where('telegram_id', $uid)->first();
                        if ($user && $a && ($a->id != $user->id)) {
                            $mess = "Tài khoản telegram này đã kích hoạt cho 1 tài khoản khác";
                            break;
                        } else {
                            $user->telegram_id = $uid;
                            $user->change_telegram_at = time();
                            $user->save();
                            $mess = "Kích hoạt thành công. Vui lòng xóa api key bạn vừa chat để tránh lộ key";
                            break;
                        }
                    }
                    break;
                case '/kich_hoat_tai_khoan':
                    $user = User::where('api_key', $reply)->first();
                    if (!$user) {
                        $mess = "Sai token vui lòng gửi lại !";
                        break;
                    } else {
                        $a = User::where('telegram_id', $uid)->first();
                        if ($user && $a && ($a->id != $user->id)) {
                            $mess = "Tài khoản telegram này đã kích hoạt cho 1 tài khoản khác";
                            break;
                        } else {
                            $user->telegram_id = $uid;
                            $user->change_telegram_at = time();
                            $user->save();
                            $mess = "Kích hoạt thành công. Vui lòng xóa api key bạn vừa chat để tránh lộ key";
                            break;
                        }
                    }
                    break;
                default;
                    $mess = "Gõ /help để hiển thị các chức năng!!";
                    break;
            }
        }

        $this->sendToTelegramId($mess, $uid);
        return true;
    }

    public function getUser($uid)
    {
        $user = User::where('telegram_id', $uid)->first();
        if (!$user || ($user && $user->change_telegram_at < $user->change_password_at)) {
            return false;
        }
        return $user;
    }

    public function sendPhoTo($chat_id)
    {
        $link_image = "https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEg0pvSRdrBCAtg_9-84zgLiElAziOgdAYDX2XOdSPQoF9KguqKgEoyMlvy_RpGpoPPAEHOb6LEj4q0pjbX5GzlJM1c9vhUgxlyuhq5SaY5TY-iVWfxo4o474oZNxkAgpEGKnStaii1NKAN9rd0LvT3f6lCBj5-_Tc_b1fUERUmTbdBbzLIKBnSUHuDT/s16000/Untitled.png";
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.telegram.org/bot5417742397:AAF3-0UvL5Rwqad7gh8liYFJPpsCIgKaHdc/sendPhoto?chat_id=' . $chat_id . '&photo=' . $link_image,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 1,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);
    }

    public function listKeyTelegram()
    {
        return [
            '/start' => 'Vui lòng gửi api key vào trong ô này',
        ];
    }

    public function checkIsKey($key)
    {
        $list = array_keys($this->listKeyTelegram());
        if (in_array($key, $list)) {
            return true;
        }
        return false;
    }


    public function baostar(Request $request)
    {
        $this->telegramService->sendMessGroupOrder4AllToBotTelegram(json_encode($request->all()));
    }

}

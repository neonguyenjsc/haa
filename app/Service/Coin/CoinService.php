<?php


namespace App\Service\Coin;


use App\Models\User;
use App\Service\Telegram\TelegramService;
use Illuminate\Support\Facades\DB;

class CoinService
{
    public function checkCoinAndHandleCoin($user_id, $coin)
    {
        $user = User::find($user_id);
        if ($user->coin > $user->total_recharge) {
            try {
                $tele = new  TelegramService();
                $tele->sendMessGroupCardToBotTelegram("Lỗi lệch tiền " . $user_id);
            } catch (\Exception $exception) {

            }
            return ['error' => 'Lỗi không xác định!'];
        }
        if ($this->checkCoinUser($user_id, $coin)) {
            if ($this->handleCoinUser($user_id, $coin)) {
//                addLogsHandleCoin($coin, $user);
                return true;
            } else {
                return ['error' => 'Không đủ tiền trong ví vui lòng nạp thêm!!!!!'];
            }
        } else {
            return ['error' => 'Không đủ tiền trong ví vui lòng nạp thêm !'];
        }
    }

    protected function checkCoinUser($user_id, $check = 1)
    {
        $user = User::where('id', $user_id)->first();
        if ($user->coin < 1) {
            return false;
        }
        if ($user->coin < $check) {
            return false;
        }
        return true;
    }

    protected function handleCoinUser($user_id, $coin)
    {
        DB::beginTransaction();
        $user = User::where('id', $user_id)->lockForUpdate()->first();
        if ($coin > 0) {
            if ($user->coin < 1) {
                return false;
            }
            $new_coin = intval($user->coin) - $coin;
            if ($new_coin >= 0) {
                try {
                    $user->coin = $new_coin;
                    $user->save();
                    DB::commit();
                    return true;
                } catch (\Exception $exception) {
                    DB::rollback();
                    return false;
                }
            }
        }
        DB::commit();
        return false;
    }

    public function SumCoin($user_id, $coin)
    {
        $user = User::where('id', $user_id)->lockForUpdate()->first();
        if ($coin > 0) {
            $new_coin = intval($user->coin) + $coin;
            if ($new_coin >= 0) {
                DB::beginTransaction();
                try {
                    $user->coin = $new_coin;
                    $user->save();
                    DB::commit();
                    return true;
                } catch (\Exception $e) {
                    DB::rollback();
                    return false;
                }
            }
        }
        return false;
    }
}

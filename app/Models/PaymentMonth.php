<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PaymentMonth extends Model
{
    use HasFactory;
    protected $table = 'payment_month';
    protected $fillable = [
        'user_id',
        'coin',
        'year',
        'month',
    ];
    protected $appends = [''];
    protected $hidden = ['cut_username'];

    public static function addCoin($user_id, $coin)
    {
        $user = User::find($user_id);
        $month = date('m');
        $year = date('Y');
        $logs = self::where('user_id', $user_id)->where('year', $year)->where('month', $month)->first();
        if ($logs) {
            $old_coin = intval($logs->coin);
            $logs->coin = $old_coin + $coin;
            $logs->save();
        } else {
            $logs = new self();
            $logs->user_id = $user_id;
            $logs->coin = $coin;
            $logs->month = $month;
            $logs->year = $year;
            $logs->username = $user->username;
            $logs->save();
        }
        if ($user->level != 6) {
            if ($user->total_recharge >= 300000) {
                $user->level = 2;
            }
            if ($user->total_recharge >= 10000000) {
                $user->level = 3;
            }
            $user->save();
        }
        return $logs;
//        $old_level = $user->level;
//        switch ($old_level) {
//            default:
//                if ($logs->coin >= 300000) {
//                    $user = User::where('id', $user_id)->first();
//                    $user->level = 2;
//                    $user->save();
//                }
//                if ($logs->coin >= 10000000) {
//                    $user = User::where('id', $user_id)->first();
//                    $user->level = 3;
//                    $user->save();
//                }
//                if ($logs->coin >= 50000000) {
//                    $user = User::where('id', $user_id)->first();
//                    $user->level = 6;
//                    $user->save();
//                }
//                break;
//        }
//        if ($user->level == 1 || $user->level == 2) {
//            if ($logs->coin >= 500000) {
//                $user = User::where('id', $user_id)->first();
//                $user->level = 2;
//                $user->save();
//            }
//            if ($logs->coin >= 1000000) {
//                $user = User::where('id', $user_id)->first();
//                $user->level = 5;
//                $user->save();
//            }
//            if ($logs->coin >= 10000000) {
//                $user = User::where('id', $user_id)->first();
//                $user->level = 3;
//                $user->save();
//            }
//            if ($logs->coin >= 50000000) {
//                $user = User::where('id', $user_id)->first();
//                $user->level = 4;
//                $user->save();
//            }
//        }
        //1 khách hàng
        // 2 đại lý
        // 3 nhà phân phối
        //4 npp 1
        // 5 đại lý 1
    }

    public function getCutUsernameAttribute()
    {
        if (Auth::user()->username != $this->username && Auth::user()->role != 'admin') {
            $length = strlen($this->username);
            return string2Stars($this->username, 0, round($length / 2));
        }
        return $this->username;
    }

    public static function getLevelMonth($user_id, $coin = 0)
    {
        $user = User::find($user_id);
        return $user->level;

        $month = date('m');
        $year = date('Y');
        $payment = self::where('user_id', $user_id)->where('year', $year)->where('month', $month)->first();
        if ($payment) {
            if ($coin > 0) {
                $check_coin = $payment->coin + $coin;
                if ($check_coin >= 5000000) {
                    return 2;
                } elseif ($check_coin >= 300000) {
                    return 3;
                }
            } else {
                if ($payment->coin >= 300000) {
                    return 2;
                } elseif ($payment->coin >= 5000000) {
                    return 3;
                }
            }
            return 1;
        } else {
            return 1;
        }
    }


}

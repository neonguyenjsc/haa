<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersCoin extends Model
{
    use HasFactory;
    protected $table = 'users_coin';
    protected $fillable = [
        'user_id',
        'coin_use',
        'coin_paid',
    ];

    public static function newUserCoin($user, $coin, $type)
    {
        $u = self::where('user_id', $user->id)->first();
        if (!$u) {
            $u = new self();
        }
        $u->user_id = $user->id;
        if ($type == 'out') {
            $u->coin_use = $u->coin_use + $coin;
        }
        $u->save();
        return $u;
    }


    public static function newData($data)
    {
        $a = new self();
        $a->fill($data);
        $a->save();
        return $a;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;
    protected $table = 'promotion';
    protected $fillable = [
        'start',
        'end',
        'value',
        'status',
    ];

    public static function newPromotion($data)
    {
        $ca = new self();
        $ca->fill($data);
        $ca->save();
        return $ca;
    }

    public static function getPromo()
    {
        return self::first();
    }

    public static function checkPromo()
    {
        $promo = self::getPromo();
        $now = strtotime('now');
        $start = strtotime($promo->start);
        $end = strtotime($promo->end);
        if ($now >= $start && $now <= $end) {
            return $promo->value;
        }
        return 0;
    }
}

<?php

namespace App\Models\Ads\FacebookVip;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VipLike extends Model
{
    use HasFactory;

    protected $table = 'ads_vip_like';
    protected $fillable = [
        'orders_id',
        'user_id',
        'username',
        'client_username',
        'client_user_id',
        'fb_id',
        'fb_name',
        'min_like',
        'max_like',
        'quantity',
        'days',
        'time_expired',
        'total_post',
        'max_post_daily',
        'min_delay',
        'max_delay',
        'pause',
        'description',
        'prices',
        'price_per',
        'notes',
        'result',
        'max_post_daily',
        'prices',
        'prices_agency',
        'prices_agency_lv2',
        'price_per',
        'price_per_agency',
        'price_per_agency_lv2',
        'user_id_agency_lv2',
        'username_agency_lv2',
    ];

    public static function newAds($data)
    {
        $a = new self();
        $a->fill($data);
        $a->save();
        return $a;
    }
}

<?php

namespace App\Models\Ads\FacebookVip;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VipLikeReaction extends Model
{
    use HasFactory;
    protected $table = 'ads_vip_reaction';
    protected $fillable = [
        'fb_id',
        'fb_name',
        'time_expired',
        'min_like',
        'max_like',
        'min_delay',
        'max_delay',
        'quantity',
        'prices',
        'price_per',
        'max_post_daily',
        'days',
        'pause',
        'user_id',
        'username',
        'description',
        'reactions',
        'reaction_config',
        'type',
        'note',
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

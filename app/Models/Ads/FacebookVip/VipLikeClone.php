<?php

namespace App\Models\Ads\FacebookVip;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VipLikeClone extends Model
{
    use HasFactory;

    protected $table = 'ads_vip_like_clone';
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
        'min_delay',
        'max_delay',
        'pause',
        'description',
        'prices',
        'price_per',
        'notes',
        'result',
        'prices',
        'prices_agency',
        'prices_agency_lv2',
        'price_per',
        'price_per_agency',
        'price_per_agency_lv2',
        'user_id_agency_lv2',
        'username_agency_lv2',
    ];

    protected $appends = ['full_link', 'object_id'];


    public function getFullLinkAttribute()
    {
        if (strpos($this->fb_id, "ttps://")) {
            return $this->fb_id;
        }
        return 'https://facebook.com/' . $this->fb_id;
    }

    public function getObjectIdAttribute()
    {
        return $this->fb_id;
    }

    public static function newAds($data)
    {
        $a = new self();
        $a->fill($data);
        $a->save();
        return $a;
    }
}

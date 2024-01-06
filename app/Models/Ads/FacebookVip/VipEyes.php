<?php

namespace App\Models\Ads\FacebookVip;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VipEyes extends Model
{
    use HasFactory;
    protected $table = 'ads_facebook_vip_eyes';

    protected $fillable = [
        'orders_id',
        'profile_id',
        'number_of_lives',
        'max_order_per_day',
        'num_dates',
        'num_minutes',
        'user_id',
        'username',
        'client_id',
        'client_username',
        'user_id_agency_lv2',
        'username_agency_lv2',
        'link',
        'object_id',
        'package_name',
        'prices',
        'price_per',
        'quantity',
        'start_like',
        'count_is_run',
        'type',
        'price_per_agency',
        'prices_agency',
        'prices_agency_lv2',
        'price_id',
        'status',
        'notes',
        'list_message',
        'time_view',
        'menu_id',
        'server',
        'prices',
        'prices_agency',
        'prices_agency_lv2',
        'price_per',
        'price_per_agency',
        'price_per_agency_lv2',
    ];

    public static function newAds($data)
    {
        $a = new self();
        $a->fill($data);
        $a->save();
        return $a;
    }


    protected $appends = ['full_link'];


    public function getFullLinkAttribute()
    {
        if (strpos($this->fb_id, "ttps://")) {
            return $this->fb_id;
        }
        return 'https://facebook.com/' . $this->fb_id;
    }

}

<?php

namespace App\Models\Ads\FacebookVipSale;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LikeClone extends Model
{
    use HasFactory;
    protected $table = 'ads_vip_like_sale_clone';
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
        'menu_id',
        'server',
        'package_name',
        'price_id',
        'prices_agency',
        'price_per_agency',
        'link',
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

    protected $appends = [
        'time_exp',
        'object_id',
        'full_link',
        'renew',
        'show_action',
    ];

    public function getTimeExpAttribute()
    {
        if ($this->status == 0) {
            return "Đã dừng";
        }
        if ($this->status == '2') {
            return 0 . ' ngày';
        }
        if ($this->time_expired < date("Y-m-d H:i:s")) {
            return 0 . ' ngày';
        } else {
            return Carbon::now()->diffInDays($this->time_expired) . ' ngày';
        }
    }

    public function getObjectIdAttribute()
    {
        return $this->fb_id;
    }

    public function getFullLinkAttribute()
    {
        return "https://facebook.com/" . $this->fb_id;
    }


    public function getRenewAttribute()
    {
        if ($this->package_name != 'facebook_vip_clone_sale_v10') {

            return 1;
        }
        return false;
    }

    public function getShowActionAttribute()
    {
        if ($this->renew) {
            return 1;
        }
        return false;
    }
}

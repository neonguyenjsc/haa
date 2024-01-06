<?php

namespace App\Models\Ads\Instagram;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstagramVipLike extends Model
{
    use HasFactory;

    protected $table = 'ads_instagram_vip_like';
    protected $fillable = [
        'orders_id',
        'user_id',
        'object_id',
        'username',
        'client_username',
        'client_user_id',
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
        'status',
        'prices_agency_lv2',
        'price_per',
        'price_per_agency',
        'price_per_agency_lv2',
        'server',
        'menu_id',
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
        'full_link',
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

    public function getFullLinkAttribute()
    {
        return "https://facebook.com/" . $this->fb_id;
    }
}

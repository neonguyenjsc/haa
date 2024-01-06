<?php

namespace App\Models\Ads\FacebookVip;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VipComment extends Model
{
    use HasFactory;

    protected $table = 'ads_vip_comment';
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
        'notes',
        'result',
        'list_message',
        'prices',
        'prices_agency',
        'prices_agency_lv2',
        'price_per',
        'price_per_agency',
        'price_per_agency_lv2',
        'user_id_agency_lv2',
        'username_agency_lv2',
        'server',
        'package_name',
    ];

    public static function newAds($data)
    {
        $a = new self();
        $a->fill($data);
        $a->save();
        return $a;
    }


    protected $appends = ['full_link', 'object_id', 'is_change_comment', 'show_action'];


    public function getFullLinkAttribute()
    {
        if (strpos($this->fb_id, "ttps://")) {
            return $this->fb_id;
        }
        return 'https://facebook.com/' . $this->fb_id;
    }

    public function getIsChangeCommentAttribute()
    {
        if (in_array($this->package_name, ['vip_comment_sv2', 'vip_comment_sv4'])) {
            return true;
        }
        return false;
    }

    public function getObjectIdAttribute()
    {
        return $this->fb_id;
    }

    public function getShowActionAttribute()
    {
        if ($this->is_change_comment) {
            return 1;
        }
        return false;
    }
}

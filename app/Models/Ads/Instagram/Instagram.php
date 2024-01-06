<?php

namespace App\Models\Ads\Instagram;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instagram extends Model
{
    use HasFactory;

    protected $table = 'ads_instagram';

    protected $fillable = [
        'orders_id',
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
        'time_view',
        'status',
        'notes',
        'list_message',
        'menu_id',
        'server',
        'prices',
        'prices_agency',
        'prices_agency_lv2',
        'price_per',
        'price_per_agency',
        'price_per_agency_lv2',
    ];

    public $tlc = [
        'instagram_follow_sv1',
        'instagram_follow_sv5',
        'instagram_follow_sv9',
        'instagram_like_sv1',
        'instagram_like_sv5',
    ];

    public $mfb = [
        'instagram_follow_sv10',
        'instagram_follow_sv2',
        'instagram_like',
        'instagram_like_sv6',
    ];

    public $viewyt = [
        'instagram_follow_sv3',
        'instagram_like_sv2',
        'instagram_like_sv3',
        'instagram_like_sv4',
        'instagram_view_99',
        'instagram_story_view_253',
        'instagram_view_story_sv0',
        'instagram_view_impression_100',
        'instagram_view_impression_101',
        'instagram_view_sv0',
    ];

    public $dvst = [
        'instagram_follow_sv11',
    ];
    protected $appends = ['status_string', 'status_class', 'allow_remove', 'full_link', 'is_check_order', 'show_action'];

    public static function newAds($data)
    {
        $a = new self();
        $a->fill($data);
        $a->save();
        return $a;
    }

    public function getStatusStringAttribute()
    {
        if ($this->status == 1) {
            if ($this->quantity <= $this->count_is_run) {
                return "Chạy xong";
            }
            return "Đang chạy";
        }
        if ($this->status == 0) {
            return "Đã hủy";
        }
        return "Đang chạy";
    }

    public function getStatusClassAttribute()
    {
        if ($this->status == 1) {
            if ($this->quantity == $this->count_is_run) {
                return "badge badge-success";
            }
            return "badge badge-warning";
        }
        if ($this->status == 0) {
            return "badge badge-danger";
        }
    }

    public function getFullLinkAttribute()
    {
        if (strpos($this->object_id, "ttps://")) {
            return $this->object_id;
        }
        if ($this->menu_id == 72) {
            return 'https://instagram.com/' . $this->object_id;
        }
        if ($this->menu_id != 124) {
            return 'https://instagram.com/p/' . $this->object_id;
        }
        return "#";
    }

    public function getAllowRemoveAttribute()
    {
        $array_package_check = array_merge($this->tlc, $this->mfb);
        if (in_array($this->menu_id, [72])) {
            if (in_array($this->package_name, $array_package_check) && in_array($this->status, [1]) && !$this->time_warranty) {
                return 1;
            }
        }
        return false;
    }

    public function getIsCheckOrderAttribute()
    {
        $array_package_check = array_merge($this->tlc, $this->mfb, $this->viewyt);
        $status = [1, -1];
        if (
            in_array($this->status, $status) &&
            $this->count_is_run < $this->quantity &&
            in_array($this->package_name, $array_package_check)) {
            return true;
        }
        return false;
    }

    public function getShowActionAttribute()
    {
        if ($this->allow_remove || $this->is_check_order) {
            return 1;
        }
        return false;
    }
}

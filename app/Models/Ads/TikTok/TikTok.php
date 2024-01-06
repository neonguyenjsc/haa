<?php

namespace App\Models\Ads\TikTok;

use App\Http\Controllers\Traits\Lib;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TikTok extends Model
{
    use HasFactory,Lib;
    protected $table = 'ads_tiktok';
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

    public $mfb = [
        "tiktok_follow_sv8",
    ];
    public $viewyt = [
        "tiktok_follow_sv2",
        "tiktok_like_v2",
        "tiktok_like_v4",
        "tiktok_like_v6",
        "tiktok_like_12",
    ];
    public $dvst = [
        'tiktok_live_5',
        'tiktok_live_6',
        'tiktok_live_7',
        'tiktok_live_8',
        'tiktok_live_9',
        'tiktok_live_10',
        'tiktok_live_11',
        'tiktok_live_12',
        'tiktok_live_13',
        'tiktok_like_v12',
    ];
    public $dvo = [
        "tiktok_comment_sv7",
        "tiktok_like_v7",
        "tiktok_like_v8",
        "tiktok_like_v9",
        "tiktok_like_v10",
        "tiktok_follow_sv11",
        "tiktok_follow_sv12",
        "tiktok_live_1",
        "tiktok_live_2",
        "tiktok_live_3",
        "tiktok_live_4",
        "tiktok_share_2",
        "tiktok_share_3",
        "tiktok_share_1",
        "tiktok_view_s5",
        "tiktok_view_s14",
        "tiktok_view_s16",
        "tiktok_comment_sv8",
    ];

    public $mlike = [
        'tiktok_view_s15'
    ];


    protected $appends = ['status_string', 'status_class', 'full_link', 'is_check_order', 'show_action', 'is_check_order', 'show_action'];

    public function getIsCheckOrderAttribute()
    {
        $array_package_check = array_merge($this->dvo, $this->dvst, $this->viewyt, $this->mfb);
        $status = [1, -1];
        if (
            in_array($this->status, $status) &&
            $this->count_is_run < $this->quantity &&
            in_array($this->package_name, $array_package_check)) {
            return true;
        }
        return false;
    }

    public function getFullLinkAttribute()
    {
        if (strpos($this->object_id, 'ttps://')) {
            return $this->object_id;
        }
        if ($this->menu_id == 81) {
            return "https://www.tiktok.com/@" . $this->object_id;
        }
        return $this->object_id;
    }

    public function getShowActionAttribute()
    {
        if ($this->is_check_order) {
            return 1;
        }
        return false;
    }

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
        if ($this->status == -1) {
            return "Dừng";
        }
        if ($this->status == 3) {
            return "Hoàn tiền";
        }
        if ($this->status == 2) {
            return "Hoàn thành";
        }
        return "Đang chạy";
    }

    public function getStatusClassAttribute()
    {
        if ($this->status == 1) {
            if ($this->quantity == $this->count_is_run) {
                return "badge badge-success";
            }
            if (strtotime($this->addDaysWithDate($this->created_at, 3)) < strtotime(date("Y-m-d H:i:s"))) {
                if ($this->count_is_run == 0) {
                    return "Chạy xong";
                }
            }
            return "badge badge-warning";
        }
        if ($this->status == 0) {
            return "badge badge-danger";
        }
        if ($this->status == 2) {
            return "badge badge-success";
        }
        if ($this->status == -1) {
            return "badge badge-danger";
        }
        if ($this->status == 3) {
            return "badge badge-danger";
        }
    }
}

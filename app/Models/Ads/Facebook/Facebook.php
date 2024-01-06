<?php

namespace App\Models\Ads\Facebook;

use App\Http\Controllers\Traits\Lib;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facebook extends Model
{
    // -1 = pause
    //0 =>hủy
    //1 =>chạy
    //2=> hoàn thành
    //3 => hoàn tiền
    use HasFactory, Lib;
    protected $table = 'ads_facebook';
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
        'price_per_agency_lv2',
        'prices_agency',
        'prices_agency_lv2',
        'price_id',
        'status',
        'notes',
//        'list_message',
        'time_view',
        'menu_id',
        'server',
    ];

    protected $appends = ['status_string', 'status_class', 'allow_warranty', 'show_action', 'is_check_order', 'allow_remove', 'price_per_remove', 'full_link', 'run_order', 'allow_warranty'];

    public $autolike = [

    ];

    public $tlc = [
        'facebook_follow_sv1',
        'facebook_follow_sv15',
        'facebook_like_v3',
        'facebook_like_v7',
        'facebook_like_v10',
        'facebook_like_v13',
        'facebook_comment_sv1',
        'facebook_comment_sv10',
        'facebook_like_page_sv17',
        'facebook_mem_v3',
    ];

    public $mxh2 = [
        'facebook_follow_sv28',
        'facebook_like_page_sv18',
        'facebook_like_page_sv19',
    ];

    public $mfb = [
        'facebook_like',
        'facebook_follow',
    ];
    public $farm = [
        'facebook_like_page_sv9',
        'facebook_like_page_sv10',
        'facebook_like_page_sv11',
        'facebook_like_page',
        'facebook_follow_sv4',
        'facebook_follow',
        'facebook_follow_sv14',
        'facebook_follow_sv12',
        'facebook_follow',
        'facebook_like_v8',
        'facebook_like_v4',
        'facebook_like_v6',
        'facebook_like_v9',
        'facebook_like_v2',
        'facebook_like_v12',
        'facebook_comment_sv3',
        'facebook_comment_sv4',
        'facebook_comment_sv5',
        'facebook_mem_v8',

    ];
    public $trumvn = [
        'facebook_follow_sv19',
        'facebook_follow_sv8',
    ];
    public $sabommo = [
        'facebook_like_page_sv14',
        'facebook_like_page_sv15',
        'facebook_follow_sv13',
        'facebook_follow_sv21',
        'facebook_mem_no_avatar',
        'facebook_like_v19',
    ];

    public $baostar = [
        'facebook_comment_sv7',
        'facebook_follow_sv19',
        'facebook_like_page_sv16',
        'facebook_mem_v10',
        'facebook_like_v15',
        'facebook_share_sv8',
        'facebook_view_21',
        'facebook_view_story_v4',
        'facebook_checkin_page_sv7',
        'facebook_checkin_page_sv6',
    ];

    public $ttc = [
        'facebook_follow_sv24',
    ];


    public static function newAds($data)
    {
        if (isset($data['object_id']) && strlen($data['object_id']) > 99) {
            $data['object_id'] = substr($data['object_id'], 0, 99);
        }
        $a = new self();
        $a->fill($data);
        $a->save();
        return $a;
    }

    public function getFullLinkAttribute()
    {
        if (strpos($this->object_id, "ttps://")) {
            return $this->object_id;
        }
        return 'https://facebook.com/' . $this->object_id;
    }

    public function getIsCheckOrderAttribute()
    {
        $array_package_check = array_merge($this->autolike, $this->farm, $this->sabommo, $this->tlc, $this->sabommo, $this->ttc, $this->mfb);
        $status = [1, -1];
        if (
            in_array($this->status, $status) &&
            $this->count_is_run < $this->quantity &&
            in_array($this->package_name, $array_package_check)) {
            return true;
        }
        return false;
    }


    public function getStatusStringAttribute()
    {
        if ($this->status == 1) {
            if ($this->quantity <= $this->count_is_run) {
                return "Chạy xong";
            }
//            if (strtotime($this->addDaysWithDate($this->created_at, 3)) < strtotime(date("Y-m-d H:i:s"))) {
//                if ($this->count_is_run == 0) {
//                    return "Chạy xong";
//                }
//            }
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
            if ($this->quantity <= $this->count_is_run) {
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
        if ($this->status == -1) {
            return "badge badge-danger";
        }
        if ($this->status == 3) {
            return "badge badge-danger";
        }
        if ($this->status == 2) {
            return "badge badge-success";
        }
    }

    public function getAllowWarrantyAttribute()
    {
        $array_package_check = array_merge($this->farm, $this->sabommo);
        if ($this->warranty && $this->warranty == 1 || (in_array($this->package_name, $array_package_check) && ($this->count_is_run >= $this->quantity))) {
            return 1;
        }
        return false;
    }

    public function getAllowRemoveAttribute()
    {
        $array_package_check = array_merge($this->autolike, $this->mfb, $this->tlc, $this->farm, $this->sabommo, $this->baostar);
        if (in_array($this->menu_id, [41, 45, 46, 44, 47])) {
            if (in_array($this->package_name, $array_package_check) && in_array($this->status, [1, -1]) && !$this->time_warranty) {
                if ($this->count_is_run < $this->quantity) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getRunOrderAttribute()
    {
        $array_package_check = array_merge($this->farm, $this->trumvn, $this->baostar);
        if (1) {
            if (in_array($this->package_name, $array_package_check) && in_array($this->status, [-1]) && !$this->time_warranty) {
                return 1;
            }
        }
        return false;
    }

    public function getShowActionAttribute()
    {
        if ($this->allow_warranty || $this->is_check_order || $this->allow_remove || $this->run_order || $this->allow_warranty) {
            return 1;
        }
        return false;
    }

    public function getPricePerRemoveAttribute()
    {
        if (in_array($this->package_name, $this->baostar)) {
            return 0;
        }
        if (in_array($this->package_name, $this->tlc)) {
            return 1000;
        }
        if (in_array($this->package_name, $this->mfb) || in_array($this->package_name, $this->farm)) {
            return 500;
        }
        return 1000;
    }
}

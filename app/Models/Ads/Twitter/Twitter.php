<?php

namespace App\Models\Ads\Twitter;

use App\Http\Controllers\Traits\Lib;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Twitter extends Model
{
    use HasFactory, Lib;
    protected $table = 'ads_twitter';
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
        'status',
        'notes',
        'list_message',
        'time_view',
        'menu_id',
        'server',
        'status_source',
        'prices',
        'prices_agency',
        'prices_agency_lv2',
        'price_per',
        'price_per_agency',
        'price_per_agency_lv2',
    ];

    public $dvo = [
    ];

    public $viewyt = [
        'twitter_like_sv1',
        'twitter_like_sv2',
        'twitter_like_sv3',
    ];

    public $dvst = [
    ];

    public $mfb = [];
    protected $appends = ['status_string', 'status_class', 'full_link', 'is_check_order', 'show_action', 'is_check_order', 'show_action'];

    public function getShowActionAttribute()
    {
        if ($this->is_check_order) {
            return 1;
        }
        return false;
    }

    public function getIsCheckOrderAttribute()
    {
        $array_package_check = array_merge($this->viewyt, $this->dvst, $this->dvo);
        $status = [1, -1];
        if (
            in_array($this->status, $status) &&
            $this->count_is_run < $this->quantity &&
            in_array($this->package_name, $array_package_check)) {
            return true;
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

    public function getFullLinkAttribute()
    {
        return $this->object_id;
        if (strpos($this->object_id, 'https://')) {
            return $this->object_id;
        }
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

<?php

namespace App\Models\Ads\Facebook;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotTuongTac extends Model
{
    use HasFactory;

    protected $table = 'ads_facebook_bot';
    protected $fillable = [
        'username',
        'user_id',
        'prices',
        'price_per',
        'client_username',
        'client_id',
        'price_per_agency',
        'prices_agency',
        'user_id_agency_lv2',
        'user_name_agency_lv2',
        'prices_agency_lv2',
        'price_per_agency_lv2',
        'title',
        'quantity',
        'status',
        'orders_id',
        'price_id',
        'menu_id',
        'server',
        'package_name',
        'time_end',
        'days',
    ];

    public static function newThis($data)
    {
        $t = new self();
        $t->fill($data);
        $t->save();
        return $t;
    }

    protected $appends = [
        'class',
        'status_str',
        'time',
    ];

    public function getStatusStrAttribute()
    {
        if ($this->quantity == $this->count_is_run) {
            return 'Xong';
        }
        switch ($this->status) {
            case 0:
                return 'Đang chạy';
                break;
            case 1:
                return 'Đang chạy';
                break;
            case 2:
                return 'Đã hủy';
                break;
            case 3:
                return 'Hoàn thành';
                break;
            case 4:
                return 'Hủy';
                break;
            default:
                return 'Đang chạy';
                break;
        }
    }

    public function getTimeAttribute()
    {
        return strtotime($this->created_at);
    }

    public function getClassAttribute()
    {

        if ($this->quantity == $this->count_is_run) {
            return 'badge badge-success';
        }
        switch ($this->status) {
            case 0:
                return 'badge badge-primary';
                break;
            case 1:
                return 'badge badge-primary';
                break;
            case 2:
                return 'badge badge-danger';
                break;
            case 3:
                return 'badge badge-success';
                break;
            case 4:
                return 'badge badge-danger';
                break;
            default:
                return 'badge badge-danger';
                break;
        }
    }
}

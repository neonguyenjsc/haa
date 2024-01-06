<?php

namespace App\Models\Ads\Other;

use App\Models\Menu;
use App\Models\Prices;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SpamMessage extends Model
{
    use HasFactory;

    protected $table = 'spam_message';
    protected $fillable = [
        'orders_id',
        'user_id',
        'content',
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
        'menu_id',
        'server',
        'prices',
        'prices_agency',
        'prices_agency_lv2',
        'price_per',
        'price_per_agency',
        'price_per_agency_lv2',
    ];

    protected $appends = ['status_string', 'status_class'];

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
            if ($this->quantity == $this->count_is_run) {
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
}

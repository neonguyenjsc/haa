<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    //status -2 là các đơn tạo lỗi
    // - 1 chờ đếm auto mới hoàn
    // 1 đã hoàn
    // 0 chờ hoàn
    protected $table = 'refund';

    protected $fillable = [
        'user_id',
        'username',
        'client_id',
        'client_username',
        'object_id',
        'coin',
        'price_per',
        'quantity',
        'description',
        'status',
        'category_id',
        'tool_name',
        'package_name',
        'server',
        'prices_agency',
        'price_per_agency',
        'vat',
        'user_id_agency_lv2',
        'prices_agency_lv2',
        'price_per_agency_lv2',
        'price_per_remove',
        'orders_id',
        'table',
        'time_create',
        'username_agency_lv2',
        'response',
        'quantity_buy',
        'days',
    ];


    public function getActionTypeAttribute()
    {
        if ($this->orders_id > 0) {
            return true;
        }
        return false;
    }

    protected $appends = [
        'action_type'
    ];

    public static function newRefund($data)
    {
        $refund = new self();
        $data['time_create'] = strtotime('now');
        $refund->fill($data);
        $refund->save();
        if ($refund->status != -1) {
//            refundAgency($data);
        }
        return $refund;
    }
}

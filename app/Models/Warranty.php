<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warranty extends Model
{
    //
    protected $table = 'warranty';
    protected $fillable = [
        'orders_id',
        'id_ads',
        'table',
        'time_check',
        'count_warranty',
        'start_like',
        'quantity',
        'time_buy',
        'package_name',
        'name',
        'menu_id',
        'user_id',
        'username',
        'object_id',
        'response',
        'notes',
        'status',
    ];

    public static function newWarranty($data)
    {
        $t = new self();
        $t->fill($data);
        $t->save();
        return $t;
    }
}

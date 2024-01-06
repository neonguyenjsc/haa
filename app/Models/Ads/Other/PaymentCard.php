<?php

namespace App\Models\Ads\Other;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentCard extends Model
{
    use HasFactory;

    protected $table = 'ads_card';
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


    public static function newAds($data)
    {
        $a = new self();
        $a->fill($data);
        $a->save();
        return $a;
    }
}

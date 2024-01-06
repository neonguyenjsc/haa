<?php

namespace App\Models\Ads\Proxy;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proxy extends Model
{
    use HasFactory;
    protected $table = 'ads_proxy';

    protected $fillable = [
        'orders_id',
        'user_id',
        'client_id',
        'user_id_agency_lv2',
        'prices',
        'price_per',
        'quantity',
        'price_per_agency',
        'prices_agency',
        'prices_agency_lv2',
        'price_id',
        'menu_id',
        'price_id',
        'status',
        'username',
        'server',
        'type',
        'username_agency_lv2',
        'client_username',
        'package_name',
        'ip',
        'port',
        'proxy_username',
        'proxy_password',
        'time_end',
        'notes',
        'prices',
        'prices_agency',
        'prices_agency_lv2',
        'price_per',
        'price_per_agency',
        'price_per_agency_lv2',
        'user_id_agency_lv2',
        'username_agency_lv2',
    ];

    public static function newAds($data)
    {
        $a = new self();
        $a->fill($data);
        $a->save();
        return $a;
    }
}

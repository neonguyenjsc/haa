<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricesConfig extends Model
{
    use HasFactory;


    protected $table = 'v2_prices_config';
    protected $fillable = [
        'menu_id',
        'prices',
        'name',
        'status',
        'level_id',
        'package_name',
        'sort',
        'description',
        'active',
        'message',
        'notes',
        'price_id'
    ];

    public static function newPricesConfig($data)
    {
        return self::newData($data);
    }


    public static function newData($data)
    {
        $a = new self();
        $a->fill($data);
        $a->save();
        return $a;
    }

    public static function getPricesByLevel($menu_id, $level_id)
    {
        $key_cache = 'get_prices_' . $menu_id . '_' . $level_id;
        return self::where('menu_id', $menu_id)->where('level_id', $level_id)->where('status', 1)->where('active', 1)->orderBy('sort', 'asc')->get();
//        return ;
    }

    public static function getPricesByPriceId($price_id, $level_id)
    {
        return self::where('price_id', $price_id)->where('level_id', $level_id)->where('status', 1)->first();
    }

    public function menu()
    {
        return $this->hasOne('App\Models\Menu', 'id', 'menu_id');
    }

    public function level()
    {
        return $this->hasOne('App\Models\Level', 'id', 'level_id');
    }
}

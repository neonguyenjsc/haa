<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prices extends Model
{
    use HasFactory;

    protected $table = 'v2_prices';
    protected $fillable = [
        'menu_id',
        'prices',
        'name',
        'status',
        'min',
        'max',
        'package_name',
        'description',
        'sort',
        'prices_origin',
        'message',
        'notes',
    ];

    public static function newPrices($data)
    {
        return self::newData($data);
    }

//    protected $appends = ['menu'];

    public static function newData($data)
    {
        $a = new self();
        $a->fill($data);
        $a->save();
        return $a;
    }

    public static function getPrices($package_name, $user)
    {
        $prices = self::where('package_name', $package_name)->where('status', 1)->first();
        if ($prices) {
            $prices_config = PricesConfig::getPricesByPriceId($prices->id, $user->level);
            if ($prices_config) {
                if ((request()->price_per_agency && request()->price_per_agency < $prices_config->prices) || (request()->price_per_agency_lv2 && request()->price_per_agency_lv2 < $prices_config->prices)) {
                    return ['error' => 'Quản trị chưa cập nhật giá. Vui lòng liên hệ admin'];
                }
                return [
                    'price' => $prices,
                    'price_config' => $prices_config
                ];
            }
            return ['error' => 'Chưa cập nhật giá cho bạn. vui lòng liên hệ admin'];
        }
        return ['error' => 'Dịch vụ đang bảo trì vui lòng quay lại sau'];
    }

    public static function checkMinMax($quantity, $prices)
    {
        if (isset($prices->min) && isset($prices->max)) {
            if ($quantity >= $prices->min && $quantity <= $prices->max) {
                return true;
            }
            return ['error' => 'Vui lòng chọn số lượng từ ' . $prices->min . ' đến ' . $prices->max];
        }
        return ['error' => 'Vui lòng chọn số lượng từ ' . $prices->min . ' đến ' . $prices->max . "!!"];
    }

//    public function getMenuAttribute()
//    {
//        return Menu::find($this->menu_id);
//    }

    public function menu()
    {
        return $this->hasOne('App\Models\Menu', 'id', 'menu_id');
    }

    public static function getPackageNameAllow($menu_id)
    {
        return self::where('menu_id', $menu_id)->where('status', 1)->pluck('package_name')->toArray();
    }
}

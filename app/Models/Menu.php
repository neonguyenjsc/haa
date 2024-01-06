<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'v2_menu';

    protected $fillable = [
        'category_id',
        'status',
        'name',
        'description',
        'notes',
        'icon',
        'path',
        'sort',
        'hot',
    ];

    public static function newMenu($data)
    {
        return self::newRecord($data);
    }

    public static function newRecord($data)
    {
        $a = new self();
        $a->fill($data);
        $a->save();
        return $a;
    }

    public function prices()
    {
        return $this->hasMany('App\Models\Prices', 'menu_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'v2_category';
    protected $fillable = [
        'name',
        'status',
        'icon',
        'sort',
        'hot'
    ];

    public static function newCategory($data)
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

    public function menu()
    {
        return $this->hasMany('App\Models\Menu', 'category_id', 'id')->where('status',1);
    }
}

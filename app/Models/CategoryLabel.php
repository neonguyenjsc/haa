<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryLabel extends Model
{
    use HasFactory;

//    protected $with = ['category'];
    protected $table = 'v2_category_label';

    public function category()
    {
        return $this->hasMany('App\Models\Category', 'category_label_id', 'id')->where('status', 1)->orderBy('sort', 'asc');
    }
}

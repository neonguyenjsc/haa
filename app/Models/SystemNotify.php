<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemNotify extends Model
{
    use HasFactory;

    protected $table = 'system_notify';
    protected $fillable = [
        'content',
        'title'
    ];

    public static function newNotify($data)
    {
        $ca = new self();
        $ca->fill($data);
        $ca->save();
        return $ca;
    }
}

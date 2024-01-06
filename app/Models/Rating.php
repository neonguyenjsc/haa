<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;
    protected $table = 'rating';
    protected $fillable = [
        'rating',
        'user_id',
        'username',
        'name',
        'content',
        'public',
        'user_id_admin',
        'start',
        'username_admin',
    ];

    public static function newRating($data)
    {
        $ca = new self();
        $ca->fill($data);
        $ca->save();
        return $ca;
    }
}

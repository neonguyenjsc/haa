<?php

namespace App\Models\Ads\Other;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RipFacebook extends Model
{
    use HasFactory;
    protected $table = 'ads_rip_facebook';
    protected $fillable = [
        'link',
        'prices',
        'status',
        'notes',
        'username',
        'user_id',
        'client_user_id',
        'client_username'
    ];

    public static function newAds($data)
    {
        $a = new self();
        $a->fill($data);
        $a->save();
        return $a;
    }
}

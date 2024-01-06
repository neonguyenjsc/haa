<?php

namespace App\Models\Ads\Facebook;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotComment extends Model
{
    use HasFactory;
    protected $table = 'ads_bot_comment';

    protected $fillable = [
        'fb_id',
        'orders_id',
        'fb_name',
        'days',
        'time_end',
        'user_id',
        'client_id',
        'user_id_agency_lv2',
        'username',
        'client_username',
        'username_agency_lv2',
        'prices',
        'prices_agency',
        'prices_agency_lv2',
        'price_per',
        'price_per_agency',
        'price_per_agency_lv2',
        'menu_id',
        'package_name',
        'server',
        'notes',
        'proxy',
        'object_id',
        'post_data',
    ];

    public static function newThis($data)
    {
        $t = new self();
        $t->fill($data);
        $t->save();
        return $t;
    }


    protected $appends = ['full_link', 'object_id'];


    public function getFullLinkAttribute()
    {
        if (strpos($this->fb_id, "ttps://")) {
            return $this->fb_id;
        }
        return 'https://facebook.com/' . $this->fb_id;
    }

    public function getObjectIdAttribute()
    {
        return $this->fb_id;
    }
}

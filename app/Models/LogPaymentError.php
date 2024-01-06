<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogPaymentError extends Model
{
    use HasFactory;

    protected $table = 'logs_payment_error';
    protected $fillable = [
        'user_id',
        'client_user_id',
        'site_id',
        'status',
        'request_id',
        'result',
        'post_data',
        'amount',
        'card',
        'code',
        'serial',
        'username',
        'description',
        'charge',
        'site_id_lv2',
        'username_agency_lv2',
        'user_id_agency_lv2',
        'client_username',
    ];

    public static function newLogError($data)
    {
        $log = new self();
        $log->fill($data);
        $log->save();
        return $log;
    }
}

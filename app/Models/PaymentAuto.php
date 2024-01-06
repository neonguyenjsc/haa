<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentAuto extends Model
{
    protected $table = 'logs_payment_auto';

    protected $fillable = [
        'trans_id',
        'username',
        'user_id',
        'coin',
        'status',
        'description',
        'date',
        '_id',
        'post_data',
        'trans_id_start',
        'trans_id_end',
    ];

    public static function newPayment($data)
    {
        $payment = new self();
        $payment->fill($data);
        $payment->save();
        return $payment;
    }
}

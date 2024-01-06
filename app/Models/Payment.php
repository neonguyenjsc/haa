<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payment_info';

    protected $fillable = [
        'name',
        'full_name',
        'stk',
        'branch',
        'type',
        'status'
    ];

    public static function newPayment($data)
    {
        $payment = new self();
        $payment->fill($data);
        $payment->save();
        return $payment;
    }
}

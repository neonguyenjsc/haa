<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAutoMomo extends Model
{
    use HasFactory;
    protected $table = 'logs_auto_momo';
    protected $fillable = [
        'user_id',
        'username',
        'status',
        'description',
        'amount',
        'trans_id',
        'phone',
        'date',
        'syntax',
        'response',
    ];

    public static function newPayment($data)
    {
        $payment = new self();
        $payment->fill($data);
        $payment->save();
        return $payment;
    }

    public function getStatusStringAttribute()
    {
        if ($this->status == 1) {
            return 'Thành công';
        }
        return 'Thất bại';
    }


    public function getStatusClassAttribute()
    {
        if ($this->status == 1) {
            return 'badge badge-success';
        }
        return 'badge badge-danger';
    }

    protected $appends = ['status_string', 'status_class'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogPayment extends Model
{
    use HasFactory;

    protected $table = 'logs_payment';
    protected $fillable = [
        'user_id',
        'client_user_id',
        'client_username',
        'site_id',
        'rollback_url_agency',
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
        'username_active',
        'time_active',
        'notes',
        'type',
        'real_coin',
        'site_id_lv2',
        'username_agency_lv2',
        'user_id_agency_lv2',
        'client_username',
    ];

    protected $appends = ['status_string'];

    public static function newLog($data)
    {
        $log = new self();
        $log->fill($data);
        $log->save();
        return $log;
    }

    public function getStatusStringAttribute()
    {
        if ($this->status == 1) {
            return [
                'class' => 'text-primary',
                'text' => 'Đang chờ duyệt'
            ];
        }
        if ($this->status == 2) {
            return [
                'class' => 'text-success',
                'text' => 'Thành công'
            ];
        }
        if ($this->status == 0) {
            return [
                'class' => 'text-danger',
                'text' => 'Lỗi'
            ];
        }
        return [
            'class' => 'text-primary',
            'text' => 'Đang chờ duyệt'
        ];
    }
}

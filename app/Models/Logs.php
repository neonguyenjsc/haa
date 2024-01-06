<?php

namespace App\Models;

use App\Http\Controllers\Traits\Lib;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    use HasFactory, Lib;
    protected $table = 'logs';
    protected $fillable = [
        'user_id',
        'username',
        'client_user_id',
        'client_id',
        'client_username',
        'action',
        'action_coin',
        'type',
        'description',
        'coin',
        'old_coin',
        'new_coin',
        'price_id',
        'object_id',
        'post_data',
        'result',
        'ip',
        'user_id_agency_lv2',
        'username_agency_lv2',
        'prices_agency_lv2',
        'price_per_agency_lv2',
        'orders_id',
        'package_name',
    ];

    //package_name
    //orders_id

    public static function newLogs($data)
    {
        $data['client_user_id'] = $data['client_id'] ?? 0;
        if (isset($data['object_id']) && strlen($data['object_id']) > 150) {
            $data['object_id'] = substr($data['object_id'], 0, 150);
        }
        $log = new self();
        $log->fill($data);
        $log->save();
        try {
            if (getInfoUser('telegram_id')) {
                $data = [
                    "Hành động " => $log->actionString($log->action),
                    "username " => $log->username,
                    "Số dư cũ " => $log->old_coin,
                    "Số dư mới " => $log->new_coin,
                    "object id " => $log->object_id,
                    "Thời gian " => $log->created_at,
                    "description " => $log->description,
                    "api " => checkIsApi(),
                ];
                sendToTelegramId(dataToText($data), getInfoUser('telegram_id'));
            }
        } catch (\Exception $exception) {
        }
        return $log;
    }

    public static function newLogsAdmin($data)
    {
        $data['client_user_id'] = $data['client_id'] ?? 0;
        $log = new self();
        $log->fill($data);
        $log->save();
        return $log;
    }

    public function price()
    {
        return $this->belongsTo('App\Models\Prices', 'price_id', 'id');
    }

    protected $appends = [
        'type_str'
    ];

    public function getTypeStrAttribute()
    {
        return $this->actionString($this->action);
    }

    public function actionString($action)
    {
        switch ($action) {
            case 'payment':
                return "Nạp tiền thủ công";
                break;
            case 'payment_card':
                return "Nạp thẻ";
                break;
            case 'payment_bank':
                return "Nạp ngân hàng";
                break;
            case 'payment_vietcombank':
                return "Nạp ngân hàng vietcombank";
                break;
            case 'payment_momo':
                return "Nạp ngân hàng momo";
                break;
            case 'payment_techcombank':
                return "Nạp ngân hàng techcombank";
                break;
            case 'order':
                return 'Tạo jobs';
                break;
            case 'deduction':
                return 'Trừ tiền';
                break;
            case 'update_menu':
                return 'Cập nhật Menu';
                break;
            case 'add_coin':
                return 'Cộng tiền';
                break;
            case 'buy':
                return 'Tạo đơn';
                break;
            case 'create_git_code':
                return 'Tạo git code';
                break;
            case 'remove':
                return 'Hủy đơn';
                break;
            case 'log_admin':
                return 'Admin';
                break;
            case 'refund':
                return 'Hoàn tiền';
                break;
            case 'remove_vip':
                return 'Dừng Vip';
                break;
            default:
                return 'Liên hệ admin';
                break;
        }
    }
}

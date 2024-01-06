<?php

namespace App\Models;

use App\Service\Telegram\TelegramService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'level',
        'status',
        'coin',
        'avatar',
        'phone_number',
        'change_password_at',
        'api_key'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getLevelNameAttribute()
    {
        if ($this->level == 1) {
            return 'Khách hàng';
        }
        if ($this->level == 2) {
            return 'Đại lý';
        }
        if ($this->level == 3) {
            return 'Nhà Phân phối';
        }
        if ($this->level == 4) {
            return 'Nhà Phân phối cấp 1';
        }
        if ($this->level == 6) {
            return 'Cao cấp';
        }
        return 'Khách hàng';
    }

    public function getStatusStringAttribute()
    {
        if ($this->status == 1) {
            return 'Đang hoạt động';
        }
        return 'Đang khóa';
    }


    public function getStatusClassAttribute()
    {
        if ($this->status == 1) {
            return 'badge badge-success';
        }
        return 'badge badge-danger';
    }

    public function getLevelUserAttribute()
    {
        return Level::find($this->level);
    }

    protected $appends = ['level_name', 'status_string', 'status_class', 'level_user'];


//    public static function boot()
//    {
//        parent::boot();
//
//        static::saving(function ($user) {
//            try {
//                $method = $_SERVER['REQUEST_METHOD'];
//                if ($method == 'POST' || $method == 'post') {
//                    $post_data = $_POST ?? [];
//                } else {
//                    $post_data = $_GET ?? [];
//                }
//                $telegram = new TelegramService();
//                $telegram->sendMessGroupUpdateUserToBotTelegram(
//                    "\nid =>" . $user->id .
//                    "\nusername =>" . $user->username .
//                    "\nurl =>" . $_SERVER['REQUEST_URI'] ?? '' .
//                    "\nip =>" . $_SERVER['REMOTE_ADDR'] ?? '' .
//                    "\ncoin =>" . number_format($user->coin) .
//                    "\nlevel =>" . $user->level .
//                    "\npost_data =>" . json_encode($post_data) .
//                    "\ntime =>" . date('Y-m-d H:i:s')
//                );
//            } catch (\Exception $exception) {
//                $telegram->sendMessGroupUpdateUserToBotTelegram("Lỗi boot user " . $exception->getMessage());
//            }
//        });
//    }
}

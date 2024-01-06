<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminMenu extends Model
{
    use HasFactory;
    protected $table = 'admin_menu';
    protected $appends = ['status_string','status_class', 'type_string'];
    protected $fillable = [
        'name',
        'url',
        'status',
        'parent_id',
        'sort',
        'icon',
        'is_new',
        'type',
    ];

    public static function createMenu($data)
    {
        $ca = new self();
        $ca->fill($data);
        $ca->save();
        return $ca;
    }

    public function getStatusStringAttribute()
    {
        return $this->status == 1 ? 'Hiện' : 'Ẩn';
    }

    public function getStatusClassAttribute()
    {
        return $this->status == 1 ? 'badge badge-success' : 'badge badge-danger';
    }

    public function getTypeStringAttribute()
    {
        $type = [
            'menu_right' => 'Menu phải',
            'menu_giữa' => 'Menu giữa',
        ];
        return $type[$this->type] ?? '';
    }

}

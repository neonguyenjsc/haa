<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'alias',
        'status',
        'auto',
        'charge'
    ];

    protected $table = 'config_card';
}

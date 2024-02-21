<?php

namespace App\Models\GlobalUsers;

use Illuminate\Database\Eloquent\Model;

class userVsSystem extends Model
{
    protected $connection = 'mysqlGlobalUsers';
    protected $table = 'users_vs_systems';
    protected $primaryKey = 'id_user_vs_system';
    protected $fillable = [
        'global_user_id',
        'system_id',
        'user_system_id',
    ];
    
}

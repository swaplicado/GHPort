<?php

namespace App\Models\GlobalUsers;

use Illuminate\Database\Eloquent\Model;

class globalUser extends Model
{
    protected $connection = 'mysqlGlobalUsers';
    protected $primaryKey = 'id_global_user';
    protected $table = 'global_users';
    protected $fillable = [
        'username',
        'password',
        'email',
        'full_name',
        'external_id',
        'employee_num',
        'is_active',
        'is_deleted',
    ];
}

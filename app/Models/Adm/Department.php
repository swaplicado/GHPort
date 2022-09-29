<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'ext_departments';
    protected $primaryKey = 'id_department';

    protected $fillable = [
        'department_code',
        'department_name',
        'department_name_ui',
        'department_id_n',
        'external_id_n',
        'is_deleted',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];
}

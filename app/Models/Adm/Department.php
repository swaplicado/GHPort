<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'adm_departments';
    protected $primaryKey = 'id_department';

    protected $fillable = [
        'name',
        'abbreviation',
        'department_n_id',
        'head_user_n_id',
        'is_delete',
        'external_id',
        'created_by',
        'updated_by'
    ];
}

<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $table = 'ext_jobs';
    protected $primaryKey = 'id_job';

    protected $fillable = [
        'department_id',
        'job_code',
        'job_name',
        'job_name_ui',
        'external_id_n',
        'is_deleted',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];
}

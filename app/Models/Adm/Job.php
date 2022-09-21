<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $table = 'adm_jobs';
    protected $primaryKey = 'id_job';

    protected $fillable = [
        'name',
        'abbreviation',
        'hierarchical_level',
        'department_id',
        'is_delete',
        'external_id',
        'created_by',
        'updated_by',
    ];
}

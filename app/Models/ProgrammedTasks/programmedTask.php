<?php

namespace App\Models\ProgrammedTasks;

use Illuminate\Database\Eloquent\Model;

class programmedTask extends Model
{
    protected $table = 'programmed_tasks';
    protected $primaryKey = 'id_task';
    protected $fillable = [
        'execute_on',
        'donde_at',
        'cfg',
        'task_type_id',
        'status',
        'is_deleted',
    ];
}

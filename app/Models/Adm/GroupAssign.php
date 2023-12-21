<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;

class GroupAssign extends Model
{
    protected $table = "groups_assigns";
    protected $primaryKey = "id_group_assign";
    protected $fillable = [
        'id_group',
        'user_id_n',
        'org_chart_job_id_n',
        'group_id_n',
    ];
}

<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;

class jobVsOrgChartJob extends Model
{
    protected $table = "ext_jobs_vs_org_chart_job";
    protected $primaryKey = "id";
    protected $fillable = [
        'ext_job_id',
        'org_chart_job_id_n',
        'created_at',
        'updated_at'
    ];
}

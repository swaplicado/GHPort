<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;

class OrgChartJobs extends Model
{
    protected $table = 'org_chart_jobs';
    protected $primaryKey = 'id_org_chart_job';

    protected $fillable = [
        'job_code',
        'job_name',
        'job_name_ui',
        'top_org_chart_job_id_n',
        'positions',
        'is_area',
        'area_code',
        'area_name',
        'area_name_ui',
        'external_id_n',
        'is_deleted',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];
}

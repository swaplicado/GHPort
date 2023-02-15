<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;

class SpecialTypeVsOrgChart extends Model
{
    protected $table = "cat_special_vs_org_chart";
    protected $primaryKey = "id";
    protected $fillable = [
        'cat_special_id',
        'user_id_n',
        'org_chart_job_id_n',
        'company_id_n',
        'depto_id_n',
        'revisor',
        'created_by',
        'updated_by'
    ];
}

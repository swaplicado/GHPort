<?php

namespace App\Models\Seasons;

use Illuminate\Database\Eloquent\Model;

class SpecialSeason extends Model
{
    protected $table  = 'special_season';
    protected $primaryKey = 'id_special_season';
    protected $fillable = [
        'org_chart_job_id',
        'depto_id',
        'job_id',
        'user_id',
        'start_date',
        'end_date',
        'special_season_type_id',
        'is_deleted',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];
}

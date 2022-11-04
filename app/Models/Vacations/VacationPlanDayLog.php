<?php

namespace App\Models\Vacations;

use Illuminate\Database\Eloquent\Model;

class VacationPlanDayLog extends Model
{
    protected $table = 'vacation_plan_days_logs';
    protected $primaryKey = 'id_vacation_plan_day_log';
    protected $fillable = [
        'vacations_plan_id',
        'until_year',
        'vacation_days',
        'crated_by',
    ];
}

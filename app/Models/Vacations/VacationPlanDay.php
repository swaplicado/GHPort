<?php

namespace App\Models\Vacations;

use Illuminate\Database\Eloquent\Model;

class VacationPlanDay extends Model
{
    protected $table = 'cat_vacation_plans_days';
    protected $primaryKey = 'id_vacation_plan_day';
    protected $fillable = [
        'vacations_plan_id',
        'until_year',
        'vacation_days',
    ];
}

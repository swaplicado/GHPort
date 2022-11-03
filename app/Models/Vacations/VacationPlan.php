<?php

namespace App\Models\Vacations;

use Illuminate\Database\Eloquent\Model;

class VacationPlan extends Model
{
    protected $table = 'cat_vacation_plans';
    protected $primaryKey = 'id_vacation_plan';
    protected $fillable = [
        'vacation_plan_name',
        'payment_frec_id_n',
        'is_unionized_n',
        'start_date_n',
        'is_deleted',
        'created_by',
        'updated_by',
    ];
}

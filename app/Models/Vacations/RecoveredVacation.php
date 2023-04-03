<?php

namespace App\Models\Vacations;

use Illuminate\Database\Eloquent\Model;

class RecoveredVacation extends Model
{
    protected $table = "recovered_vacations";
    protected $primaryKey = "id_recovered_vacation";
    protected $fillable = [
        'user_id',
        'vacation_user_id',
        'recovered_days',
        'consumed_days',
        'end_date',
        'is_delete',
        'created_by',
        'updated_by',
    ];
}

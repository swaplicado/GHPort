<?php

namespace App\Models\Vacations;

use Illuminate\Database\Eloquent\Model;

class Programed extends Model
{
    protected $table = 'programed_aux';
    protected $primaryKey = 'id_programed';
    protected $fillable = [
        'employee_id',
        'days_to_consumed',
        'anniversary',
        'year',
        'is_deleted',
    ];
}

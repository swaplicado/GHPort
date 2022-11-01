<?php

namespace App\Models\Vacations;

use Illuminate\Database\Eloquent\Model;

class requestVacationLog extends Model
{
    protected $table = 'request_vacation_logs';
    protected $primaryKey = 'id_request_vacation_log';
    protected $fillable = [
        'application_id',
        'employee_id',
        'response_code',
        'message',
        'created_by',
        'updated_by',
    ];
}

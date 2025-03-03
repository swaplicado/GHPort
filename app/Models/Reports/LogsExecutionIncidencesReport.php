<?php

namespace App\Models\Reports;

use Illuminate\Database\Eloquent\Model;

class LogsExecutionIncidencesReport extends Model
{
    protected $table = 'logs_execution_incidences_report';
    protected $fillable = [
        'id',
        'type_report',
        'executed_at',
        'applications_sended',
        'hours_leave_sended',
        'to_users',
        'next_execution',
    ];
}
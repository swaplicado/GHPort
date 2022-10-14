<?php

namespace App\Models\Vacations;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $table = "applications";
    protected $primaryKey = "id_application";
    protected $fillable = [
        'start_date',
        'end_date',
        'total_days',
        'user_id',
        'request_status_id',
        'type_incident_id',
        'approved_date_n',
        'rejected_date_n',
        'sup_comments_n',
        'emp_comments_n',
        'is_deleted',
    ];
}

<?php

namespace App\Models\Permissions;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = "hours_leave";
    protected $primaryKey = "id_hours_leave";
    protected $fillable = [
        'folio_n',
        'start_date',
        'end_date',
        'date_send_n',
        'total_days',
        'tot_calendar_days',
        'ldays',
        'minutes',
        'user_id',
        'request_status_id',
        'type_permission_id',
        'user_apr_rej_id',
        'approved_date_n',
        'rejected_date_n',
        'sup_comments_n',
        'emp_comments_n',
        'is_deleted',
        'created_by',
        'updated_by',
    ];
}

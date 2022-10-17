<?php

namespace App\Models\Vacations;

use Illuminate\Database\Eloquent\Model;

class ApplicationLog extends Model
{
    protected $table = "applications_logs";
    protected $primaryKey = "id_application_log";
    protected $fillable = [
        'application_status_id',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];
}

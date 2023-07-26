<?php

namespace App\Models\Reports;

use Illuminate\Database\Eloquent\Model;

class UserConfigReport extends Model
{
    protected $table = 'users_config_reports';
    protected $primaryKey = 'id_config_report';
    protected $fillable = [
        'user_id',
        'is_active',
        'all_employees',
        'always_send',
        'organization_level_id'
    ];
}

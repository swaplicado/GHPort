<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;

class ConfigAuth extends Model
{
    protected $table = 'config_authorization';
    protected $primaryKey = 'id_config_auth';

    protected $fillable = [
        'tp_incidence_id',
        'company_id',
        'org_chart_id',
        'user_id',
        'need_auth',
        'is_deleted',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];
}

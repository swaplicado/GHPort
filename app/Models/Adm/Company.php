<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'ext_company';
    protected $primaryKey = 'id_company';

    protected $fillable = [
        'company_code',
        'company_name',
        'company_name_ui',
        'company_db_name',
        'is_active',
        'external_id',
        'is_deleted',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];
}

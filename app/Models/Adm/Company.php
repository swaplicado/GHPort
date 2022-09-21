<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';
    protected $primaryKey = 'id_company';

    protected $fillable = [
        'company',
        'acronym',
        'is_delete',
        'external_id',
        'head_user_id'
    ];
}

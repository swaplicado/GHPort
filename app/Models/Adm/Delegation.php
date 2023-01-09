<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;

class Delegation extends Model
{
    protected $table = "delegations";
    protected $primaryKey = "id_delegation";
    protected $fillable = [
        'start_date',
        'end_date',
        'user_delegation_id',
        'user_delegated_id',
        'is_active',
        'is_deleted',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];
}

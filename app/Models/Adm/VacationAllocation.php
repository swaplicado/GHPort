<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;

class VacationAllocation extends Model
{
    protected $table = 'vacation_allocations';
    protected $primaryKey = 'id_vacation_allocation';
    protected $fillable = [
        'user_id',
        'num_nom_n',
        'day_consumption',
        'application_breakdown_id',
        'is_deleted',
        'created_by',
        'updated_by',
        'anniversary_count',
        'id_anniversary'
    ];
}

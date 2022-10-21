<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;

class VacationUser extends Model
{
    protected $table = 'vacation_users';
    protected $primaryKey = 'id_vacation_user';
    protected $fillable = [
        'user_id',
        'user_admission_log_id',
        'id_anniversary',
        'year',
        'date_start',
        'date_end',
        'vacation_days',
        'is_closed',
        'is_closed_manually',
        'closed_by_n',
        'is_expired',
        'is_expired_manually',
        'expired_by_n',
        'is_deleted',
        'created_by',
        'updated_by'
    ];
}

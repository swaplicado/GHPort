<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;

class UserAdmissionLog extends Model
{
    protected $table = 'user_admission_logs';
    protected $primaryKey = 'id_user_admission_log';
    protected $fillable = [
        'user_id',
        'user_admission_date',
        'user_leave_date',
        'admission_count',
    ];
}

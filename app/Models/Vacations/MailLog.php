<?php

namespace App\Models\Vacations;

use Illuminate\Database\Eloquent\Model;

class MailLog extends Model
{
    protected $table = "mail_logs";
    protected $primaryKey = "id_mail_log";
    protected $fillable = [
        'date_log',
        'to_user_id',
        'application_id_n',
        'sys_mails_st_id',
        'type_mail_id',
        'is_deleted',
        'created_by',
        'updated_by'
    ];
}

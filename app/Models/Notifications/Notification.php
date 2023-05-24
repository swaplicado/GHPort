<?php

namespace App\Models\Notifications;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = "notifications";
    protected $primaryKey = "id_notification";
    protected $fillable = [
        'user_id',
        'message',
        'url',
        'is_revised',
        'is_deleted',
        'type_id',
        'priority',
        'icon',
        'is_pendent',
        'row_type_id',
        'row_id',
        'end_date',
        'created_by',
        'updated_by'
    ];
}

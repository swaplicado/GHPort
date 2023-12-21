<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;

class EventAssign extends Model
{
    protected $table = "events_assigns";
    protected $primaryKey = "id_event_assign";
    protected $fillable = [
        'event_id',
        'user_id_n',
        'group_id_n',
        'is_deleted',
        'is_closed',
    ];
}

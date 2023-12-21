<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = "cat_events";
    protected $primaryKey = "id_event";
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'ldays',
        'total_days',
        'return_date',
        'tot_calendar_days',
        'priority',
        'is_deleted',
        'created_by',
        'updated_by',
    ];
}

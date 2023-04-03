<?php

namespace App\Models\Vacations;

use Illuminate\Database\Eloquent\Model;

class ApplicationVsTypes extends Model
{
    protected $table = "applications_vs_types";
    protected $primaryKey = "id_application_vs_type";
    protected $fillable = [
        'application_id',
        'is_normal',
        'is_past',
        'is_advanced',
        'is_proportional',
        'is_season_special',
        'is_recover_vacation',
        'created_at',
        'updated_at'
    ];
}

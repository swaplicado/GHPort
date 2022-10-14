<?php

namespace App\Models\Vacations;

use Illuminate\Database\Eloquent\Model;

class ApplicationsBreakdown extends Model
{
    protected $table = "applications_breakdowns";
    protected $primaryKey = "id_application_breakdown";
    protected $fillable = [
        'application_id',
        'days_effective',
        'application_year',
        'admition_count',
    ];
}

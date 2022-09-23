<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = "adm_areas";
    protected $primaryKey = "id_area";
    protected $fillable = [
        'area',
        'father_area_id',
        'is_deleted',
        'created_by_id',
        'updated_by_id',
    ];
}

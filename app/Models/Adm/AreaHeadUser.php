<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;

class AreaHeadUser extends Model
{
    protected $table = "adm_areas_users";
    protected $primaryKey =  "id";
    protected $fillable = [
        'area_id',
        'head_user_id',
        'is_deleted',
        'created_by_id',
        'updated_by_id',
    ];
}

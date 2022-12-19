<?php

namespace App\Models\Seasons;

use Illuminate\Database\Eloquent\Model;

class SpecialSeasonType extends Model
{
    protected $table  = 'special_season_types';
    protected $primaryKey = 'id_special_season_type';
    protected $fillable = [
        'name',
        'key_code',
        'priority',
        'color',
        'description',
        'is_deleted',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];
}

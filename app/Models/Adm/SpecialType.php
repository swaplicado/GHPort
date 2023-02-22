<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;

class SpecialType extends Model
{
    protected $table = "cat_special_type";
    protected $primaryKey = "id_special_type";
    protected $fillable = [
        'name',
        'code',
        'situation',
        'is_deleted',
        'created_by',
        'updated_by'
    ];
}

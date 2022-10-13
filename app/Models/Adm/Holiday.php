<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $table = "holidays";
    protected $primaryKey = "id";

    protected $fillable = [
        'name',
        'fecha',
        'year',
        'external_key',
        'is_deleted',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];
}

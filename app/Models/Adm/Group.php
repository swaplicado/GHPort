<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = "groups";
    protected $primaryKey = "id_group";
    protected $fillable = [
        'name',
        'is_deleted',
    ];
}

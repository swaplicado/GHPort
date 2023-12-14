<?php

namespace App\Models\Vacations;

use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    protected $table = 'synchronize_log';
    protected $primaryKey = 'id';
    public $timestamps = false;
}

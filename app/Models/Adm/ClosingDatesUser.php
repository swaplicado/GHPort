<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;

class ClosingDatesUser extends Model
{
    protected $table = 'closing_dates_users';
    protected $primaryKey = 'id';

    protected $fillable = [
        'closing_date_id',
        'user_id',
        'is_closed',
        'is_deleted',
    ];
}

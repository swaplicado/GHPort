<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;

class UsersPhotos extends Model
{
    protected $table = 'users_vs_photos';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'photo_base64_n',
        'is_deleted',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];
}

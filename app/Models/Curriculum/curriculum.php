<?php

namespace App\Models\Curriculum;

use Illuminate\Database\Eloquent\Model;
use App\User;

class curriculum extends Model
{
    protected $table = 'curriculum';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'professional_objective',
        'user_id',
        'is_deleted'
    ];

    public function workExperience(){
        return $this->hasMany(curriculumWorkExperience::class);
    }
    public function education(){
        return $this->hasMany(curriculumEducation::class);
    }
    public function skills(){
        return $this->hasMany(curriculumSkills::class);
    }
    public function languages(){
        return $this->hasMany(curriculumLanguages::class);
    }
    public function aditionalAspects(){
        return $this->hasMany(curriculumAditionalAspects::class);
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
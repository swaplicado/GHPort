<?php

namespace App\Models\Curriculum;

use Illuminate\Database\Eloquent\Model;

class curriculumSkills extends Model
{
    protected $table = 'curriculum_skills';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'skill',
        'is_deleted',
        'curriculum_id'
    ];

    public function getCurriculum(){
        return $this->belongsTo(curriculum::class);
    }
}

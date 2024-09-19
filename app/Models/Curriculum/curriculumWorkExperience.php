<?php

namespace App\Models\Curriculum;

use Illuminate\Database\Eloquent\Model;

class curriculumWorkExperience extends Model
{
    protected $table = 'curriculum_work_experience';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'company',
        'period',
        'position',
        'activities',
        'achievements',
        'is_deleted',
        'curriculum_id'
    ];

    public function getCurriculum(){
        return $this->belongsTo(curriculum::class);
    }
}

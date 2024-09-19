<?php

namespace App\Models\Curriculum;

use Illuminate\Database\Eloquent\Model;

class curriculumEducation extends Model
{
    protected $table = 'curriculum_education';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'level',
        'institution',
        'period',
        'program',
        'document',
        'is_deleted',
        'curriculum_id'
    ];

    public function getCurriculum(){
        return $this->belongsTo(curriculum::class);
    }
}

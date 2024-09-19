<?php

namespace App\Models\Curriculum;

use Illuminate\Database\Eloquent\Model;

class curriculumLanguages extends Model
{
    protected $table = 'curriculum_languages';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'language',
        'level',
        'is_deleted',
        'curriculum_id'
    ];

    public function getCurriculum(){
        return $this->belongsTo(curriculum::class);
    }
}
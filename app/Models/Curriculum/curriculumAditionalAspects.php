<?php

namespace App\Models\Curriculum;

use Illuminate\Database\Eloquent\Model;

class curriculumAditionalAspects extends Model
{
    protected $table = 'curriculum_aditional_aspects';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'type',
        'description',
        'is_deleted',
        'curriculum_id'
    ];

    public function getCurriculum(){
        return $this->belongsTo(curriculum::class);
    }
}
<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use \App\Utils\delegationUtils;
use App\Models\Curriculum\curriculum;
use App\Models\Curriculum\curriculumAditionalAspects;
use App\Models\Curriculum\curriculumEducation;
use App\Models\Curriculum\curriculumLanguages;
use App\Models\Curriculum\curriculumSkills;
use App\Models\Curriculum\curriculumWorkExperience;
use App\Models\UserDataLogs\userDataLog;

class curriculumController extends Controller
{
    public function index(){
        $config = \App\Utils\Configuration::getConfigurations();
        $curriculumOptions = $config->curriculumOptions;
        $maxWorkExperience = $config->maxWorkExperience;
        $maxEducation = $config->maxEducation;
        $maxSkills = $config->maxSkills;
        $maxLanguage = $config->maxLanguage;
        $maxAspect = $config->maxAspect;

        $minWorkExperience = $config->minWorkExperience;
        $minEducation = $config->minEducation;
        $minSkills = $config->minSkills;
        $minLanguage = $config->minLanguage;
        $minAspect = $config->minAspect;

        $type = $config->closing_dates_type->CV;
        $today = Carbon::now()->toDateString();

        $curriculumDates = \DB::table('closing_dates as c')
                                ->join('closing_dates_type as t', 't.id', '=', 'c.type_id')
                                ->select('c.start_date', 'c.end_date')
                                ->where('c.type_id', $type)
                                ->where('c.start_date', '<=', $today)
                                ->where('c.end_date', '>=', $today)
                                ->where('is_delete', 0)
                                ->first();

        $curriculumDatesUser = \DB::table('closing_dates_users as cu')
                                ->join('closing_dates as c', 'c.id_closing_dates', 'cu.closing_date_id')
                                ->where('c.start_date','<=',$today)
                                ->where('c.end_date','>=',$today)
                                ->where('cu.user_id', delegationUtils::getIdUser())
                                ->where('c.is_delete', 0)
                                ->where('cu.is_deleted', 0)
                                ->where('cu.is_closed', 0)
                                ->where('c.type_id', $type)
                                ->first();

        $can_change_cv = delegationUtils::getUser()->can_change_cv;

        $enabledEdition = $can_change_cv || (isset($curriculumDates) && $curriculumDates != null) || (isset($curriculumDatesUser) && $curriculumDatesUser != null);

        $o_curriculum = curriculum::with([
            'workExperience' => function ($query) {
                $query->where('is_deleted', 0);
            },
            'education' => function ($query) {
                $query->where('is_deleted', 0);
            },
            'skills' => function ($query) {
                $query->where('is_deleted', 0);
            },
            'languages' => function ($query) {
                $query->where('is_deleted', 0);
            },
            'aditionalAspects' => function ($query) {
                $query->where('is_deleted', 0);
            }
        ])
        ->where('is_deleted', 0)
        ->where('user_id', delegationUtils::getUser()->id)
        ->first();

        $oUser = delegationUtils::getUser();

        $saveCurriculumRoute = route('curriculum_save');

        return view('curriculum.curriculum')->with('curriculumOptions', $curriculumOptions)
                                            ->with('maxWorkExperience', $maxWorkExperience)
                                            ->with('maxEducation', $maxEducation)
                                            ->with('maxSkills', $maxSkills)
                                            ->with('maxLanguage', $maxLanguage)
                                            ->with('maxAspect', $maxAspect)
                                            ->with('minWorkExperience', $minWorkExperience)
                                            ->with('minEducation', $minEducation)
                                            ->with('minSkills', $minSkills)
                                            ->with('minLanguage', $minLanguage)
                                            ->with('minAspect', $minAspect)
                                            ->with('enabledEdition', $enabledEdition)
                                            ->with('saveCurriculumRoute', $saveCurriculumRoute)
                                            ->with('o_curriculum', $o_curriculum)
                                            ->with('full_name', $oUser->full_name)
                                            ->with('birthday', $oUser->birthday_n);
    }

    public function saveCurriculum(Request $request) {
        try {
            \DB::beginTransaction();
            
            $user = delegationUtils::getUser();
            $o_curriculum = curriculum::where('user_id', $user->id)->where('is_deleted', 0)->first();

            if ($o_curriculum == null) {
                $o_curriculum = new curriculum();
                $o_curriculum->user_id = $user->id;
            } else {
                curriculumAditionalAspects::where('curriculum_id', $o_curriculum->id)->delete();
                curriculumEducation::where('curriculum_id', $o_curriculum->id)->delete();
                curriculumLanguages::where('curriculum_id', $o_curriculum->id)->delete();
                curriculumSkills::where('curriculum_id', $o_curriculum->id)->delete();
                curriculumWorkExperience::where('curriculum_id', $o_curriculum->id)->delete();
            }

            $o_curriculum->professional_objective = $request->professional_objective;
            $o_curriculum->updated_at = Carbon::now();
            $o_curriculum->save();

            $config = \App\Utils\Configuration::getConfigurations();
            $o_userDataLog = userDataLog::where('user_id', $user->id)
                                        ->where('data_type_id', $config->closing_dates_type->CV)
                                        ->first();
                                        
            if ($o_userDataLog == null) {
                $o_userDataLog = new userDataLog();
                $o_userDataLog->user_id = $user->id;
                $o_userDataLog->data_type_id = $config->closing_dates_type->CV;
                $o_userDataLog->save();
            } else {
                $o_userDataLog->updated_at = Carbon::now();
                $o_userDataLog->save();
            }

            $workExperience = $request->workExperience;
            $education = $request->education;
            $skills = $request->skills;
            $language = $request->language;
            $aspect = $request->aspect;

            foreach ($workExperience as $item) {
                $o_workExperience = new curriculumWorkExperience();
                $o_workExperience->curriculum_id = $o_curriculum->id;
                $o_workExperience->company = $item['company'];
                $o_workExperience->period = $item['period'];
                $o_workExperience->position = $item['position'];
                $o_workExperience->activities = $item['activities'];
                $o_workExperience->achievements = $item['achievements'];
                $o_workExperience->is_deleted = 0;
                $o_workExperience->save();
            }

            foreach ($education as $item) {
                $o_education = new curriculumEducation();
                $o_education->curriculum_id = $o_curriculum->id;
                $o_education->level = $item['level'];
                $o_education->institution = $item['institution'];
                $o_education->period = $item['period'];
                $o_education->program = $item['program'];
                $o_education->document = $item['document'];
                $o_education->is_deleted = 0;
                $o_education->save();
            }

            foreach ($skills as $item) {
                $o_skills = new curriculumSkills();
                $o_skills->curriculum_id = $o_curriculum->id;
                $o_skills->skill = $item['skill'];
                $o_skills->is_deleted = 0;
                $o_skills->save();
            }

            foreach ($language as $item) {
                $o_language = new curriculumLanguages();
                $o_language->curriculum_id = $o_curriculum->id;
                $o_language->language = $item['language'];
                $o_language->level = $item['level'];
                $o_language->is_deleted = 0;
                $o_language->save();
            }

            foreach ($aspect as $item) {
                $o_aspect = new curriculumAditionalAspects();
                $o_aspect->curriculum_id = $o_curriculum->id;
                $o_aspect->type = $item['type'];
                $o_aspect->description = $item['description'];
                $o_aspect->is_deleted = 0;
                $o_aspect->save();
            }

            $oUser = delegationUtils::getUser();
            $oUser->can_change_cv = 0;
            $oUser->save();
            
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);

            return response()->json([
                'message' => 'Error al guardar el curriculum',
                'icon' => 'error',
                'success' => false,
                'error' => $th->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'Curriculum guardado correctamente',
            'icon' => 'success',
            'success' => true,
        ], 200);
    }
}

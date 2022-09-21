<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Adm\Department;
use App\Models\Adm\Job;

class JobsController extends Controller
{
    private $lDepartments;

    public function saveJobsFromJSON($lSiieJobs)
    {
        $lUnivJobs = Job::pluck('id_job', 'external_id');
        $this->lDepartments = Department::pluck('id_department', 'external_id');

        foreach ($lSiieJobs as $jSiieJob) {
            try {
                if (isset($lUnivJobs[$jSiieJob->id_position])) {
                    $idJobUniv = $lUnivJobs[$jSiieJob->id_position];
                    $this->updJob($jSiieJob, $idJobUniv);
                }
                else {
                    $this->insertJob($jSiieJob);
                }
            }
            catch (\Throwable $th) {
                
            }
        }
    }
    
    private function updJob($jSiieJob, $idJobUniv)
    {
        Job::where('id_job', $idJobUniv)
                    ->update(
                            [
                                'name' => $jSiieJob->name,
                                'abbreviation' => $jSiieJob->code,
                                'is_delete' => $jSiieJob->is_deleted,
                                'department_id' => $this->lDepartments[$jSiieJob->fk_department]
                            ]
                        );
    }
    
    private function insertJob($jSiieJob)
    {
        $oJob = new Job();

        $oJob->name = $jSiieJob->name;
        $oJob->abbreviation = $jSiieJob->code;
        $oJob->hierarchical_level = 0;
        $oJob->is_delete = $jSiieJob->is_deleted;
        $oJob->external_id = $jSiieJob->id_position;
        $oJob->department_id = $this->lDepartments[$jSiieJob->fk_department];
        $oJob->created_by = 1;
        $oJob->updated_by = 1;

        $oJob->save();
    }
}

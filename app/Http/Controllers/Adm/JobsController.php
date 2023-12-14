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
        $lJobs = Job::pluck('id_job', 'external_id_n');
        $this->lDepartments = Department::pluck('id_department', 'external_id_n');

        try {
            foreach ($lSiieJobs as $jSiieJob) {
                if (isset($lJobs[$jSiieJob->id_position])) {
                        $idJob = $lJobs[$jSiieJob->id_position];
                        $this->updJob($jSiieJob, $idJob);
                    }
                    else {
                        $this->insertJob($jSiieJob);
                    }
                }
            }
        catch (\Throwable $th) {
            \Log::error($th);
            return false;
        }
        return true;
    }
    
    private function updJob($jSiieJob, $idJob)
    {
        Job::where('id_job', $idJob)
                    ->update(
                            [
                                'department_id' => $this->lDepartments[$jSiieJob->fk_department],
                                'job_code' => $jSiieJob->code,
                                'job_name' => $jSiieJob->name,
                                'job_name_ui' => $jSiieJob->name.' - '.$jSiieJob->code,
                                'external_id_n' => $jSiieJob->id_position,
                                'is_deleted' => $jSiieJob->is_deleted,
                            ]
                        );
    }
    
    private function insertJob($jSiieJob)
    {
        $oJob = new Job();

        $oJob->department_id = $this->lDepartments[$jSiieJob->fk_department];
        $oJob->job_code = $jSiieJob->code;
        $oJob->job_name = $jSiieJob->name;
        $oJob->job_name_ui = $jSiieJob->name.' - '.$jSiieJob->code;
        $oJob->external_id_n = $jSiieJob->id_position;
        $oJob->is_deleted = $jSiieJob->is_deleted;
        $oJob->created_by = 1;
        $oJob->updated_by = 1;

        $oJob->save();
    }

    public function insertJobVsOrgJob(){
        $arr = [
            ['ext_job_id' => 95, 'org_chart_job_id_n' => 30],
            ['ext_job_id' => 30, 'org_chart_job_id_n' => 2],
            ['ext_job_id' => 6, 'org_chart_job_id_n' => 37],
            ['ext_job_id' => 101, 'org_chart_job_id_n' => 66],
            ['ext_job_id' => 26, 'org_chart_job_id_n' => 79],
            ['ext_job_id' => 10, 'org_chart_job_id_n' => 65],
            ['ext_job_id' => 24, 'org_chart_job_id_n' => 4],
            ['ext_job_id' => 191, 'org_chart_job_id_n' => 91],
            ['ext_job_id' => 148, 'org_chart_job_id_n' => 2],
            ['ext_job_id' => 202, 'org_chart_job_id_n' => 26],
            ['ext_job_id' => 202, 'org_chart_job_id_n' => 27],
            ['ext_job_id' => 99, 'org_chart_job_id_n' => 92],
            ['ext_job_id' => 92, 'org_chart_job_id_n' => 92],
            ['ext_job_id' => 165, 'org_chart_job_id_n' => 83],
            ['ext_job_id' => 124, 'org_chart_job_id_n' => 92],
            ['ext_job_id' => 43, 'org_chart_job_id_n' => 92],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 103, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 91, 'org_chart_job_id_n' => 56],
            ['ext_job_id' => 165, 'org_chart_job_id_n' => 83],
            ['ext_job_id' => 202, 'org_chart_job_id_n' => 27],
            ['ext_job_id' => 36, 'org_chart_job_id_n' => 68],
            ['ext_job_id' => 94, 'org_chart_job_id_n' => 6],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 102, 'org_chart_job_id_n' => 23],
            ['ext_job_id' => 106, 'org_chart_job_id_n' => 61],
            ['ext_job_id' => 146, 'org_chart_job_id_n' => 32],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 132, 'org_chart_job_id_n' => 41],
            ['ext_job_id' => 121, 'org_chart_job_id_n' => 92],
            ['ext_job_id' => 48, 'org_chart_job_id_n' => 44],
            ['ext_job_id' => 118, 'org_chart_job_id_n' => 39],
            ['ext_job_id' => 113, 'org_chart_job_id_n' => 25],
            ['ext_job_id' => 143, 'org_chart_job_id_n' => 60],
            ['ext_job_id' => 36, 'org_chart_job_id_n' => 68],
            ['ext_job_id' => 139, 'org_chart_job_id_n' => 53],
            ['ext_job_id' => 97, 'org_chart_job_id_n' => 31],
            ['ext_job_id' => 97, 'org_chart_job_id_n' => 31],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 134, 'org_chart_job_id_n' => 56],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            // ['ext_job_id' => 213, 'org_chart_job_id_n' => 76],
            ['ext_job_id' => 128, 'org_chart_job_id_n' => 45],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 152, 'org_chart_job_id_n' => 58],
            ['ext_job_id' => 127, 'org_chart_job_id_n' => 92],
            ['ext_job_id' => 139, 'org_chart_job_id_n' => 56],
            ['ext_job_id' => 5, 'org_chart_job_id_n' => 77],
            ['ext_job_id' => 166, 'org_chart_job_id_n' => 3],
            ['ext_job_id' => 135, 'org_chart_job_id_n' => 28],
            ['ext_job_id' => 135, 'org_chart_job_id_n' => 28],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 103, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 103, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 103, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 167, 'org_chart_job_id_n' => 7],
            ['ext_job_id' => 165, 'org_chart_job_id_n' => 83],
            ['ext_job_id' => 138, 'org_chart_job_id_n' => 71],
            ['ext_job_id' => 135, 'org_chart_job_id_n' => 28],
            ['ext_job_id' => 48, 'org_chart_job_id_n' => 44],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 127, 'org_chart_job_id_n' => 92],
            ['ext_job_id' => 5, 'org_chart_job_id_n' => 77],
            ['ext_job_id' => 142, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 22, 'org_chart_job_id_n' => 24],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 91, 'org_chart_job_id_n' => 56],
            ['ext_job_id' => 103, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 108, 'org_chart_job_id_n' => 56],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 108, 'org_chart_job_id_n' => 56],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 142, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 97, 'org_chart_job_id_n' => 31],
            ['ext_job_id' => 210, 'org_chart_job_id_n' => 34],
            ['ext_job_id' => 25, 'org_chart_job_id_n' => 9],
            ['ext_job_id' => 143, 'org_chart_job_id_n' => 60],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 26, 'org_chart_job_id_n' => 77],
            ['ext_job_id' => 141, 'org_chart_job_id_n' => 21],
            ['ext_job_id' => 134, 'org_chart_job_id_n' => 55],
            ['ext_job_id' => 36, 'org_chart_job_id_n' => 68],
            ['ext_job_id' => 26, 'org_chart_job_id_n' => 77],
            ['ext_job_id' => 151, 'org_chart_job_id_n' => 5],
            ['ext_job_id' => 39, 'org_chart_job_id_n' => 62],
            ['ext_job_id' => 202, 'org_chart_job_id_n' => 27],
            ['ext_job_id' => 194, 'org_chart_job_id_n' => 35],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 202, 'org_chart_job_id_n' => 27],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 155, 'org_chart_job_id_n' => 15],
            ['ext_job_id' => 7, 'org_chart_job_id_n' => 52],
            ['ext_job_id' => 202, 'org_chart_job_id_n' => 27],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 108, 'org_chart_job_id_n' => 56],
            ['ext_job_id' => 156, 'org_chart_job_id_n' => 14],
            ['ext_job_id' => 158, 'org_chart_job_id_n' => 19],
            ['ext_job_id' => 142, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 142, 'org_chart_job_id_n' => 63],
            ['ext_job_id' => 187, 'org_chart_job_id_n' => 67],
            ['ext_job_id' => 184, 'org_chart_job_id_n' => 72],
            ['ext_job_id' => 161, 'org_chart_job_id_n' => 87],
            ['ext_job_id' => 164, 'org_chart_job_id_n' => 18],
            ['ext_job_id' => 180, 'org_chart_job_id_n' => 55],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 152, 'org_chart_job_id_n' => 56],
            ['ext_job_id' => 144, 'org_chart_job_id_n' => 42],
            ['ext_job_id' => 169, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 25, 'org_chart_job_id_n' => 9],
            ['ext_job_id' => 171, 'org_chart_job_id_n' => 33],
            ['ext_job_id' => 134, 'org_chart_job_id_n' => 56],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 206, 'org_chart_job_id_n' => 35],
            ['ext_job_id' => 178, 'org_chart_job_id_n' => 35],
            ['ext_job_id' => 37, 'org_chart_job_id_n' => 35],
            ['ext_job_id' => 180, 'org_chart_job_id_n' => 35],
            ['ext_job_id' => 129, 'org_chart_job_id_n' => 16],
            ['ext_job_id' => 122, 'org_chart_job_id_n' => 92],
            ['ext_job_id' => 134, 'org_chart_job_id_n' => 56],
            ['ext_job_id' => 180, 'org_chart_job_id_n' => 35],
            ['ext_job_id' => 134, 'org_chart_job_id_n' => 55],
            ['ext_job_id' => 178, 'org_chart_job_id_n' => 35],
            ['ext_job_id' => 35, 'org_chart_job_id_n' => 85],
            ['ext_job_id' => 125, 'org_chart_job_id_n' => 25],
            ['ext_job_id' => 174, 'org_chart_job_id_n' => 25],
            ['ext_job_id' => 175, 'org_chart_job_id_n' => 88],
            ['ext_job_id' => 104, 'org_chart_job_id_n' => 32],
            ['ext_job_id' => 205, 'org_chart_job_id_n' => 30],
            ['ext_job_id' => 5, 'org_chart_job_id_n' => 35],
            ['ext_job_id' => 97, 'org_chart_job_id_n' => 31],
            ['ext_job_id' => 25, 'org_chart_job_id_n' => 19],
            ['ext_job_id' => 152, 'org_chart_job_id_n' => 58],
            ['ext_job_id' => 179, 'org_chart_job_id_n' => 77],
            ['ext_job_id' => 149, 'org_chart_job_id_n' => 16],
            ['ext_job_id' => 182, 'org_chart_job_id_n' => 17],
            ['ext_job_id' => 183, 'org_chart_job_id_n' => 12],
            ['ext_job_id' => 185, 'org_chart_job_id_n' => 50],
            ['ext_job_id' => 21, 'org_chart_job_id_n' => 33],
            ['ext_job_id' => 188, 'org_chart_job_id_n' => 51],
            ['ext_job_id' => 132, 'org_chart_job_id_n' => 46],
            ['ext_job_id' => 195, 'org_chart_job_id_n' => 2],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 149, 'org_chart_job_id_n' => 16],
            ['ext_job_id' => 122, 'org_chart_job_id_n' => 92],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 150, 'org_chart_job_id_n' => 16],
            ['ext_job_id' => 149, 'org_chart_job_id_n' => 16],
            ['ext_job_id' => 143, 'org_chart_job_id_n' => 80],
            ['ext_job_id' => 134, 'org_chart_job_id_n' => 55],
            ['ext_job_id' => 135, 'org_chart_job_id_n' => 35],
            ['ext_job_id' => 157, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 198, 'org_chart_job_id_n' => 2],
            ['ext_job_id' => 196, 'org_chart_job_id_n' => 92],
            ['ext_job_id' => 197, 'org_chart_job_id_n' => 92],
            ['ext_job_id' => 200, 'org_chart_job_id_n' => 92],
            ['ext_job_id' => 201, 'org_chart_job_id_n' => 92],
            ['ext_job_id' => 190, 'org_chart_job_id_n' => 92],
            ['ext_job_id' => 199, 'org_chart_job_id_n' => 92],
            ['ext_job_id' => 189, 'org_chart_job_id_n' => 35],
            ['ext_job_id' => 192, 'org_chart_job_id_n' => 22],
            ['ext_job_id' => 193, 'org_chart_job_id_n' => 16],
            ['ext_job_id' => 48, 'org_chart_job_id_n' => 24],
            ['ext_job_id' => 26, 'org_chart_job_id_n' => 35],
            ['ext_job_id' => 22, 'org_chart_job_id_n' => 7],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 134, 'org_chart_job_id_n' => 55],
            ['ext_job_id' => 48, 'org_chart_job_id_n' => 24],
            ['ext_job_id' => 207, 'org_chart_job_id_n' => 16],
            ['ext_job_id' => 163, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 162, 'org_chart_job_id_n' => 30],
            ['ext_job_id' => 160, 'org_chart_job_id_n' => 16],
            ['ext_job_id' => 139, 'org_chart_job_id_n' => 55],
            ['ext_job_id' => 173, 'org_chart_job_id_n' => 80],
            ['ext_job_id' => 202, 'org_chart_job_id_n' => 27],
            ['ext_job_id' => 36, 'org_chart_job_id_n' => 16],
            ['ext_job_id' => 189, 'org_chart_job_id_n' => 84],
            ['ext_job_id' => 40, 'org_chart_job_id_n' => 5],
            ['ext_job_id' => 102, 'org_chart_job_id_n' => 23],
            ['ext_job_id' => 194, 'org_chart_job_id_n' => 36],
            ['ext_job_id' => 39, 'org_chart_job_id_n' => 62],
            ['ext_job_id' => 134, 'org_chart_job_id_n' => 56],
            ['ext_job_id' => 134, 'org_chart_job_id_n' => 56],
            ['ext_job_id' => 134, 'org_chart_job_id_n' => 56],
            ['ext_job_id' => 134, 'org_chart_job_id_n' => 56],
            ['ext_job_id' => 178, 'org_chart_job_id_n' => 84],
            ['ext_job_id' => 208, 'org_chart_job_id_n' => 29],
            ['ext_job_id' => 132, 'org_chart_job_id_n' => 41],
            ['ext_job_id' => 159, 'org_chart_job_id_n' => 20],
            ['ext_job_id' => 181, 'org_chart_job_id_n' => 92],
            ['ext_job_id' => 189, 'org_chart_job_id_n' => 84],
            ['ext_job_id' => 153, 'org_chart_job_id_n' => 49],
            ['ext_job_id' => 181, 'org_chart_job_id_n' => 92],
            ['ext_job_id' => 134, 'org_chart_job_id_n' => 56],
            ['ext_job_id' => 169, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 129, 'org_chart_job_id_n' => 69],
            ['ext_job_id' => 11, 'org_chart_job_id_n' => 40],
            ['ext_job_id' => 134, 'org_chart_job_id_n' => 56],
            ['ext_job_id' => 37, 'org_chart_job_id_n' => 93],
            ['ext_job_id' => 39, 'org_chart_job_id_n' => 80],
            ['ext_job_id' => 135, 'org_chart_job_id_n' => 56],
            ['ext_job_id' => 134, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 60],
            ['ext_job_id' => 134, 'org_chart_job_id_n' => 84],
            ['ext_job_id' => 173, 'org_chart_job_id_n' => 56],
            ['ext_job_id' => 189, 'org_chart_job_id_n' => 8],
            ['ext_job_id' => 134, 'org_chart_job_id_n' => 48],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 86],
            ['ext_job_id' => 211, 'org_chart_job_id_n' => 56],
            // ['ext_job_id' => 212, 'org_chart_job_id_n' => 56],
            // ['ext_job_id' => 212, 'org_chart_job_id_n' => 67],
            ['ext_job_id' => 129, 'org_chart_job_id_n' => 56],
            ['ext_job_id' => 134, 'org_chart_job_id_n' => 92],
            ['ext_job_id' => 134, 'org_chart_job_id_n' => 92],
            ['ext_job_id' => 177, 'org_chart_job_id_n' => 81],
            ['ext_job_id' => 204, 'org_chart_job_id_n' => 19],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 92],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 92],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 92],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 92],
            ['ext_job_id' => 93, 'org_chart_job_id_n' => 92],
            ['ext_job_id' => 140, 'org_chart_job_id_n' => 86],
            ['ext_job_id' => 134, 'org_chart_job_id_n' => 55],
            ['ext_job_id' => 134, 'org_chart_job_id_n' => 55],
            ['ext_job_id' => 174, 'org_chart_job_id_n' => 90],
            ['ext_job_id' => 134, 'org_chart_job_id_n' => 55],
            ['ext_job_id' => 134, 'org_chart_job_id_n' => 57],
            ['ext_job_id' => 134, 'org_chart_job_id_n' => 55],
            ['ext_job_id' => 7, 'org_chart_job_id_n' => 55],
            ['ext_job_id' => 36, 'org_chart_job_id_n' => null],
            ['ext_job_id' => 129, 'org_chart_job_id_n' => null],
            ['ext_job_id' => 189, 'org_chart_job_id_n' => null],
            ['ext_job_id' => 36, 'org_chart_job_id_n' => null],
            ['ext_job_id' => 206, 'org_chart_job_id_n' => null],
            ['ext_job_id' => 135, 'org_chart_job_id_n' => null]
        ];

        $coll = collect($arr);

        try {
            \DB::table('ext_jobs_vs_org_chart_job')->delete();
    
            \DB::table('ext_jobs_vs_org_chart_job')->insert(
                $coll->unique('ext_job_id')->toArray()
            );
        } catch (\Throwable $th) {
        }

    }
}

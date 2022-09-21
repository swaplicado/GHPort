<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Adm\Department;
use App\User;
class DepartmentsController extends Controller
{
    public function saveDeptsFromJSON($lSiieDepts)
    {
        $lUnivDepts = Department::pluck('id_department', 'external_id');

        foreach ($lSiieDepts as $jSiieDept) {
            try {
                if (isset($lUnivDepts[$jSiieDept->id_department])) {
                    $idDeptUniv = $lUnivDepts[$jSiieDept->id_department];
                    $this->updDepartment($jSiieDept, $idDeptUniv);
                }
                else {
                    $this->insertDepartment($jSiieDept);
                }
            }
            catch (\Throwable $th) {
                
            }
        }
    }
    
    private function updDepartment($jSiieDept, $idDeptUniv)
    {
        Department::where('id_department', $idDeptUniv)
                    ->update(
                            [
                                'name' => $jSiieDept->dept_name,
                                'abbreviation' => $jSiieDept->dept_code,
                                'is_delete' => $jSiieDept->is_deleted
                            ]
                        );
    }
    
    private function insertDepartment($jSiieDept)
    {
        $oDept = new Department();

        $oDept->name = $jSiieDept->dept_name;
        $oDept->abbreviation = $jSiieDept->dept_code;
        $oDept->is_delete = $jSiieDept->is_deleted;
        $oDept->external_id = $jSiieDept->id_department;
        $oDept->created_by = 1;
        $oDept->updated_by = 1;

        $oDept->save();
    }

    public function setSupDeptAndHeadUser($lSiieDepts)
    {
        $lUnivDepts = Department::pluck('id_department', 'external_id');
        $lUsers = User::pluck('id', 'external_id');

        foreach ($lSiieDepts as $siieDepto) {
            $upds = [];
            if ($siieDepto->superior_department_id > 0) {
                $idSupDepto = $lUnivDepts[$siieDepto->superior_department_id];
                $upds['department_n_id'] = $idSupDepto;
            }
            
            if ($siieDepto->head_employee_id > 0) {
                $idHeadUser = $lUsers[$siieDepto->head_employee_id];
                $upds['head_user_n_id'] = $idHeadUser;
            }

            if (count($upds) == 0) {
                continue;
            }

            $idDepto = $lUnivDepts[$siieDepto->id_department];
    
            Department::where('id_department', $idDepto)
                        ->update($upds);
        }

    }

    public function index(){
        
    }
}

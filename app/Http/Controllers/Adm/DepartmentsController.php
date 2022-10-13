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
        $lUnivDepts = Department::pluck('id_department', 'external_id_n');

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
                                'department_name' => $jSiieDept->dept_name,
                                'department_code' => $jSiieDept->dept_code,
                                'department_name_ui' => $jSiieDept->dept_name.' - '.$jSiieDept->dept_code,
                                'is_deleted' => $jSiieDept->is_deleted
                            ]
                        );
    }
    
    private function insertDepartment($jSiieDept)
    {
        $oDept = new Department();

        $oDept->department_code = $jSiieDept->dept_code;
        $oDept->department_name = $jSiieDept->dept_name;
        $oDept->department_name_ui = $jSiieDept->dept_name.' - '.$jSiieDept->dept_code;
        $oDept->department_id_n = null;
        $oDept->external_id_n = $jSiieDept->id_department;
        $oDept->is_deleted = $jSiieDept->is_deleted;
        $oDept->created_by = 1;
        $oDept->updated_by = 1;

        $oDept->save();
    }

    public function index(){
        
    }
}

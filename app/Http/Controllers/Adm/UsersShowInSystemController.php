<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Models\Adm\OrgChartJob;

// Definir el tipo de contenido como texto/html
header('Content-Type: text/html');

// Definir cabeceras de caché para evitar que el navegador almacene en caché la página
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
header('Pragma: no-cache'); // HTTP 1.0.
header('Expires: 0'); // Proxies.

class UsersShowInSystemController extends Controller
{
    public function index(){
        $lUsers = User::join('org_chart_jobs as org', 'org.id_org_chart_job', '=', 'users.org_chart_job_id')
                    ->where('users.id', '!=', 1)
                    ->where('is_delete', 0)
                    ->select('users.*', 'org.job_name as area')
                    ->orderBy('users.full_name')
                    ->get();
                    
        return view('Adm.usersShowInSystem')->with('lUsers', $lUsers);
    }

    public function updateShowInSystem(Request $request){
        try {
            $showInSystem = $request->showInSystem;
            $id = $request->user_id;
            $oUser = User::findOrFail($id);
            $oUser->show_in_system = $showInSystem;
            $oUser->update();
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage(), 'toastType' => 'error']);
        }

        return json_encode(['success' => true, 'showInSystem' => $oUser->show_in_system, 'toastType' => 'success']);
    }

    public function officeOrgChartJob(){
        $lOrgChartJobs = OrgChartJob::where('is_deleted', 0)
                                    ->orderBy('job_name')
                                    ->get();

        return view('Adm.officeOrgChartJob')->with('lOrgChartJobs', $lOrgChartJobs);
    }

    public function updateOfficeOrgChartJob(Request $request){
        try {
            $id = $request->area_id;
            $oOrgChartJob = OrgChartJob::findOrFail($id);
            $oOrgChartJob->is_office = $request->is_office;
            $oOrgChartJob->update();
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage(), 'toastType' => 'error']);
        }

        return json_encode(['success' => true, 'is_office' => $oOrgChartJob->is_office, 'toastType' => 'success']);
    }
}

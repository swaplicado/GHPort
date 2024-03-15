<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;

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
}

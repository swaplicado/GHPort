<?php

namespace App\Http\Controllers\Pages;

use App\Constants\SysConst;
use App\Http\Controllers\Controller;
use App\Models\Reports\UserConfigReport;
use App\Utils\OrgChartUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;
use \App\Utils\delegationUtils;
use App\Utils\GlobalUsersUtils;

class profileController extends Controller
{
    public function index(){
        $user = \Auth::user();
        $constants = [
            'JEFE' => SysConst::JEFE,
            'GH' => SysConst::GH,
            'ADMIN' => SysConst::ADMINISTRADOR,
        ];
        $oReport = UserConfigReport::where('user_id', \Auth::user()->id)->first();
        $report_enabled = \App\Utils\Configuration::getConfigurations()->incidents_report->enabled;

        if(is_null($oReport)){
            $oReport = new \stdClass;
            $oReport->is_active = 0;
            $oReport->always_send = 0;
        }

        $myLevel = \DB::table('org_chart_jobs as o')
                        ->join('organization_levels as l', 'l.id_organization_level', '=', 'o.org_level_id')
                        ->where('id_org_chart_job', $user->org_chart_job_id)
                        ->value('l.level');

        $arrOrgJobs = OrgChartUtils::getAllChildsOrgChartJob($user->org_chart_job_id);
        $lOrgChart = \DB::table('org_chart_jobs')
                        ->whereIn('id_org_chart_job', $arrOrgJobs)
                        ->groupBy('org_level_id')
                        ->pluck('org_level_id')
                        ->toArray();

        $levels = [];
        if($myLevel > 0){
            $oLevels = \DB::table('organization_levels')
                        ->whereIn('id_organization_level', $lOrgChart)
                        ->where('id_organization_level', '!=', 1)
                        ->get();

            $levels = $oLevels->map(function ($item){
                return [
                    'id' => $item->id_organization_level,
                    'text' => $item->name,
                ];
            });

            $levels->prepend(['id' => 0, 'text' => 'Todos']);
        }

        $myConf_level = \DB::table('users_config_reports')
                            ->where('user_id', \Auth::user()->id)
                            ->where('is_active', 1)
                            ->value('organization_level_id');

        return view('users.profile')->with('user', $user)
                                    ->with('constants', $constants)
                                    ->with('reportChecked', $oReport->is_active)
                                    ->with('reportAlways_send', $oReport->always_send)
                                    ->with('report_enabled', $report_enabled)
                                    ->with('levels', $levels)
                                    ->with('myConf_level', $myConf_level);
    }

    public function updatePass(Request $request){
        if($request->password != $request->confirm_password){
            return json_encode(['success' => false, 'message' => 'Los campos de contraseña y confirmación de contraseña deben ser iguales. Por favor, ingréselos de nuevo.', 'icon' => 'error']);
        }

        try {
            \DB::beginTransaction();
            $user = User::findOrFail(\Auth::user()->id);
            // $user = User::findOrFail(delegationUtils::getIdUser());
            $user->password = Hash::make($request->password);
            $user->changed_password = 1;
            $user->update();
            \DB::commit();
            $user->pass = $user->password;
            $user->external_id = $user->external_id_n;
            GlobalUsersUtils::globalUpdateFromSystem($user, SysConst::SYSTEM_PGH);
        } catch (\Throwable $th) {
            \DB::rollback();
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'message' => 'Registro actualizado', 'icon' => 'success']);
    }

    public function updateReport(Request $request){
        try {
            $is_active = $request->is_active;
            $myConf_level = $request->myConf_level == 0 ? null : $request->myConf_level;

            \DB::beginTransaction();

            $oReport = UserConfigReport::where('user_id', \Auth::user()->id)->first();

            if(is_null($oReport)){
                $oReport = new UserConfigReport();
            }
            $oReport->user_id = \Auth::user()->id;
            $oReport->is_active = $is_active;
            $oReport->all_employees = 0;
            $oReport->always_send = $request->always_send;
            $oReport->organization_level_id = $myConf_level;
            $oReport->save();

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema', 'icon' => 'error', 'checked' => !$is_active]);
        }

        return json_encode(['success' => true, 'checked' => $is_active]);
    }
}

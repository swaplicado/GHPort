<?php

namespace App\Http\Controllers\Pages;

use App\Constants\SysConst;
use App\Http\Controllers\Controller;
use App\Models\Reports\UserConfigReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;
use \App\Utils\delegationUtils;

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

        return view('users.profile')->with('user', $user)
                                    ->with('constants', $constants)
                                    ->with('reportChecked', $oReport->is_active)
                                    ->with('reportAlways_send', $oReport->always_send)
                                    ->with('report_enabled', $report_enabled);
    }

    public function updatePass(Request $request){
        if($request->password != $request->confirm_password){
            return json_encode(['success' => false, 'message' => 'Los campos contraseña deben coincidir', 'icon' => 'error']);
        }

        try {
            \DB::beginTransaction();
            $user = User::findOrFail(\Auth::user()->id);
            // $user = User::findOrFail(delegationUtils::getIdUser());
            $user->password = Hash::make($request->password);
            $user->changed_password = 1;
            $user->update();
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollback();
            return json_encode(['success' => false, 'message' => 'Error al actualizar el registro', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'message' => 'Registro actualizado', 'icon' => 'success']);
    }

    public function updateReport(Request $request){
        try {
            $is_active = $request->is_active;

            \DB::beginTransaction();

            $oReport = UserConfigReport::where('user_id', \Auth::user()->id)->first();

            if(is_null($oReport)){
                $oReport = new UserConfigReport();
            }
            $oReport->user_id = \Auth::user()->id;
            $oReport->is_active = $is_active;
            $oReport->all_employees = 0;
            $oReport->always_send = $request->always_send;
            $oReport->save();

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'error', 'checked' => !$is_active]);
        }

        return json_encode(['success' => true, 'checked' => $is_active]);
    }
}

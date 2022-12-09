<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\User;
use App\Constants\SysConst;
use App\Models\Adm\VacationUser;
use App\Models\Adm\VacationUserLog;
use App\Models\Vacations\VacationPlan;
use App\Models\Vacations\VacationPlanDay;
use App\Models\Vacations\VacationPlanDayLog;

class VacationPlansController extends Controller
{
    public function index(){
        \Auth::user()->authorizedRole([SysConst::ADMINISTRADOR, SysConst::GH]);
        $lVacationPlans = VacationPlan::where('is_deleted', 0)->get();

        return view('Adm.vacations_plans')->with('lVacationPlans', $lVacationPlans);
    }

    public function checkDataBeforeSave($years){
        for($i = 0; $i < sizeof($years); $i++){
            if($i > 0){
                if((Integer)$years[$i]['year'] <= (Integer)$years[$i - 1]['year']){
                    return false;
                }
                if((Integer)$years[$i]['days'] < (Integer)$years[$i - 1]['days']){
                    return false;
                }
            }
        }
        return true;
    }

    public function listYears($years){
        $oPlan[] = $years[0];
        for($i = 0; $i < sizeof($years); $i++){
            if($i>0){
                if(((integer)$years[$i]->year - (integer)$years[$i-1]->year) > 1){
                    for($j = 1; $j<((integer)$years[$i]->year - (integer)$years[$i-1]->year); $j++){
                        $oPlan[] = new \stdClass();
                        $oPlan[sizeof($oPlan)-1]->year = ((integer)$years[$i-1]->year + $j);
                        $oPlan[sizeof($oPlan)-1]->days = $years[$i-1]->days;
                    }
                    $oPlan[] =  $years[$i];
                }else{
                    $oPlan[] =  $years[$i];
                }
            }
        }
        return $oPlan;
    }

    public function saveVacationPlan(Request $request){
        \Auth::user()->authorizedRole([SysConst::ADMINISTRADOR, SysConst::GH]);
        if($this->checkDataBeforeSave($request->years)){
            $years = $this->listYears(json_decode(json_encode($request->years), FALSE));
            $name = $request->name;
            $payment_frec = $request->payment_frec;
            $unionized = $request->unionized;
            $start_date = $request->start_date;

            try {
                \DB::beginTransaction();
                $oVacationPlan = new VacationPlan();
                $oVacationPlan->vacation_plan_name = $name;
                $oVacationPlan->payment_frec_id_n = $payment_frec != 0 ? $payment_frec : null;
                $oVacationPlan->is_unionized_n = $unionized;
                $oVacationPlan->start_date_n = $start_date;
                $oVacationPlan->is_deleted = 0;
                $oVacationPlan->created_by = \Auth::user()->id;
                $oVacationPlan->updated_by = \Auth::user()->id;
                $oVacationPlan->save();

                foreach($years as $year){
                    $oVacationPlanDays = new VacationPlanDay();
                    $oVacationPlanDays->vacations_plan_id = $oVacationPlan->id_vacation_plan;
                    $oVacationPlanDays->until_year = $year->year;
                    $oVacationPlanDays->vacation_days = $year->days;
                    $oVacationPlanDays->save();
                }

                $lVacationPlans = VacationPlan::where('is_deleted', 0)->get();

                \DB::commit();
            } catch (\Throwable $th) {
                \DB::rollBack();
                return json_encode(['success' => false, 'message' => 'Error al guardar el registro', 'icon' => 'error']);
            }
            return json_encode(['success' => true, 'lVacationPlans' => $lVacationPlans, 'message' => 'Registro Guardado con éxito', 'icon' => 'success']);
        }else{
            return json_encode(['success' => false, 'message' => 'Error al guardar el registro', 'icon' => 'error']);
        }
    }

    public function getVacationPlanDays(Request $request){
        try {
            $oVacationPlanDays = VacationPlanDay::where('vacations_plan_id', $request->vacation_plan_id)->get();
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al obtener los registros', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'vacationPlanDays' => $oVacationPlanDays]);
    }

    public function deleteVacationPlan(Request $request){
        try {
            \DB::beginTransaction();
                $oVacationPlan = VacationPlan::findOrFail($request->vacation_plan_id);
                $oVacationPlan->is_deleted = 1;
                $oVacationPlan->updated_by = \Auth::user()->id;
                $oVacationPlan->update();

                $lVacationPlans = VacationPlan::where('is_deleted', 0)->get();

                \DB::commit();
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al eliminar el registro', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lVacationPlans' => $lVacationPlans, 'message' => 'Registro eliminado con éxito', 'icon' => 'success']);
    }

    public function updateVacationPlan(Request $request){
        if($this->checkDataBeforeSave($request->years)){
            $years = $this->listYears(json_decode(json_encode($request->years), FALSE));
            $name = $request->name;
            $payment_frec = $request->payment_frec;
            $unionized = $request->unionized;
            $start_date = $request->start_date;

            try {
                \DB::beginTransaction();
                $oVacationPlan = VacationPlan::findOrFail($request->idVacPlan);
                $this->saveVacationPLanLog($oVacationPlan->id_vacation_plan, $oVacationPlan->created_by);
                $oVacPlanDay = VacationPlanDay::where('vacations_plan_id', $request->idVacPlan)->get();
                foreach($oVacPlanDay as $oVacDay){
                    $oVacDay->delete();
                }
                $oVacationPlan->vacation_plan_name = $name;
                $oVacationPlan->payment_frec_id_n = $payment_frec != 0 ? $payment_frec : null;
                $oVacationPlan->is_unionized_n = $unionized;
                $oVacationPlan->start_date_n = $start_date;
                $oVacationPlan->updated_by = \Auth::user()->id;
                $oVacationPlan->update();

                foreach($years as $year){
                    $oVacationPlanDays = new VacationPlanDay();
                    $oVacationPlanDays->vacations_plan_id = $oVacationPlan->id_vacation_plan;
                    $oVacationPlanDays->until_year = $year->year;
                    $oVacationPlanDays->vacation_days = $year->days;
                    $oVacationPlanDays->save();
                }

                $lVacationPlans = VacationPlan::where('is_deleted', 0)->get();

                \DB::commit();
            } catch (\Throwable $th) {
                \DB::rollBack();
                return json_encode(['success' => false, 'message' => 'Error al actualizar el registro', 'icon' => 'error']);
            }

            return json_encode(['success' => true, 'lVacationPlans' => $lVacationPlans, 'message' => 'Registro actualizado con éxito', 'icon' => 'success']);
        }else{
            return json_encode(['success' => false, 'message' => 'Error al actualizar el registro', 'icon' => 'error']);
        }
    }

    public function saveVacationPLanLog($vacation_plan_id, $created_by){
        $oVacationPlanDays = VacationPlanDay::where('vacations_plan_id', $vacation_plan_id)->get();
        foreach($oVacationPlanDays as $oVac){
            $oLog = new VacationPlanDayLog();
            $oLog->vacations_plan_id = $vacation_plan_id;
            $oLog->until_year = $oVac->until_year;
            $oLog->vacation_days = $oVac->vacation_days;
            $oLog->created_by = $created_by;
            $oLog->save();
        }
    }

    public function getUsersAssigned(Request $request){
        try {
            $lUsers = \DB::table('users')
                        ->where('is_delete', 0)
                        ->where('is_active', 1)
                        ->where('id', '!=', 1)
                        ->where('vacation_plan_id', '!=', $request->vacation_plan_id)
                        ->select('id','vacation_plan_id', 'full_name', 'full_name_ui')
                        ->orderBy('full_name_ui')
                        ->get();
    
            $lUsersAssigned = \DB::table('users')
                                ->where('is_delete', 0)
                                ->where('is_active', 1)
                                ->where('id', '!=', 1)
                                ->where('vacation_plan_id', $request->vacation_plan_id)
                                ->select('id','vacation_plan_id', 'full_name', 'full_name_ui')
                                ->orderBy('full_name_ui')
                                ->get();
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al obtener el registro', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lUsers' => $lUsers, 'lUsersAssigned' => $lUsersAssigned]);
    }

    public function saveAssignVacationPlan(Request $request){
        try {
            $lUserAssigned_id = collect($request->lUsersAssigned)->pluck('id');
    
            $lUsersUnsigned = User::where('vacation_plan_id', $request->vacation_plan_id)
                                    ->where('is_delete', 0)
                                    ->where('is_active', 1)
                                    ->whereNotIn('id', $lUserAssigned_id)
                                    ->get();

            \DB::beginTransaction();
            foreach($lUsersUnsigned as $userUns){
                $this->saveVacationUserLog($userUns);
                $userUns->vacation_plan_id = 1;
                $userUns->update();
                $this->generateVacationUser($userUns->id, 1);
            }
    
            $lUsers = User::whereIn('id', $lUserAssigned_id)->get();
    
            foreach($lUsers as $user){
                $this->saveVacationUserLog($user);
                $user->vacation_plan_id = $request->vacation_plan_id;
                $user->update();
                $this->generateVacationUser($user->id, $request->vacation_plan_id);
            }

            \DB::commit();
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al guardar los registros', 'icon' => 'error']);
        }

        return json_encode(['success' => true]);
    }

    public function generateVacationUser($user_id, $vacation_plan_id){
        $vacation_plan = VacationPlan::findOrFail($vacation_plan_id);

        $vacation_plan_day = VacationPlanDay::where('vacations_plan_id', $vacation_plan_id)->get();

        $vacationUser = VacationUser::where('user_id', $user_id)
                                    ->where('is_deleted', 0)
                                    ->get();

        $oUser = \DB::table('users as u')
                    ->join('user_admission_logs as ual', 'ual.user_id', '=', 'u.id')
                    ->where('id', $user_id)
                    ->first();

        $date = Carbon::parse($oUser->benefits_date);
        if(sizeof($vacationUser) > 0){
            $vacationUser = $vacationUser->where('date_start', '>', $vacation_plan->start_date_n);
            foreach($vacationUser as $vac){
                $oDays = $vacation_plan_day->where('until_year', $vac->id_anniversary)->first();
                if(is_null($oDays)){
                    $oDays = $vacation_plan_day->last();
                }
                $vac->vacation_days =  $oDays->vacation_days;
                $vac->year = $date->year;
                $vac->date_start = $date->format('Y-m-d');
                $vac->date_end = $date->addYear(1)->subDays(1)->format('Y-m-d');
                $vac->is_expired = $date->lt(Carbon::today()) ? $date->diffInYears(Carbon::today()) > 2 : 0;
                $vac->update();
            }
        }else{
            for($i=1; $i<=50; $i++){
                $oDays = $vacation_plan_day->where('until_year', $i)->first();
                if(is_null($oDays)){
                    $oDays = $vacation_plan_day->last();
                }

                $oVacAll = new VacationUser();
                $oVacAll->user_id = $oUser->id;
                $oVacAll->user_admission_log_id = $oUser->id_user_admission_log;
                $oVacAll->id_anniversary = $i;
                $oVacAll->year = $date->year;
                $oVacAll->date_start = $date->format('Y-m-d');
                $oVacAll->date_end = $date->addYear(1)->subDays(1)->format('Y-m-d');
                $oVacAll->vacation_days = $oDays->vacation_days;
                $oVacAll->is_closed = 0;
                $oVacAll->is_closed_manually = 0;
                $oVacAll->is_expired = $date->lt(Carbon::today()) ? $date->diffInYears(Carbon::today()) > 2 : 0;
                $oVacAll->is_expired_manually = 0;
                $oVacAll->is_deleted = 0;
                $oVacAll->created_by = \Auth::user()->id;
                $oVacAll->updated_by = \Auth::user()->id;
                $oVacAll->save();

                $date->addDays(1);
            }
        }
    }

    public function saveVacationUserLog($oUser){
        $oVacUser = VacationUser::where('user_id', $oUser->id)
                                ->where('is_deleted', 0)
                                ->get();

        foreach($oVacUser as $v){
            $vacUserLog = new VacationUserLog();
            $vacUserLog->date_log = Carbon::now()->toDateString();
            $vacUserLog->user_id = $oUser->id;
            $vacUserLog->user_admission_log_id = $v->user_admission_log_id;
            $vacUserLog->id_anniversary = $v->id_anniversary;
            $vacUserLog->year = $v->year;
            $vacUserLog->date_start = $v->date_start;
            $vacUserLog->date_end = $v->date_end;
            $vacUserLog->vacation_days = $v->vacation_days;
            $vacUserLog->is_closed = $v->is_closed;
            $vacUserLog->is_closed_manually = $v->is_closed_manually;
            $vacUserLog->closed_by_n = $v->closed_by_n;
            $vacUserLog->is_expired = $v->is_expired;
            $vacUserLog->is_expired_manually = $v->is_expired_manually;
            $vacUserLog->expired_by_n = $v->expired_by_n;
            $vacUserLog->is_deleted = $v->is_deleted;
            $vacUserLog->created_by = \Auth::user()->id;
            $vacUserLog->updated_by = \Auth::user()->id;
            $vacUserLog->save();
        }
    }
}

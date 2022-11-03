<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vacations\VacationPlan;
use App\Models\Vacations\VacationPlanDay;

class VacationPlansController extends Controller
{
    public function index(){
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
        }

        return json_encode(['success' => true, 'lVacationPlans' => $lVacationPlans, 'message' => 'Registro Guardado con éxito', 'icon' => 'success']);
    }

    public function getVacationPlanDays(Request $request){
        try {
            $oVacationPlanDays = VacationPlanDay::where('vacations_plan_id', $request->vacation_plan_id)->get();
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al obtener los registros', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'vacationPlanDays' => $oVacationPlanDays]);
    }
}

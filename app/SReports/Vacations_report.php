<?php namespace App\SReports;

use App\Constants\SysConst;
use App\Mail\incidencesReportMail;
use App\Models\Reports\UserConfigReport;
use App\Utils\dateUtils;
use App\Utils\incidencesUtils;
use App\Utils\OrgChartUtils;
use App\Utils\permissionsUtils;
use ArrayObject;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class Vacations_report {
    public static function makeVacationsReport(){
        try {
            $config = \App\Utils\Configuration::getConfigurations();
            $lConfigReports = UserConfigReport::join('users as u', 'users_config_reports.user_id', '=', 'u.id')
                                                ->where('users_config_reports.is_active', 1)
                                                ->select(
                                                    'users_config_reports.id_config_report',
                                                    'users_config_reports.user_id',
                                                    'users_config_reports.is_active',
                                                    'users_config_reports.always_send',
                                                    'users_config_reports.organization_level_id',
                                                    'u.username',
                                                    'u.institutional_mail',
                                                    'u.org_chart_job_id',
                                                    'u.rol_id',
                                                )
                                                ->get();
            
            Carbon::setLocale('es');

            if($config->incidents_report->week == "next"){
                $date_ini = Carbon::now()->next(Carbon::MONDAY);
                $date_end = Carbon::now()->next(Carbon::SUNDAY)->addWeek();
            }else if($config->incidents_report->week == "actual"){
                $now = Carbon::now();
                $date_ini = clone $now->startOfWeek();
                $date_end = clone $now->endOfWeek();
            }

            $diff = $date_end->diffInDays($date_ini);
            $week = [];
            for($i = 0; $i <= $diff; $i++){
                // $day = Carbon::now()->next(Carbon::MONDAY);
                $day = clone $date_ini; 
                $week[] = [
                            'date' => $day->addDays($i)->format('Y-m-d'),
                            'day_name' => $day->isoFormat('dddd'),
                            'day_num' => dateUtils::formatDate($day->format('d-m-Y'), 'D-M-Y'),
                            'num_week' => $day->dayOfWeek,
                            'incidences' => [],
                            'comments' => [],
                            'id' => 0,
                            'span' => 0,
                            'holiday' => null,
                        ];
            }
            $date_ini = $date_ini->toDateString();
            $date_end = $date_end->toDateString();

            $sDate = dateUtils::datesToString($date_ini, $date_end);
            $sDateHead = dateUtils::datesToString($date_ini, $date_end, 'm');

            $ini = dateUtils::formatDate($date_ini, 'D-m-Y');
            $end = dateUtils::formatDate($date_end, 'D-m-Y');

            $holidays = \DB::table('holidays')
                        ->where('fecha', '>=', $date_ini)
                        ->where('fecha', '<=', $date_end)
                        ->where('is_deleted', 0)
                        ->select(
                            'fecha',
                            'name'
                        )
                        ->get()
                        ->toArray();

            foreach ($lConfigReports as $conf) {
                if($conf->rol_id == SysConst::ADMINISTRADOR){
                    $conf->org_chart_job_id = 2;
                }

                if(is_null($conf->organization_level_id)){
                    $lEmployees = incidencesUtils::getAllMyEmployeeslIncidences($conf->org_chart_job_id, $date_ini, $date_end, $week);
                }else{
                    $lEmployees = incidencesUtils::getEmployeesByLevel($conf->org_chart_job_id, $conf->organization_level_id, $date_ini, $date_end, $week);
                }

                $lEmployees = $lEmployees->sortBy('full_name');
                foreach($lEmployees as $index => $emp){
                    $emp->myWeek = array_slice($week, 0);
                    for($i = 0; $i < sizeof($emp->myWeek); $i++){
                        foreach($holidays as $h){
                            if($h->fecha == $emp->myWeek[$i]['date']){
                                $emp->myWeek[$i]['holiday'] = $h->name;
                            }
                        }
                    }
                    $emp->lIncidences = $emp->lIncidences->sortBy('end_date')->values();
                    if(count($emp->lIncidences) > 0){
                        if(isset($emp->lIncidences[count($emp->lIncidences)-1]->return_date)){
                            $emp->returnDate = dateUtils::formatDate($emp->lIncidences[count($emp->lIncidences)-1]->return_date, 'ddd D-m-Y');
                        }else{
                            $emp->returnDate = '';
                        }
                    }else{
                        $emp->returnDate = '';
                    }
                    
                    foreach($emp->lIncidences as $inc){
                        $incidenceDays = json_decode($inc->ldays);
                        foreach($incidenceDays as $incDay){
                            for($i = 0; $i < sizeof($emp->myWeek); $i++){
                                if($incDay->date == $emp->myWeek[$i]['date']){
                                    if($incDay->taked){
                                        if($inc->application_type == 'permission'){
                                            if($inc->permission_type != SysConst::PERMISO_INTERMEDIO){
                                                $permissionSchedule = Vacations_report::getScheduleUser($emp->id, $emp->myWeek[$i]['num_week'], $inc->permission_type, $inc->minutes);
                                                if ($permissionSchedule) {
                                                    array_push($emp->myWeek[$i]['incidences'], 'Permiso '. strtolower($inc->name) . ': ' . $permissionSchedule);
                                                } else {
                                                    $res = permissionsUtils::convertMinutesToHours($inc->minutes);
                                                    array_push($emp->myWeek[$i]['incidences'], 'Permiso '. strtolower($inc->name).': '.$res[0].' hrs. '.$res[1].' min.');
                                                }
                                            }else{
                                                $salida = Carbon::parse($inc->intermediate_out)->format('H:i');
                                                $regreso = Carbon::parse($inc->intermediate_return)->format('H:i');
                                                array_push($emp->myWeek[$i]['incidences'], 'permiso: '. $inc->name.' salida: '.$salida.' regreso: '.$regreso);
                                            }
                                        }else{
                                            array_push($emp->myWeek[$i]['incidences'], $inc->name);
                                        }
                                        array_push($emp->myWeek[$i]['comments'], $inc->emp_comments_n);
                                        $emp->myWeek[$i]['id'] = $inc->id_incidence;
                                    }
                                }
                            }
                        }
                    }
                    $arrAux = [];
                    $id_incidence = 0;
                    $span = 1;
                    foreach($emp->myWeek as $w){
                        if($id_incidence != $w['id'] || $w['id'] == 0){
                            array_push($arrAux, $w);
                            $id_incidence = $w['id'];
                            $span = 1;
                        }else{
                            $span++;
                            $arrAux[count($arrAux)-1]['span'] = $span;
                        }
                    }
                    $emp->myWeek = $arrAux;
                    if(count($emp->lIncidences) == 0){
                        $lEmployees->forget($index);
                    }
                }

                $arrOrg = $lEmployees->pluck('org_chart_job_id')->unique();

                $lOrgCharts = \DB::table('org_chart_jobs as org')
                                ->leftJoin('organization_levels as l', 'l.id_organization_level', '=', 'org_level_id')
                                ->whereIn('id_org_chart_job', $arrOrg)
                                ->select(
                                    'org.id_org_chart_job',
                                    'org.job_name',
                                    'l.id_organization_level',
                                    'l.level',
                                    'l.name as level_name'
                                )
                                ->orderBy('level')
                                ->get();

                foreach($lOrgCharts as $org){
                    $lOrg = OrgChartUtils::getDirectFatherBossOrgChartJob($org->id_org_chart_job);

                    $org->superviser = \DB::table('users')
                                            ->whereIn('org_chart_job_id', $lOrg)
                                            ->where('is_active', 1)
                                            ->where('is_delete', 0)
                                            ->pluck('full_name')
                                            ->toArray();

                    $org->lEmployees = $lEmployees->where('org_chart_job_id', $org->id_org_chart_job)->sortBy('full_name');
                }

                try {
                    if(!$config->incidents_report->always_send){
                        if($conf->always_send){
                            Mail::to($conf->institutional_mail)->send(new incidencesReportMail($lEmployees, $week, $ini, $end, $lOrgCharts, $sDate, $sDateHead));
                        }elseif (count($lEmployees) > 0) {
                            Mail::to($conf->institutional_mail)->send(new incidencesReportMail($lEmployees, $week, $ini, $end, $lOrgCharts, $sDate, $sDateHead));
                        }
                    }else{
                        Mail::to($conf->institutional_mail)->send(new incidencesReportMail($lEmployees, $week, $ini, $end, $lOrgCharts, $sDate, $sDateHead));
                    }
                    $output = new \Symfony\Component\Console\Output\ConsoleOutput();
                    $output->writeln("Reporte enviado a ".$conf->institutional_mail);
                } catch (\Throwable $th) {
                    \Log::error($th);
                    $output = new \Symfony\Component\Console\Output\ConsoleOutput();
                    $output->writeln($th->getMessage());
                }
            }

        } catch (\Throwable $th) {
            \Log::error($th);
            $output = new \Symfony\Component\Console\Output\ConsoleOutput();
            $output->writeln($th->getMessage());
            return $th->getMessage();
        }
    }

    public static function getScheduleUser($user_id, $numDay, $permission_type, $minutes) {
        $schedule = \DB::table('users as u')
                        ->join('schedule_template as st', 'st.id', '=', 'u.schedule_template_id')
                        ->join('schedule_day as sd', 'sd.schedule_template_id', '=', 'st.id')
                        ->where('u.id', $user_id)
                        ->where('sd.is_working', 1)
                        ->where('sd.is_deleted', 0)
                        ->where('sd.day_num', $numDay)
                        ->select(
                            'st.name',
                            'sd.day_name',
                            'sd.day_num',
                            \DB::raw("DATE_FORMAT(sd.entry, '%H:%i') as entry"),
                            \DB::raw("DATE_FORMAT(sd.departure, '%H:%i') as departure")
                        )
                        ->first();

        $hasSchedule = false;
        $permissionSchedule = '';
        $entry = '';
        $departure = '';
        if(!is_null($schedule)){
            $hasSchedule = true;
            $entry = $schedule->entry;
            $departure = $schedule->departure;
            if($permission_type == SysConst::PERMISO_ENTRADA){
                $permissionSchedule = Carbon::parse($entry)->addMinutes($minutes)->format('h:i A');
            }else if ($permission_type == SysConst::PERMISO_SALIDA){
                $permissionSchedule = Carbon::parse($departure)->subMinutes($minutes)->format('h:i A');
            }

            $schedule->entry = Carbon::parse($schedule->entry)->format('h:i A');
            $schedule->departure = Carbon::parse($schedule->departure)->format('h:i A');
        }

        return $permissionSchedule;
    }
}
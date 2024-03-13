<?php

namespace App\Mail;

use App\Constants\SysConst;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Utils\dateUtils;
use App\Utils\permissionsUtils;

class requestPermissionMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($permission_id)
    {
        $this->permission_id = $permission_id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $permission = \DB::table('hours_leave as hr')
                            ->join('cat_permission_tp as tp', 'tp.id_permission_tp', '=', 'hr.type_permission_id')
                            ->join('permission_cl as cl', 'cl.id_permission_cl', '=', 'hr.cl_permission_id')
                            ->where('hr.id_hours_leave', $this->permission_id)
                            ->select(
                                'hr.*',
                                'tp.permission_tp_name',
                                'cl.id_permission_cl AS class',
                            )
                            ->first();

        $employee = \DB::table('users')
                        ->where('id', $permission->user_id)
                        ->first();

        $numDay = Carbon::parse($permission->start_date)->dayOfWeek;
        $permission->start_date = dateUtils::formatDate($permission->start_date, 'D/m/Y dddd');

        if($permission->type_permission_id != SysConst::PERMISO_INTERMEDIO){
            $result = permissionsUtils::convertMinutesToHours($permission->minutes);
            $permission->time = $result[0].':'.$result[1].' hrs';
        }else{
            $interOut = Carbon::createFromFormat('H:i:s', $permission->intermediate_out)->format('h:i A');
            $interReturn = Carbon::createFromFormat('H:i:s', $permission->intermediate_return)->format('h:i A');
            $permission->time = $interOut.' a '.$interReturn;
        }

        $schedule = \DB::table('users as u')
                        ->join('schedule_template as st', 'st.id', '=', 'u.schedule_template_id')
                        ->join('schedule_day as sd', 'sd.schedule_template_id', '=', 'st.id')
                        ->where('u.id', $permission->user_id)
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
            if($permission->type_permission_id == SysConst::PERMISO_ENTRADA){
                $permissionSchedule = Carbon::parse($entry)->addMinutes($permission->minutes)->format('h:i A');
            }else if ($permission->type_permission_id == SysConst::PERMISO_SALIDA){
                $permissionSchedule = Carbon::parse($departure)->subMinutes($permission->minutes)->format('h:i A');
            }

            $schedule->entry = Carbon::parse($schedule->entry)->format('h:i A');
            $schedule->departure = Carbon::parse($schedule->departure)->format('h:i A');

        }

        $email = "Portalgh@aeth.mx";
        if( $permission->class == 1 ){
            return $this->from($email)
                        ->subject('[Portal GH] Solicitud permiso personal por horas '.$employee->short_name.' '.$employee->first_name.' '.$employee->last_name)
                        ->view('mails.requestPermissionMail')
                        ->with('permission', $permission)
                        ->with('employee', $employee)
                        ->with('emp_comments_n', $permission->emp_comments_n)
                        ->with('hasSchedule', $hasSchedule)
                        ->with('schedule', $schedule)
                        ->with('permissionSchedule', $permissionSchedule);
        }else{
            return $this->from($email)
                        ->subject('[Portal GH] Solicitud tema laboral por horas '.$employee->short_name.' '.$employee->first_name.' '.$employee->last_name)
                        ->view('mails.requestPermissionMail')
                        ->with('permission', $permission)
                        ->with('employee', $employee)
                        ->with('emp_comments_n', $permission->emp_comments_n)
                        ->with('hasSchedule', $hasSchedule)
                        ->with('schedule', $schedule)
                        ->with('permissionSchedule', $permissionSchedule);
        }
    }
}
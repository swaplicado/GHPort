<?php namespace App\Utils;

use App\Http\Controllers\Sys\CheckVacationsToExpireController;
use \App\Utils\delegationUtils;
use \App\Constants\SysConst;
use \App\Models\Notifications\Notification;
use Carbon\Carbon;

class notificationsUtils {
    public static function initNotifications(){
        session()->put('lNotifications', []);
        session()->put('oldlNotifications', []);
        session()->put('notificationsToSee', null);
        session()->put('notificationChecked', false);
    }

    public static function setOldNotifications(){
        $lNotifications = session()->get('lNotifications');
        session()->put('oldlNotifications', $lNotifications);
        if(count($lNotifications) > 0){
            session()->put('showNotificationAlert', true);
        }
    }

    public static function checkNewNotifications(){
        $lNotifications = session()->get('lNotifications');
        $oldlNotifications = session()->get('oldlNotifications');
        $searchKey1 = 'type';
        $searchKey2 = 'id';
        $result = false;
        foreach($lNotifications as $notify){
            $searchValue1 = $notify['type'];
            $searchValue2 = $notify['id'];

            foreach($oldlNotifications as $old){
                if ($old[$searchKey1] === $searchValue1 && $old[$searchKey2] === $searchValue2) {
                    $result = false;
                    break;
                }else{
                    $result = true;
                }
            }

            if($result){
                break;
            }
        }

        if($result){
            session()->put('oldlNotifications', $lNotifications);
            session()->put('notificationChecked', false);
        }
        // return $result;
    }

    public static function notifyVacationToExpire(){
        $oCheckVacationToExpire = new CheckVacationsToExpireController();
        $result = $oCheckVacationToExpire->checkVacationToExpire(\Auth::user()->id);

        if(!is_null($result)){
            $oResult = json_decode($result);
            $lNotify = session()->get('lNotifications');
            if($oResult->vacToExpired > 0){
                array_push($lNotify, [
                                        'id' => 0,
                                        'icon' => 'bx bx-calendar-event',
                                        'text' => "tienes ".$oResult->vacToExpired." días de vacaciones por vencer en ".$oResult->days." días",
                                        'link' => route('myVacations'),
                                    ]
                                );
            }
            session()->put('lNotifications', $lNotify);
        }else{
            $lNotify = session('lNotifications')->get();
            $lNotify = [];
            array_push($lNotify, [
                                    'icon' => 'bx bx-bug',
                                    'text' => "Ha ocurrido un fallo al obtener las vacaciones por vencer",
                                    'link' => '#',
                                ]
                            );
            session()->put('lNotifications', $lNotify);
        }
    }

    public static function cleanNotifications(){
        session()->put('lNotifications', []);
    }

    public static function setNotificationsToSee(){
        $lNotifications = session()->get('lNotifications');
        $notificationsToSee = count($lNotifications);
        session()->put('notificationsToSee', $notificationsToSee);
    }

    public static function notifyVacationsRequest(){
        if(delegationUtils::getRolUser() == SysConst::JEFE){
            $org_chart_job_id = delegationUtils::getOrgChartJobIdUser();
            $arrOrgJobs = orgChartUtils::getDirectChildsOrgChartJob($org_chart_job_id);
            $lEmployees = EmployeeVacationUtils::getlEmployees($arrOrgJobs);
            $lNotify = session()->get('lNotifications');

            foreach($lEmployees as $emp){
                $applications = EmployeeVacationUtils::getApplications(
                                                                $emp->id,
                                                                null,
                                                                [ SysConst::APPLICATION_ENVIADO ]
                                                            );

                foreach($applications as $app){
                    array_push($lNotify, [
                                            'type' => SysConst::CLASS_VACACIONES,
                                            'id' => $app->id_application,
                                            'icon' => 'bx bx-calendar-event',
                                            'text' => $emp->employee." | Tiene una solicitud de vacaciones",
                                            'link' => route('requestVacations', ['id' => $app->application_id]),
                                        ]
                                    );
                }
            }
            session()->put('lNotifications', $lNotify);
        }
    }


    /***********************************************************************************/
    public static function createNotification($data){
        try {
            $oNotification = new Notification();
            $oNotification->user_id = $data->user_id;
            $oNotification->message = $data->message;
            $oNotification->url = $data->url;
            $oNotification->is_revised = 0;
            $oNotification->is_deleted = 0;
            $oNotification->type_id = $data->type_id;
            $oNotification->priority = $data->priority;
            $oNotification->icon = $data->icon;
            $oNotification->is_pendent = 1;
            $oNotification->row_type_id = $data->row_type_id;
            $oNotification->row_id = $data->row_id;
            $oNotification->end_date = $data->end_date;
            $oNotification->created_by = \Auth::user()->id;
            $oNotification->updated_by = \Auth::user()->id;
            $oNotification->save();
        } catch (\Throwable $th) {
        }
    }

    public static function pendetNotification($data){
        try {
            $oNotification = Notification::findOrFail($data->id_notification);
            $oNotification->is_pendent = 0;
            $oNotification->update();
        } catch (\Throwable $th) {
        }
    }

    public static function revisedNotification($data){
        try {
            $oNotification = Notification::findOrFail($data->id_notification);
            $oNotification->is_revised = 1;
            $oNotification->update();
        } catch (\Throwable $th) {
            $th;
        }
    }

    public static function getNotifications($user_id){
        try {
            $lNotifications = \DB::table('notifications')
                                ->where('user_id', $user_id)
                                ->where('is_revised', 0)
                                ->where('is_deleted', 0)
                                ->get();

            $globalNotifications = \DB::table('notifications')
                                    ->where('user_id', null)
                                    ->where('is_revised', 0)
                                    ->where('is_deleted',0)
                                    ->where('end_date', '>', Carbon::now()->toDateString())
                                    ->get();

            $lNotifications = $globalNotifications->merge($lNotifications);
        } catch (\Throwable $th) {
            return null;
        }

        return $lNotifications;
    }

    public static function findNotification($row_type_id, $row_id){
        try {
            $oNotify = \DB::table('notifications')
                        ->where('is_deleted', 0)
                        ->where('is_revised', 0)
                        ->where('row_type_id', $row_type_id)
                        ->where('row_id', $row_id)
                        ->first();

            return $oNotify;
        } catch (\Throwable $th) {
        }
    }

    public static function revisedNotificationFromAction($row_type_id, $row_id){
        try {
            $oNotify = notificationsUtils::findNotification($row_type_id, $row_id);
            if(!is_null($oNotify)){
                notificationsUtils::revisedNotification($oNotify);
            }
        } catch (\Throwable $th) {
        }
    }
}
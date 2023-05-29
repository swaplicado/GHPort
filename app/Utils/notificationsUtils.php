<?php namespace App\Utils;

use App\Http\Controllers\Sys\CheckVacationsToExpireController;
use \App\Utils\delegationUtils;
use \App\Constants\SysConst;
use \App\Models\Notifications\Notification;
use Carbon\Carbon;
use App\Utils\orgChartUtils;

class notificationsUtils {
    public static function createNotification($data){
        try {

            $lUsers = orgChartUtils::getAllUsersByOrgChartJob($data->org_chart_job_id_n);

            foreach($lUsers as $user){
                $oNotification = new Notification();
                $oNotification->user_id = $user->id;
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
            }

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
            $lNotify = \DB::table('notifications')
                        ->where('is_deleted', 0)
                        ->where('is_revised', 0)
                        ->where('row_type_id', $row_type_id)
                        ->where('row_id', $row_id)
                        ->get();

            return $lNotify;
        } catch (\Throwable $th) {
        }
    }

    public static function revisedNotificationFromAction($row_type_id, $row_id){
        try {
            $lNotify = notificationsUtils::findNotification($row_type_id, $row_id);

            foreach($lNotify as $oNotify){
                notificationsUtils::revisedNotification($oNotify);
            }
        } catch (\Throwable $th) {
        }
    }
}
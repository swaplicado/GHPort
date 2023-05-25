<?php

namespace App\Http\Controllers\Utils;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\notificationsUtils;
use \App\Utils\delegationUtils;

class notificationsController extends Controller
{
    public function cleanNotificationsToSee(){
        session()->put('notificationsToSee', null);
        session()->put('notificationChecked', true);
    }

/*********************************************************** */
    public function getNotifications(){
        try {
            $lNotifications = notificationsUtils::getNotifications(delegationUtils::getIdUser());
            $result = $lNotifications->where('is_pendent', 1);
            if(count($result) > 0){
                $showNotificationAlert = true;
            }else{
                $showNotificationAlert = false;
            }
        } catch (\Throwable $th) {
            return json_encode(['success' => false]);
        }
        return json_encode(['success' => true, 'lNotifications' => $lNotifications, 'showNotificationAlert' => $showNotificationAlert]);
    }

    public function cleanPendetNotification(Request $request){
        try {
            $lNotifications = $request->lNotifications;
            foreach ($lNotifications as $notify) {
                $notify = (object)$notify;
                notificationsUtils::pendetNotification($notify);
            }
        } catch (\Throwable $th) {
        }
    }
    public function revisedNotification(Request $request){
        try {
            $oNotify = (object)$request->oNotify;
            notificationsUtils::revisedNotification($oNotify);
        } catch (\Throwable $th) {
        }
    }
}

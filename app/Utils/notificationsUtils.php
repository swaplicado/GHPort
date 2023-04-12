<?php namespace App\Utils;

use App\Http\Controllers\Sys\CheckVacationsToExpireController;

class notificationsUtils {
    public static function initNotifications(){
        session()->put('lNotifications', []);
        session()->put('notificationsToSee', null);
    }

    public static function notifyVacationToExpire(){
        $oCheckVacationToExpire = new CheckVacationsToExpireController();
        $result = $oCheckVacationToExpire->checkVacationToExpire(\Auth::user()->id);

        if(!is_null($result)){
            $oResult = json_decode($result);
            $lNotify = session()->get('lNotifications');
            $notificationsToSee = session()->get('notificationsToSee');
            if($oResult->vacToExpired > 0){
                array_push($lNotify, [
                                        'icon' => 'bx bx-calendar-event',
                                        'text' => "tienes ".$oResult->vacToExpired." días de vacaciones por vencer en ".$oResult->days." días",
                                        'link' => route('myVacations'),
                                    ]
                                );
                $notificationsToSee = $notificationsToSee + 1;
            }
            session()->put('lNotifications', $lNotify);
            session()->put('notificationsToSee', $notificationsToSee);
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
            $notificationsToSee = $notificationsToSee + 1;
            session()->put('notificationsToSee', $notificationsToSee);
        }
    }

    function cleanNotificationsToSee(){
        session()->put('notificationsToSee', null);
    }
}
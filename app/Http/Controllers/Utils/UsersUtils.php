<?php namespace App\Http\Controllers\Utils;

use Illuminate\Http\Request;
use App\User;
use Carbon\Carbon;

class usersUtils {
    public function getUserData(Request $request){
        try {
            $oUser = User::where('users.id', $request->user_id)
                        ->leftJoin('users_vs_photos as up', 'up.user_id', '=', 'users.id')
                        ->where('up.is_deleted', 0)
                        ->select(
                            'users.employee_num',
                            'users.full_name',
                            'users.benefits_date',
                            'up.photo_base64_n as photo64',
                        )
                        ->first();

            $from = Carbon::parse($oUser->benefits_date);
            $to = Carbon::today()->locale('es');
    
            $human = $to->diffForHumans($from, true, false, 6);
    
            $oUser->antiquity = $human;
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'oUser' => null]);
        }

        return json_encode(['success' => true, 'oUser' => $oUser]);
    }
}
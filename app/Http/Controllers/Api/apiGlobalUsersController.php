<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\GlobalUsersUtils;
use App\Utils\OrgChartUtils;
use App\User;

class apiGlobalUsersController extends Controller
{
    public function syncUser(Request $request){
        try {
            $oUser = (object)$request->user;
            $fromSystem = $request->fromSystem;
            GlobalUsersUtils::globalUpdateFromSystem($oUser, $fromSystem);
        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'data' => null
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Se sincronizaron los usuarios correctamente",
            'data' => $oUser
            ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function updateGlobalPassword(Request $request){
        try {
            $oUser = (object)$request->user;
            GlobalUsersUtils::updateUserGlobalPassword($oUser, $request->fromSystem);
        } catch (\Throwable $th) {
           \Log::error($th);
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'data' => null
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Se actualizaron las contraseñas correctamente",
            'data' => $oUser
            ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function firstSyncWithExternalSystem(){
        GlobalUsersUtils::syncExternalWithGlobalUsers();
    }

    public function insertUserVsSystem(Request $request){
        try {
            $oUser = (object)$request->user;
            $global_id = $request->id_global;
            $fromSystem = $request->id_system;

            $systemUser = GlobalUsersUtils::getSystemUserId($global_id, $fromSystem);
            if(is_null($systemUser)){
                GlobalUsersUtils::insertSystemUser($global_id, $fromSystem, $oUser->id);
            }
        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'data' => null
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Se insertaron los usuarios correctamente",
            'data' => $oUser
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function getDirectManager($global_user_id){
        try {
            $pghUser_id = GlobalUsersUtils::getSystemUserId($global_user_id, 5);
            $oUser = User::find($pghUser_id);
            $superviser = OrgChartUtils::getExistDirectSuperviserOrgChartJob($oUser->org_chart_job_id);
            $superviser->password = null;
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'data' => null
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Se obtuvo el gerente correctamente",
            'data' => $superviser
            ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}

<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\GlobalUsersUtils;

class GlobalComunicationController extends Controller
{
    public function getPendingUser(Request $request){
        try{
            $company = $request->company;
        
            $pendingUser = globalUsersUtils::findGlobalUserWithCompany($company);
        }catch (\Throwable $th) {
            \Log::error($th);
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'data' => null
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
        if(count($pendingUser) > 0){
            return response()->json([
                'status' => 'success',
                'message' => "Se envian los usuarios faltantes",
                'data' => $pendingUser
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }else{
            return response()->json([
                'status' => 'success',
                'message' => "No se encontraron usuarios pendientes",
                'data' => null
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }

}

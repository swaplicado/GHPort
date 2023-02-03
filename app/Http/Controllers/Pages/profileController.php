<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;
use \App\Utils\delegationUtils;

class profileController extends Controller
{
    public function index(){
        $user = \Auth::user();
        // $user = delegationUtils::getUser();

        return view('users.profile')->with('user', $user);
    }

    public function updatePass(Request $request){
        if($request->password != $request->confirm_password){
            return json_encode(['success' => false, 'message' => 'Los campos contraseña deben coincidir', 'icon' => 'error']);
        }

        try {
            \DB::beginTransaction();
            $user = User::findOrFail(\Auth::user()->id);
            // $user = User::findOrFail(delegationUtils::getIdUser());
            $user->password = Hash::make($request->password);
            $user->changed_password = 1;
            $user->update();
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollback();
            return json_encode(['success' => false, 'message' => 'Error al actualizar el registro', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'message' => 'Registro actualizado', 'icon' => 'success']);
    }
}

<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Models\Adm\UsersPhotos;

class UsersPhotosController extends Controller
{
    public function saveUsersFromJSON($lUsers)
    {
        $lGhPortUsers = User::pluck('id', 'external_id_n');
        
        foreach ($lUsers as $jUser) {
            try {
                if (isset($lGhPortUsers[$jUser->id_employee])) {
                    $id_user = $lGhPortUsers[$jUser->id_employee];
                    $this->updUserPhoto($jUser, $id_user);
                }
            }
            catch (\Throwable $th) {
                $out = new \Symfony\Component\Console\Output\ConsoleOutput();
                $out->writeln($th);
            }
        }
    }

    private function updUserPhoto($jUser, $id){
        $oUsersPhotos = UsersPhotos::where('user_id', $oUser->id)->first();

        if(is_null($oUsersPhotos)){
            $this->insertUserPhotos($jUser, $id_user);
        }else{
            $oUsersPhotos->photo_base64_n = $jUser->photo;
            $oUsersPhotos->update();
        }
    }

    private function insertUserPhotos($jUser, $id){
        $oUsersPhotos = new UsersPhotos();
        $oUsersPhotos->user_id = $id;
        $oUsersPhotos->photo_base64_n = $jUser->photo;
        $oUsersPhotos->is_deleted = 0;
        $oUsersPhotos->created_by = 1;
        $oUsersPhotos->updated_by = 1;
        $oUsersPhotos->save();
    }
}

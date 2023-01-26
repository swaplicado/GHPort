<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Models\Adm\UsersPhotos;

class UsersPhotosController extends Controller
{
    public function saveUsersPhotosFromJSON($lPhotos)
    {
        $lGhPortUsers = User::pluck('id', 'external_id_n');
        
        foreach ($lPhotos->photos as $oPhoto) {
            try {
                if (isset($lGhPortUsers[$oPhoto->idEmployee])) {
                    $id_user = $lGhPortUsers[$oPhoto->idEmployee];
                    $this->updUserPhoto($oPhoto, $id_user);
                }
            }
            catch (\Throwable $th) {
                $out = new \Symfony\Component\Console\Output\ConsoleOutput();
                $out->writeln($th);
            }
        }
    }

    private function updUserPhoto($oPhoto, $id){
        $oUsersPhotos = UsersPhotos::where('user_id', $id)->first();

        if(is_null($oUsersPhotos)){
            $this->insertUserPhotos($oPhoto, $id);
        }else{
            $oUsersPhotos->photo_base64_n = $oPhoto->photo;
            $oUsersPhotos->update();
        }
    }

    private function insertUserPhotos($oPhoto, $id){
        $oUsersPhotos = new UsersPhotos();
        $oUsersPhotos->user_id = $id;
        $oUsersPhotos->photo_base64_n = $oPhoto->photo;
        $oUsersPhotos->is_deleted = 0;
        $oUsersPhotos->created_by = 1;
        $oUsersPhotos->updated_by = 1;
        $oUsersPhotos->save();
    }
}

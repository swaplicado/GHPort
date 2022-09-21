<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\User;
use App\Models\Adm\Job;
use Illuminate\Support\Str;
class UsersController extends Controller
{
    private $lJobs;

    public function saveUsersFromJSON($lUsers)
    {
        $lGhPortUsers = User::pluck('id', 'external_id');

        $this->lJobs = Job::pluck('id_job', 'external_id');
        foreach ($lUsers as $jUser) {
            try {
                if (isset($lGhPortUsers[$jUser->id_employee])) {
                    $id = $lGhPortUsers[$jUser->id_employee];
                    $this->updUser($jUser, $id);
                }
                else {
                    $this->insertUser($jUser);
                }
            }
            catch (\Throwable $th) {
                
            }
        }
    }

    private function updUser($jUser, $id)
    {
        User::where('id', $id)
                    ->update(
                            [
                                'num_employee' => $jUser->num_employee,
                                'first_name' => $jUser->lastname1,
                                'last_name' => $jUser->lastname2,
                                'full_name' => $jUser->lastname1.' '.$jUser->lastname2.', '.$jUser->firstname,
                                'is_active' => $jUser->is_active,
                                'is_delete' => $jUser->is_deleted,
                                'job_id' => $this->lJobs[$jUser->siie_job_id],
                            ]
                        );
    }

    private function insertUser($jUser)
    {
        if ((!$jUser->is_active) || $jUser->is_deleted) {
            return;
        }

        $name = str_replace([' LA ', ' DE ', ' LOS ', ' DEL ', ' LAS ', ' EL ', ], ' ', $jUser->firstname);
        $lastname1 = str_replace([' LA ', ' DE ', ' LOS ', ' DEL ', ' LAS ', ' EL ', ], ' ', $jUser->lastname1);
        $lastname2 = str_replace([' LA ', ' DE ', ' LOS ', ' DEL ', ' LAS ', ' EL ', ], ' ', $jUser->lastname2);
        // $usernameTmp = strtolower($jUser->num_employee.'.'.$jUser->lastname1.'.'.$jUser->lastname2);
        
        $names = explode(' ', $name);
        $lastname1s = explode(' ', $lastname1);
        $lastname2s = explode(' ', $lastname2);

        $usr = [];
        if (count($names) > 0 && count($lastname1s) > 0) {
            $usernameTmp = strtolower(str::slug($names[0]).'.'.str::slug($lastname1s[0]));
            $username = $this->getUserName($usernameTmp);
            $usr = User::where('username', $username)->first();
        }
        
        if ($usr != null) {
            if (count($names) > 1) {
                $usernameTmp = strtolower(str::slug($names[1]).'.'.str::slug($lastname1s[0]));
                $username = $this->getUserName($usernameTmp);
                $usr = User::where('username', $username)->first();
            }

            if ($usr != null) {
                if (count($lastname2s) > 0) {
                    $usernameTmp = strtolower(str::slug($names[0]).'.'.str::slug($lastname2s[0]));
                    $username = $this->getUserName($usernameTmp);
                    $usr = User::where('username', $username)->first();
                }
            }

            if ($usr != null) {
                $usernameTmp = strtolower($jUser->lastname1.'.'.$jUser->num_employee);
                $username = $this->getUserName($usernameTmp);
                $usr = User::where('username', $username)->first();

                if ($usr != null) {
                    return;
                }
            }
        }

        $oUser = new User();

        $oUser->username = $username;
        $oUser->password = bcrypt($username);
        $oUser->email = $jUser->email;
        $oUser->num_employee = $jUser->num_employee;
        $oUser->first_name = $jUser->lastname1;
        $oUser->last_name = $jUser->lastname2;
        $oUser->full_name = $jUser->lastname1.' '.$jUser->lastname2.', '.$jUser->firstname;
        $oUser->is_active = $jUser->is_active;
        $oUser->is_delete = $jUser->is_deleted;
        $oUser->external_id = $jUser->id_employee;
        $oUser->job_id = $this->lJobs[$jUser->siie_job_id];
        $oUser->created_by = 1;
        $oUser->updated_by = 1;

        $oUser->save();
    }

    private function getUserName($usernameTmp)
    {
        $username = str_replace(['ñ', 'Ñ'], 'n', $usernameTmp);
        $username = str_replace('-', '', $username);
        $username = str_replace(' ', '', $username);

        return $username;
    }
}

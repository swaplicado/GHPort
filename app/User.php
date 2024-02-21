<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Utils\orgChartUtils;
use App\Notifications\PasswordReset;
use App\Models\Adm\UsersPhotos;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable;
    use HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'employee_num',
        'first_name',
        'last_name',
        'full_name',
        'full_name_ui',
        'short_name',
        'benefits_date',
        'vacation_date',
        'last_admission_date',
        'last_dismiss_date_n',
        'birthday_n',
        'current_hire_log_id',
        'is_unionized',
        'org_chart_job_id',
        'is_active',
        'changed_password',
        'external_id_n',
        'is_delete',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function authorizedRole($rol){
        if(!is_array($rol)){
            abort_unless($this->rol_id == $rol, 401);
        }else{
            $continue = false;
            foreach($rol as $r){
                if($this->rol_id == $r){
                    $continue = true;
                    break;
                }
            }
            abort_unless($continue, 401);
        }
    }

    public function IsMyEmployee($id_employee){
        $arrOrgJobs = orgChartUtils::getAllChildsOrgChartJob($this->org_chart_job_id);
        $lEmployees = \DB::table('users as u')
                        ->where('u.is_active', 1)
                        ->where('u.is_delete', 0)
                        ->whereIn('u.org_chart_job_id', $arrOrgJobs)
                        ->select(
                            'u.id',
                            'u.employee_num',
                            'u.full_name_ui as employee',
                            'u.full_name',
                            'u.last_admission_date',
                            'u.org_chart_job_id',
                            'u.payment_frec_id',
                        )
                        ->get();

        $emp = collect($lEmployees)->where('id', $id_employee)->first();
        abort_unless(!is_null($emp), 401);
    }

    // public function sendPasswordResetNotification($token){
    //     $this->notify(new PasswordReset($token));
    // }

    public function sendPasswordResetNotification($token){
        Notification::route('mail', $this->institutional_mail)->notify(new PasswordReset($token));
    }

    public function getPhoto(){
        $photo64 = UsersPhotos::where('user_id', $this->id)
                            ->where('is_deleted', 0)
                            ->value('photo_base64_n');

        return $photo64;
    }

    public function getEmailForPasswordReset(){
        return $this->institutional_mail;
    }
}

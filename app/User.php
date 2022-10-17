<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Utils\orgChartUtils;

class User extends Authenticatable
{
    use Notifiable;

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
        'current_hire_log_id',
        'is_unionized',
        'is_active',
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

    public function authorizedRole($id_rol){
        abort_unless($this->rol_id == $id_rol, 401);
    }

    public function IsMyEmployee($id_employee){
        $arrOrgJobs = orgChartUtils::getDirectChildsOrgChartJob(\Auth::user()->org_chart_job_id);
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
}

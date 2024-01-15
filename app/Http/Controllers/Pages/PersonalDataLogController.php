<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\DatePersonal\registerProofPersonal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;
use Carbon\Carbon;


class PersonalDataLogController extends Controller
{
    public function index(){
        $dateEmployees = $this->getLogEmployees(Auth::user()->id);
        $startDate = Carbon::now()->startofmonth()->toDateString();
        $endDate = Carbon::now()->endofmonth()->toDateString();
        return view('data_personal.log_view_work_record') -> with ('dataUser', $dateEmployees)-> with ('startDate', $startDate) -> with ('endDate', $endDate);
    }


    public function getLogEmployees($id){
        $lEmployees = DB::table('register_proof_personal as pr')
                        ->join('users as u','u.id','=','pr.id_user_appl')
                        ->join('users as us','us.id','=','pr.id_user_proof')
                        ->select('pr.id_comp', 'u.full_name_ui', 'us.full_name', 'pr.isSalary', 'pr.created_at')
                        ->get();
        return $lEmployees;
    }


}







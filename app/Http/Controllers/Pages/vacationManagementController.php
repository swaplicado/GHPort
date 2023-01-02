<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\EmployeeVacationUtils;
use Carbon\Carbon;

class vacationManagementController extends Controller
{
    public function getEmployeeData(Request $request){
        try {
            $user = EmployeeVacationUtils::getEmployeeDataForMyVacation($request->employee_id);

            $now = Carbon::now();
            $initialCalendarDate = $now->addDays(1)->toDateString();

            $year = $now->year;

        } catch (\Throwable $th) {
            return json_encode(['succeess' => false, 'message' => 'Error al obtener los registros', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'oUser' => $user, 'initialCalendarDate' => $initialCalendarDate, 'year' => $year]);
    }
}
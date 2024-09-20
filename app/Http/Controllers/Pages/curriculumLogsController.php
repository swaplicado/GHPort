<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Curriculum\curriculum;
use App\User;
use GuzzleHttp\Client;
use App\Models\Adm\ClosingDates;
use App\Models\UserDataLogs\userDataLog;

class curriculumLogsController extends Controller
{
    public function index(){
        $lUser = User::select('id', 'full_name', 'updated_at')
        ->with([
            'curriculum' => function ($query) {
                $query->select('id', 'user_id', 'updated_at')
                    ->where('is_deleted', 0);
            },
            'userDataLog'=> function ($query) {
                $query->select('id', 'user_id', 'data_type_id', 'updated_at');
            }
        ])
        ->where('is_delete', 0)
        ->where('is_active', 1)
        ->where('id', '!=', 1)
        ->where('show_in_system', 1)
        ->get();

        $today = Carbon::now()->toDateString();
        $config = \App\Utils\Configuration::getConfigurations();
        $typeCV = $config->closing_dates_type->CV;
        $typeDP = $config->closing_dates_type->DP;
        $lastDateUpdateDP = ClosingDates::where(function ($query) use ($today) {
            $query->where('start_date', '<=', $today)
                ->orWhere('end_date', '<=', $today);
        })
        ->where('type_id', $typeDP)
        ->where('is_delete', 0)
        ->orderByRaw('ABS(DATEDIFF(start_date, ?))', [$today])
        ->first();

        $lastDateUpdateCV = ClosingDates::where(function ($query) use ($today) {
            $query->where('start_date', '<=', $today)
                ->orWhere('end_date', '<=', $today);
        })
        ->where('type_id', $typeCV)
        ->where('is_delete', 0)
        ->orderByRaw('ABS(DATEDIFF(start_date, ?))', [$today])
        ->first();

        foreach ($lUser as $user) {
            if ($lastDateUpdateDP) {
                $updated_at = null;

                foreach ($user->userDataLog as $dataLog) {
                    if ($dataLog->data_type_id == $typeDP) {
                        $updated_at = $dataLog->updated_at;
                        break;
                    }
                }

                $user->DP_updated_at = $updated_at;

                $user->colorDP = $updated_at ? 
                    ( 
                        Carbon::parse($updated_at)->lt(Carbon::parse($lastDateUpdateDP->start_date)) 
                            ? '#fad7a0' : '' 
                    ) : '#f5b7b1';
            } else {
                $updated_at = null;

                foreach ($user->userDataLog as $dataLog) {
                    if ($dataLog->data_type_id == $typeDP) {
                        $updated_at = $dataLog->updated_at;
                        break;
                    }
                }

                $user->DP_updated_at = $updated_at;
                $user->colorDP = '';
            }

            if ($lastDateUpdateCV) {
                if ($user->curriculum) {
                    $updated_at = null;

                    foreach ($user->userDataLog as $dataLog) {
                        if ($dataLog->data_type_id == $typeCV) {
                            $updated_at = $dataLog->updated_at;
                            break;
                        }
                    }

                    $user->CV_updated_at = $updated_at;

                    $user->colorCV = $updated_at ?
                        (
                            Carbon::parse($updated_at)->lt(Carbon::parse($lastDateUpdateCV->start_date))
                                ? '#fad7a0' : ''
                        ) : '#f5b7b1';
                } else {
                    $user->colorCV = '#f5b7b1';
                }
            } else {
                $updated_at = null;

                    foreach ($user->userDataLog as $dataLog) {
                        if ($dataLog->data_type_id == $typeCV) {
                            $updated_at = $dataLog->updated_at;
                            break;
                        }
                    }

                    $user->CV_updated_at = $updated_at;
                $user->colorCV = '';
            }
        }

        return view('curriculum.curriculumLogs')->with('lUser', $lUser)
                                                ->with('lastDateUpdateDP', $lastDateUpdateDP)
                                                ->with('lastDateUpdateCV', $lastDateUpdateCV);
    }
}

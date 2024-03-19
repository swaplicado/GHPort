<?php namespace App\Utils;

use App\User;
use App\Models\Adm\OrgChartJob;
class usersInSystemUtils {
    public static function FilterUsersInSystem($lUsers, $key) {
        $lUsersNotInSystem = User::where('is_delete', 0)
                                ->where('show_in_system', 0)->get();

        $lUsersNotInSystemIds = $lUsersNotInSystem->pluck('id')->toArray();

        $type = gettype($lUsers);

        $lUsers = collect($lUsers);

        $lUsers = $lUsers->filter(function ($user) use ($lUsersNotInSystemIds, $key) {
            $userType = gettype($user);
            if ($userType == 'object') {
                return !in_array($user->$key, $lUsersNotInSystemIds);
            }
            else if ($userType == 'array') {
                return !in_array($user[$key], $lUsersNotInSystemIds);
            }
        })->values();

        if ($type == 'object') {
            return $lUsers;
        }
        else if ($type == 'array') {
            return $lUsers->toArray();
        }
    }

    public static function getUsersInSystem() {
        $lUsersInSystem = User::where('is_deleted', 0)
                                ->where('show_in_system', 1)->get();

        return $lUsersInSystem;
    }

    public static function getUsersNotInSystem() {
        $lUsersNotInSystem = User::where('is_deleted', 0)
                                ->where('show_in_system', 0)->get();

        return $lUsersNotInSystem;
    }

    public static function FilterUsersByOfficeOrgChartJob($lUsers, $key){
        $lUsers = collect($lUsers);
        $type = gettype($lUsers);

        $lOrgChartJobsNoOffice = OrgChartJob::where('is_deleted', 0)->where('is_office', 0)->get()->pluck('id_org_chart_job')->toArray();
        
        $lUsers = $lUsers->filter(function ($user) use ($lOrgChartJobsNoOffice, $key) {
            $userType = gettype($user);
            if ($userType == 'object') {
                return !in_array($user->$key, $lOrgChartJobsNoOffice);
            }
            else if ($userType == 'array') {
                return !in_array($user[$key], $lOrgChartJobsNoOffice);
            }
        })->values();

        if ($type == 'object') {
            return $lUsers;
        }
        else if ($type == 'array') {
            return $lUsers->toArray();
        }
    }
}
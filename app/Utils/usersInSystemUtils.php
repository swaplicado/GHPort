<?php namespace App\Utils;

use App\User;
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
}
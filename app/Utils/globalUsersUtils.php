<?php namespace App\Utils;

use App\Constants\SysConst;
use GuzzleHttp\Client;
use GuzzleHttp\Request;
use GuzzleHttp\Exception\RequestException;
use App\Models\GlobalUsers\globalUser;
use App\Models\GlobalUsers\userVsSystem;

class GlobalUsersUtils {

    /**
     * The function inserts a new global user into the database and associates them with a system.
     * 
     * @param $systemId The systemId parameter is the ID of the system in which the user is being
     * inserted.
     * @param $userSystemId The userSystemId parameter is the unique identifier for the user within the
     * system. It is used to associate the user with a specific system in the userVsSystem table.
     * @param $username The username of the new global user.
     * @param $password The password parameter is the password for the new global user.
     * @param $email The email parameter is the email address of the user.
     * @param $full_name The full name of the user.
     * @param $external_id The external_id parameter is used to store an external identifier for the
     * user. This can be any unique identifier that is used in another system or application to
     * identify the user.
     * @param $employee_num The employee number of the user.
     * 
     * @return $globalUser the newly created globalUser object.
     */
    public static function insertNewGlobalUser($systemId, $userSystemId, $username, $password, $email, $full_name, $external_id, $employee_num, $is_active = 1, $is_deleted = 0){
        $globalUser = new globalUser();
        $globalUser->username = $username;
        $globalUser->password = $password;
        $globalUser->email = $email;
        $globalUser->full_name = $full_name;
        $globalUser->external_id = $external_id;
        $globalUser->employee_num = $employee_num;
        $globalUser->is_active = $is_active;
        $globalUser->is_deleted = $is_deleted;
        $globalUser->save();

        $userSystem = new userVsSystem();
        $userSystem->global_user_id = $globalUser->id_global_user;
        $userSystem->system_id = $systemId;
        $userSystem->user_system_id = $userSystemId;
        $userSystem->save();

        return $globalUser;
    }

    /**
     * The function updates the properties of a global user object with the provided values.
     * 
     * @param id The id parameter is the unique identifier of the global user that needs to be updated.
     * @param username The username of the global user. It is used to identify the user and is
     * typically unique for each user.
     * @param password The password parameter is the new password for the global user.
     * @param email The email parameter is the new email address for the global user.
     * @param full_name The full name of the user.
     * @param external_id The external_id parameter is used to store an external identifier for the
     * user. This can be any unique identifier that is used in an external system or service to
     * identify the user.
     * @param employee_num The employee_num parameter is the employee number of the global user. It is
     * used to uniquely identify the user within the system.
     */
    public static function updateGlobalUser($id, $username, $password, $email, $full_name, $external_id, $employee_num, $is_active = 1, $is_deleted = 0){
        $globalUser = globalUser::find($id);
        $globalUser->username = $username;
        $globalUser->password = $password;
        $globalUser->email = $email;
        $globalUser->full_name = $full_name;
        $globalUser->external_id = $external_id;
        $globalUser->employee_num = $employee_num;
        $globalUser->is_active = $is_active;
        $globalUser->is_deleted = $is_deleted;
        $globalUser->update();
    }

    public static function findSystemUser($globalUserId, $systemId, $userSystemId){
        $userSystem = userVsSystem::where('global_user_id', $globalUserId)->where('system_id', $systemId)->where('user_system_id', $userSystemId)->first();
        if(is_null($userSystem)){
            return json_encode(['success' => false, 'userSystem' => null, 'message' => 'No global user found']);
        }
        
        return json_encode(['success' => true, 'userSystem' => $userSystem]);
    }

    /**
     * The function inserts a new user system record into the database.
     * 
     * @param globalUserId The global user ID is a unique identifier for a user in the system. It is
     * used to associate the user with the system they are being inserted into.
     * @param systemId The systemId parameter represents the ID of the system that the user is being
     * inserted into.
     * @param userSystemId The userSystemId parameter is the unique identifier for the user within the
     * system. It is used to associate the user with the specific system they belong to.
     */
    public static function insertSystemUser($globalUserId, $systemId, $userSystemId){
        $userSystem = new userVsSystem();
        $userSystem->global_user_id = $globalUserId;
        $userSystem->system_id = $systemId;
        $userSystem->user_system_id = $userSystemId;
        $userSystem->save();
    }

    public static function findGlobalUser($username = null, $full_name = null, $external_id = null, $employee_num = null){
        $globalUser = null;

        // $query = globalUser::where('is_active', 1)->where('is_deleted', 0);
        $query = globalUser::query();

        if(!is_null($username)){
            $query->where('username', $username);
        }

        if(!is_null($full_name)){
            $query->where('full_name', $full_name);
        }
        if(!is_null($external_id)){
            $query->where('external_id', $external_id);
        }
        if(!is_null($employee_num)){
            $query->where('employee_num', $employee_num);
        }

        $query->get();

        if($query->count() == 1){
            $globalUser = $query->first();

            return json_encode(['success' => true, 'globalUser' => $globalUser]);
        }else if($query->count() == 0){
            return json_encode(['success' => true, 'globalUser' => null, 'message' => 'No global user found']);
        }else if($query->count() > 0){
            return json_encode(['success' => false, 'message' => 'Multiple global users found for ' . $username . ' ' . $full_name . ' ' . $external_id . ' ' . $employee_num ]);
        }
    }

    public static function loginToUniv(){
        $config = \App\Utils\Configuration::getConfigurations();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];

        $url =  \DB::connection('mysqlGlobalUsers')
                    ->table('systems')
                    ->where('id_system', SysConst::SYSTEM_UNIVAETH)
                    ->value('url');

        $client = new Client([
            'base_uri' => $url,
            'timeout' => 30.0,
            'headers' => $headers,
            'verify' => false
        ]);

        $univUser = $config->univUser;
        $body = '{
                "username": "'. $univUser->user .'",
                "password": "'. $univUser->password .'"
        }';

        $request = new \GuzzleHttp\Psr7\Request('POST', 'login', $headers, $body);
        $response = $client->sendAsync($request)->wait();
        $jsonString = $response->getBody()->getContents();
        $data = json_decode($jsonString);

        return $data;
    }

    public static function getUserFromUnivAeth($token_type, $access_token, $full_name = null, $username = null, $external_id = null, $employee_num = null){
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => $token_type.' '.$access_token
        ];

        $url =  \DB::connection('mysqlGlobalUsers')
                    ->table('systems')
                    ->where('id_system', SysConst::SYSTEM_UNIVAETH)
                    ->value('url');
        
        $client = new Client([
            'base_uri' => $url,
            'timeout' => 30.0,
            'headers' => $headers
        ]);

        $body = '{
            "full_name": "'.$full_name.'",
            "username": "'.$username.'",
            "external_id": "'.$external_id.'",
            "employee_num": "'.$employee_num.'"
        }';

        $request = new \GuzzleHttp\Psr7\Request('POST', 'getUserToGlobalUser', $headers, $body);
        $response = $client->sendAsync($request)->wait();
        $jsonString = $response->getBody()->getContents();
        $data = json_decode($jsonString);

        return $data;
    }

    public static function getListUsersFromUnivAeth($token_type, $access_token, $lUsers){
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => $token_type.' '.$access_token
        ];

        $url =  \DB::connection('mysqlGlobalUsers')
                    ->table('systems')
                    ->where('id_system', SysConst::SYSTEM_UNIVAETH)
                    ->value('url');
        
        $client = new Client([
            'base_uri' => $url,
            'timeout' => 30.0,
            'headers' => $headers
        ]);

        $jsonUsers = json_encode($lUsers);

        $body = json_encode(array("lUsers" => $jsonUsers));

        $request = new \GuzzleHttp\Psr7\Request('POST', 'getListUsers', $headers, $body);
        $response = $client->sendAsync($request)->wait();
        $jsonString = $response->getBody()->getContents();
        $data = json_decode($jsonString);

        return $data;
    }

    public static function loginToCAP(){
        $config = \App\Utils\Configuration::getConfigurations();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];

        $url =  \DB::connection('mysqlGlobalUsers')
                    ->table('systems')
                    ->where('id_system', SysConst::SYSTEM_CAP)
                    ->value('url');

        $client = new Client([
            'base_uri' => $url,
            'timeout' => 30.0,
            'headers' => $headers,
            'verify' => false
        ]);

        $CAPUser = $config->CAPUser;
        $body = '{
                "email": "'.$CAPUser->user.'",
                "password": "'.$CAPUser->password.'"
        }';

        $response = $client->request('POST', 'login' , [
            'body' => $body
        ]);

        $jsonString = $response->getBody()->getContents();

        $data = json_decode($jsonString);

        return $data;
    }

    public static function getListUsersFromCAP($token_type, $access_token, $lUsers){
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => $token_type.' '.$access_token
        ];

        $url =  \DB::connection('mysqlGlobalUsers')
                    ->table('systems')
                    ->where('id_system', SysConst::SYSTEM_CAP)
                    ->value('url');
        
        $client = new Client([
            'base_uri' => $url,
            'timeout' => 30.0,
            'headers' => $headers
        ]);

        $jsonUsers = json_encode($lUsers);

        $body = json_encode(array("lUsers" => $jsonUsers));

        $request = new \GuzzleHttp\Psr7\Request('POST', 'getListUsers', $headers, $body);
        $response = $client->sendAsync($request)->wait();
        $jsonString = $response->getBody()->getContents();
        $data = json_decode($jsonString);

        return $data;
    }

    public static function loginToEval(){
        $config = \App\Utils\Configuration::getConfigurations();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];

        $url =  \DB::connection('mysqlGlobalUsers')
                    ->table('systems')
                    ->where('id_system', SysConst::SYSTEM_EVALUACIONDESEMPENO)
                    ->value('url');

        $client = new Client([
            'base_uri' => $url,
            'timeout' => 30.0,
            'headers' => $headers,
            'verify' => false
        ]);

        $evalUser = $config->evalUser;
        $body = '{
                "name": "'.$evalUser->user.'",
                "password": "'.$evalUser->password.'"
        }';

        $response = $client->request('POST', 'login' , [
            'body' => $body
        ]);

        $jsonString = $response->getBody()->getContents();

        $data = json_decode($jsonString);

        return $data;
    }

    public static function getListUsersFromEval($token_type, $access_token, $lUsers){
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => $token_type.' '.$access_token
        ];

        $url =  \DB::connection('mysqlGlobalUsers')
                    ->table('systems')
                    ->where('id_system', SysConst::SYSTEM_EVALUACIONDESEMPENO)
                    ->value('url');

        $client = new Client([
            'base_uri' => $url,
            'timeout' => 30.0,
            'headers' => $headers
        ]);

        $jsonUsers = json_encode($lUsers);

        $body = json_encode(array("lUsers" => $jsonUsers));

        $request = new \GuzzleHttp\Psr7\Request('POST', 'getListUsers', $headers, $body);
        $response = $client->sendAsync($request)->wait();
        $jsonString = $response->getBody()->getContents();
        $data = json_decode($jsonString);

        return $data;
    }
}
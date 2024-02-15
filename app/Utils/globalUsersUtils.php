<?php namespace App\Utils;

use App\Constants\SysConst;
use GuzzleHttp\Client;
use GuzzleHttp\Request;
use GuzzleHttp\Exception\RequestException;
use App\Models\GlobalUsers\globalUser;
use App\Models\GlobalUsers\userVsSystem;
use App\User;
use Carbon\Carbon;
use Exception;

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
     * @param $id_global_user The id parameter is the unique identifier of the global user that needs to be updated.
     * @param $username The username of the global user. It is used to identify the user and is
     * typically unique for each user.
     * @param $password The password parameter is the new password for the global user.
     * @param $email The email parameter is the new email address for the global user.
     * @param $full_name The full name of the user.
     * @param $external_id The external_id parameter is used to store an external identifier for the
     * user. This can be any unique identifier that is used in an external system or service to
     * identify the user.
     * @param $employee_num The employee_num parameter is the employee number of the global user. It is
     * used to uniquely identify the user within the system.
     */
    public static function updateGlobalUser($id_global_user, $username, $password, $email, $full_name, $external_id, $employee_num, $is_active = 1, $is_deleted = 0){
        $globalUser = globalUser::find($id_global_user);
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

    public static function getSystemUserId($globalUserId, $systemId){
        $userSystem = userVsSystem::where('global_user_id', $globalUserId)->where('system_id', $systemId)->first();
        if(is_null($userSystem)){
            return null;
        }
        
        return $userSystem->user_system_id;
    }

    /**
     * The function inserts a new user system record into the database.
     * 
     * @param $globalUserId The global user ID is a unique identifier for a user in the system. It is
     * used to associate the user with the system they are being inserted into.
     * @param $systemId The systemId parameter represents the ID of the system that the user is being
     * inserted into.
     * @param $userSystemId The userSystemId parameter is the unique identifier for the user within the
     * system. It is used to associate the user with the specific system they belong to.
     */
    public static function insertSystemUser($globalUserId, $systemId, $userSystemId){
        $userSystem = new userVsSystem();
        $userSystem->global_user_id = $globalUserId;
        $userSystem->system_id = $systemId;
        $userSystem->user_system_id = $userSystemId;
        $userSystem->save();
    }

    /**
     * The function `findGlobalUser` searches for a global user based on the provided parameters and
     * returns the result in JSON format.
     * 
     * @param $username The username of the global user you want to find.
     * @param $full_name The full name of the global user you want to find.
     * @param $external_id The external_id parameter is used to search for a global user based on their
     * external ID.
     * @param $employee_num The employee number of the global user.
     * 
     * @return $ a JSON-encoded string. The returned string contains information about the success of the
     * search operation and the global user found (if any).
     */
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

    public static function findGlobalUserByIdSystem($userSystemId, $systemId){
        $query = globalUser::join('users_vs_systems as us', 'us.global_user_id', '=', 'global_users.id_global_user')
                            ->where('us.user_system_id', $userSystemId)
                            ->where('us.system_id', $systemId)
                            ->select('global_users.*')
                            ->get();

        if($query->count() == 1){
            $globalUser = $query->first();
            return json_encode(['success' => true, 'globalUser' => $globalUser]);
        }else if($query->count() == 0){
            return json_encode(['success' => true, 'globalUser' => null, 'message' => 'No global user found']);
        }else if($query->count() > 0){
            return json_encode(['success' => false, 'message' => 'Multiple global users found for userSystemId: ' . $userSystemId . ' systemId: ' . $systemId ]);
        }
    }

    /**
     * The function `loginToUniv` is a PHP function that sends a POST request to a university login
     * endpoint with a username and password, and returns the response data.
     * 
     * @return $ the data received from the login request to the university system.
     */
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

    /**
     * The function `getUserFromUnivAeth` retrieves user data from a remote server using a token for
     * authentication.
     * 
     * @param $token_type The token type is a string that specifies the type of token being used for
     * authentication. It could be "Bearer" or any other type specified by the authentication system.
     * @param $access_token The access token is a security credential that is obtained after a user
     * successfully authenticates with the system. It is used to authorize and authenticate subsequent
     * API requests.
     * @param $full_name The full name of the user.
     * @param $username The username of the user in the university's system.
     * @param $external_id The external_id parameter is used to specify the unique identifier of the
     * user in the external system. It can be any value that uniquely identifies the user in the
     * external system, such as an ID number or a username.
     * @param $employee_num The employee number of the user in the university system.
     * 
     * @return $ the data obtained from the API call.
     */
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

    /**
     * The function getListUsersFromUnivAeth retrieves a list of users from a specific university using
     * an access token and returns the data.
     * 
     * @param $token_type The `token_type` parameter is the type of token being used for authentication.
     * It is typically a string value indicating the type of token, such as "Bearer" or "Token".
     * @param $access_token The access_token parameter is a token that is used to authenticate the user
     * and authorize access to the API. It is typically obtained through an authentication process,
     * such as OAuth, and is used to validate the user's identity and permissions when making API
     * requests.
     * @param $lUsers An array of users.
     * 
     * @return $ the data obtained from the API call made to the specified URL.
     */
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

    /**
     * The function `loginToCAP` is used to authenticate a user with the CAP system by sending a POST
     * request with the user's email and password.
     * 
     * @return the data received from the CAP login API.
     */
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

    /**
     * The function getListUsersFromCAP retrieves a list of users from a remote API using a token for
     * authentication.
     * 
     * @param $token_type The `token_type` parameter is the type of token being used for authentication.
     * It is typically a string value indicating the type of token, such as "Bearer" or "Token".
     * @param $access_token The access_token parameter is a token that is used to authenticate the user
     * and authorize access to the API. It is typically obtained through an authentication process,
     * such as OAuth, and is used to validate the user's identity and permissions when making API
     * requests.
     * @param $lUsers An array of users.
     * 
     * @return $ the data obtained from the CAP API after making a POST request to the 'getListUsers'
     * endpoint.
     */
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

    /**
     * The function `loginToEval()` is used to login to a specific system and return the response data.
     * 
     * @return $ the data obtained from the login request to the evaluation system.
     */
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

    /**
     * The function getListUsersFromEval sends a POST request to a specified URL with a list of users
     * and returns the response data.
     * 
     * @param $token_type The `token_type` parameter is the type of token used for authentication. It is
     * typically a string value indicating the type of token, such as "Bearer" or "Token".
     * @param $access_token The access_token parameter is a token that is used to authenticate the user
     * and authorize access to the API. It is typically obtained by the user during the authentication
     * process.
     * @param $lUsers An array of users.
     * 
     * @return $ the data obtained from the API call.
     */
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

    /**
     * The function syncUserToUniv sends a POST request to a specified URL with a user object and
     * returns the response.
     * 
     * @param $token_type The `token_type` parameter is a string that represents the type of token being
     * used for authentication. It is typically used in the `Authorization` header to specify the type
     * of token being sent. Examples of token types include "Bearer" and "Token".
     * @param $access_token The access_token parameter is a token that is used to authenticate the user
     * and authorize access to the API. It is typically obtained through an authentication process,
     * such as OAuth, and is used to validate the user's identity and permissions when making API
     * requests.
     * @param $oUser An object representing a user. It contains properties such as username, password,
     * email, etc.
     * 
     * @return $ the data received from the API call made to sync the user to the university system.
     */
    public static function syncUserToUniv($token_type, $access_token, $oUser, $type){
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

        if(isset($oUser->password)){
            $oUser->pass = $oUser->password;
        }

        $body = json_encode(['user' => $oUser, 'type' => $type]);

        $request = new \GuzzleHttp\Psr7\Request('POST', 'syncUser', $headers, $body);
        $response = $client->sendAsync($request)->wait();
        $jsonString = $response->getBody()->getContents();
        $data = json_decode($jsonString);
        
        return $data;
    }

    public static function syncListUserToUniv($token_type, $access_token, $lUser, $type){
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => $token_type.' '.$access_token
        ];

        $url = \DB::connection('mysqlGlobalUsers')
                    ->table('systems')
                    ->where('id_system', SysConst::SYSTEM_UNIVAETH)
                    ->value('url');
        
        $client = new Client([
            'base_uri' => $url,
            'timeout' => 30.0,
            'headers' => $headers
        ]);

        $body = json_encode(['lUser' => $lUser, 'type' => $type]);

        $request = new \GuzzleHttp\Psr7\Request('POST', 'syncListUser', $headers, $body);
        $response = $client->sendAsync($request)->wait();
        $jsonString = $response->getBody()->getContents();
        $data = json_decode($jsonString);
        
        return $data;
    }

    /**
     * The function syncs a user to a CAP system using a token and user data.
     * 
     * @param $token_type The `token_type` parameter is the type of token used for authentication. It is
     * typically a string value indicating the type of token, such as "Bearer" or "Token".
     * @param $access_token The access_token parameter is a token that is used for authentication and
     * authorization. It is typically obtained by the user during the authentication process and is
     * used to access protected resources on the server.
     * @param $oUser The parameter `` is an object that represents the user data that needs to be
     * synchronized to the CAP (Central Authentication Platform). It contains information such as the
     * user's name, email, username, password, and any other relevant details required by the CAP
     * system.
     * 
     * @return $ the data received from the CAP system after synchronizing the user.
     */
    public static function syncUserToCAP($token_type, $access_token, $oUser, $type){
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

        $body = json_encode(['user' => $oUser]);

        $request = new \GuzzleHttp\Psr7\Request('POST', 'syncUser', $headers, $body);
        $response = $client->sendAsync($request)->wait();
        $jsonString = $response->getBody()->getContents();
        $data = json_decode($jsonString);

        return $data;
    }

    /**
     * The function syncUserToEval sends a POST request to a specified URL with a user object as the
     * body, using the provided access token and token type for authorization.
     * 
     * @param $token_type The `token_type` parameter is a string that represents the type of token being
     * used for authentication. It is typically used in the `Authorization` header to specify the type
     * of token being sent. Examples of token types include "Bearer" and "Token".
     * @param $access_token The access_token parameter is a token that is used for authentication and
     * authorization. It is typically obtained by the user during the authentication process and is
     * used to access protected resources on the server.
     * @param $oUser The parameter `` is an object that represents a user. It contains information
     * about the user such as their name, email, and other relevant details.
     * 
     * @return $ the data received from the API call.
     */
    public static function syncUserToEval($token_type, $access_token, $oUser, $type){
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

        $body = json_encode(['user' => $oUser]);

        $request = new \GuzzleHttp\Psr7\Request('POST', 'syncUser', $headers, $body);
        $response = $client->sendAsync($request)->wait();
        $jsonString = $response->getBody()->getContents();
        $data = json_decode($jsonString);

        return $data;
    }

    public static function syncToPGH($oUser){
        $userPGH = user::findOrFail($oUser->id_user_system);
        $userPGH->username = $oUser->username;
        $userPGH->password = $oUser->pass;
        $userPGH->email = $oUser->email;
        $userPGH->update();
    }

    public static function globalUpdateFromSystem($oUser, $fromSystem){
        $result = json_decode(GlobalUsersUtils::findGlobalUserByIdSystem($oUser->id, $fromSystem));

        $globalUser = null;
        if($result->success){
            $globalUser = $result->globalUser; 
            if(!is_null($globalUser)){
                try {
                    GlobalUsersUtils::updateGlobalUser(
                        $globalUser->id_global_user,
                        $oUser->username,
                        $oUser->pass,
                        $oUser->email,
                        $oUser->full_name,
                        $oUser->external_id,
                        $oUser->employee_num,
                        $oUser->is_active,
                        $oUser->is_delete
                    );
                } catch (\Throwable $th) {
                    \Log::error($th);
                    throw new Exception($th->getMessage());
                }

                if($fromSystem != SysConst::SYSTEM_PGH){
                    try {
                        $userPGHId = GlobalUsersUtils::getSystemUserId($globalUser->id_global_user, SysConst::SYSTEM_PGH);
                        if(!is_null($userPGHId)){
                            $oUser->id_user_system = $userPGHId;
                            GlobalUsersUtils::syncToPGH($oUser);
                        }
                    } catch (\Throwable $th) {
                        \Log::error($th);
                        programmedTaskUtils::createTaskToUsersGlobal(SysConst::TASK_UPDATE_PGH, $oUser, SysConst::SYSTEM_GLOBAL_USERS);
                    }
                }

                if($fromSystem != SysConst::SYSTEM_UNIVAETH){
                    try {
                        $userUnivId = GlobalUsersUtils::getSystemUserId($globalUser->id_global_user, SysConst::SYSTEM_UNIVAETH);
                        if(!is_null($userUnivId)){
                            $oUser->id_user_system = $userUnivId;
                            $loginUniv = GlobalUsersUtils::loginToUniv();
                            if($loginUniv->status == 'success'){
                                $resultUniv = GlobalUsersUtils::syncUserToUniv($loginUniv->token_type, $loginUniv->access_token, $oUser, SysConst::USERGLOBAL_UPDATE);
                                if($resultUniv->status != 'success'){
                                    programmedTaskUtils::createTaskToUsersGlobal(SysConst::TASK_UPDATE_UNIV, $oUser, SysConst::SYSTEM_PGH);
                                }
                            }
                        }
                    } catch (\Throwable $th) {
                        \Log::error($th);
                        try {
                            $oUser->id_user_system = $userUnivId;
                            $oUser->id_global_user = $globalUser->id_global_user;
                            programmedTaskUtils::createTaskToUsersGlobal(SysConst::TASK_UPDATE_UNIV, $oUser, SysConst::SYSTEM_PGH);
                        } catch (\Throwable $th) {
                            \Log::error($th);
                        }
                    }
                }
                    
                if($fromSystem != SysConst::SYSTEM_CAP){
                    try {
                        $userCAPId = GlobalUsersUtils::getSystemUserId($globalUser->id_global_user, SysConst::SYSTEM_CAP);
                        if(!is_null($userCAPId)){
                            $oUser->id_user_system = $userCAPId;
                            $loginCAP = GlobalUsersUtils::loginToCAP();
                            if($loginCAP->status == 'success'){
                                $resultCAP = GlobalUsersUtils::syncUserToCAP($loginCAP->token_type, $loginCAP->access_token, $oUser, SysConst::USERGLOBAL_UPDATE);
                                if($resultCAP->status != 'success'){
                                    programmedTaskUtils::createTaskToUsersGlobal(SysConst::TASK_UPDATE_CAP, $oUser, SysConst::SYSTEM_PGH);
                                }
                            }
                        }
                    } catch (\Throwable $th) {
                        \Log::error($th);
                        programmedTaskUtils::createTaskToUsersGlobal(SysConst::TASK_UPDATE_CAP, $oUser, SysConst::SYSTEM_PGH);
                    }
                }
        
                if($fromSystem != SysConst::SYSTEM_EVALUACIONDESEMPENO){
                    try {
                        $userEvalId = GlobalUsersUtils::getSystemUserId($globalUser->id_global_user, SysConst::SYSTEM_EVALUACIONDESEMPENO);
                        if(!is_null($userEvalId)){
                            $oUser->id_user_system = $userEvalId;
                            $loginEval = GlobalUsersUtils::loginToEval();
                            if($loginEval->status == 'success'){
                                GlobalUsersUtils::syncUserToEval($loginEval->token_type, $loginEval->access_token, $oUser, SysConst::USERGLOBAL_UPDATE);
                            }
                        }
                    } catch (\Throwable $th) {
                        \Log::error($th);
                        programmedTaskUtils::createTaskToUsersGlobal(SysConst::TASK_UPDATE_EVAL, $oUser, SysConst::SYSTEM_PGH);
                    }
                }
            }else{
                \Log::error('No se encontro el usuario global user_id: ' . $oUser->id . ' system_id: ' . $fromSystem);
            }
        }
    }

    public static function getUserFromSystem($userIdSystem, $system_id){
        $loginData = null;
        $route = null;

        switch ($system_id) {
            case SysConst::SYSTEM_PGH:
                $oUser = User::find($userIdSystem);
                $oUser->pass = $oUser->password;
                $oUser->external_id = $oUser->external_id_n;
                if(!is_null($oUser)){
                    return json_decode(json_encode(['status' => 'success',
                                        'message' => "Se encontró el usuario correctamente",
                                        'data' => $oUser
                                        ]));
                }else{
                    return json_decode(json_encode(['status' => 'error',
                                        'message' => "No se encontró el usuario id: ".$userIdSystem,
                                        'data' => null
                                        ]));
                }
                break;
            case SysConst::SYSTEM_UNIVAETH:
                $loginData = GlobalUsersUtils::loginToUniv();
                $route = 'getUserById/';
                break;
            case SysConst::SYSTEM_CAP:
                $loginData = GlobalUsersUtils::loginToCAP();
                $route = 'getUserById/';
                break;
            case SysConst::SYSTEM_EVALUACIONDESEMPENO:
                $loginData = GlobalUsersUtils::loginToEval();
                $route = 'getUserById/';
                break;
            default:
                break;
        }

        if($loginData->status == 'success'){
            $token_type = $loginData->token_type;
            $access_token = $loginData->access_token;
            
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => $token_type.' '.$access_token
            ];
    
            $url =  \DB::connection('mysqlGlobalUsers')
                        ->table('systems')
                        ->where('id_system', $system_id)
                        ->value('url');
    
            $client = new Client([
                'base_uri' => $url,
                'timeout' => 30.0,
                'headers' => $headers
            ]);
    
            $request = new \GuzzleHttp\Psr7\Request('GET', $route.$userIdSystem , $headers);
            $response = $client->sendAsync($request)->wait();
            $jsonString = $response->getBody()->getContents();
            $data = json_decode($jsonString);
    
            return $data;
        }
    }

    /**
     * Metodo solo para aplicaciones externas, si es desde pgh usar globalUpdateFromSystem
     */
    public static function updateUserGlobalPassword($user, $fromSystem){
        try {
            $globalUser = globalUser::join('users_vs_systems', 'users_vs_systems.global_user_id', '=', 'global_users.id_global_user')
                                    ->where('users_vs_systems.user_system_id', $user->user_system_id)
                                    ->where('users_vs_systems.system_id', $fromSystem)
                                    ->first();
            
            $globalUser->username = $user->username;
            $globalUser->email = $user->email;                        
            $globalUser->password = $user->pass;
            $globalUser->update();
        } catch (\Throwable $th) {
            \Log::error($th);
            $globalUser->id_user_system = $user->user_system_id;
            programmedTaskUtils::createTaskToUsersGlobal(SysConst::TASK_UPDATE_PASSWORD_USERGLOBAL, $globalUser, SysConst::SYSTEM_UNIVAETH);
            throw new Exception($th->getMessage());
        }

        if($fromSystem != SysConst::SYSTEM_PGH){
            try {
                $userPghId = globalUser::join('users_vs_systems', 'users_vs_systems.global_user_id', '=', 'global_users.id_global_user')
                                        ->where('users_vs_systems.global_user_id', $globalUser->id_global_user)
                                        ->where('users_vs_systems.system_id', SysConst::SYSTEM_PGH)
                                        ->value('users_vs_systems.user_system_id');

                self::updatePGHPassword($user, $userPghId);
            } catch (\Throwable $th) {
                \Log::error($th);
                $globalUser->id_user_system = null;
                programmedTaskUtils::createTaskToUsersGlobal(SysConst::TASK_UPDATE_PASSWORD_PGH, $globalUser, SysConst::SYSTEM_GLOBAL_USERS);
            }
        }

        if($fromSystem != SysConst::SYSTEM_UNIVAETH){
            try {
                $loginUniv = self::loginToUniv();
                if($loginUniv->status == 'success'){
                    $userUnivId = globalUser::join('users_vs_systems', 'users_vs_systems.global_user_id', '=', 'global_users.id_global_user')
                                        ->where('users_vs_systems.global_user_id', $globalUser->id_global_user)
                                        ->where('users_vs_systems.system_id', SysConst::SYSTEM_UNIVAETH)
                                        ->value('users_vs_systems.user_system_id');

                    $user->user_system_id = $userUnivId;
                    $resultUniv = self::updateUnivPassword($loginUniv->token_type, $loginUniv->access_token, $user);
                    if($resultUniv->status != 'success'){
                        $globalUser->id_user_system = null;
                        programmedTaskUtils::createTaskToUsersGlobal(SysConst::TASK_UPDATE_PASSWORD_UNIV, $globalUser, SysConst::SYSTEM_GLOBAL_USERS);
                    }
                }
            } catch (\Throwable $th) {
                \Log::error($th);
                $globalUser->id_user_system = null;
                programmedTaskUtils::createTaskToUsersGlobal(SysConst::TASK_UPDATE_PASSWORD_UNIV, $globalUser, SysConst::SYSTEM_GLOBAL_USERS);
            }
        }

        if($fromSystem != SysConst::SYSTEM_CAP){
            try {
                $loginCAP = self::loginToCAP();
                if($loginCAP->status == 'success'){
                    $userCapId = globalUser::join('users_vs_systems', 'users_vs_systems.global_user_id', '=', 'global_users.id_global_user')
                                        ->where('users_vs_systems.global_user_id', $globalUser->id_global_user)
                                        ->where('users_vs_systems.system_id', SysConst::SYSTEM_CAP)
                                        ->value('users_vs_systems.user_system_id');

                    $user->user_system_id = $userCapId;
                    $resultCAP = self::updateCAPPassword($loginCAP->token_type, $loginCAP->access_token, $user);
                    if($resultCAP->status != 'success'){
                        $globalUser->id_user_system = null;
                        programmedTaskUtils::createTaskToUsersGlobal(SysConst::TASK_UPDATE_PASSWORD_CAP, $globalUser, SysConst::SYSTEM_GLOBAL_USERS);
                    }
                }
            } catch (\Throwable $th) {
                \Log::error($th);
                $globalUser->id_user_system = null;
                programmedTaskUtils::createTaskToUsersGlobal(SysConst::TASK_UPDATE_PASSWORD_CAP, $globalUser, SysConst::SYSTEM_GLOBAL_USERS);
            }
        }

        if($fromSystem != SysConst::SYSTEM_EVALUACIONDESEMPENO){
            try {
                $loginEval = self::loginToEval();
                if($loginEval->status == 'success'){
                    $userEvalId = globalUser::join('users_vs_systems', 'users_vs_systems.global_user_id', '=', 'global_users.id_global_user')
                                        ->where('users_vs_systems.global_user_id', $globalUser->id_global_user)
                                        ->where('users_vs_systems.system_id', SysConst::SYSTEM_EVALUACIONDESEMPENO)
                                        ->value('users_vs_systems.user_system_id');

                    $user->user_system_id = $userEvalId;
                    $resultEval = self::updateEvalPassword($loginEval->token_type, $loginEval->access_token, $user);
                    if($resultEval->status != 'success'){
                        $globalUser->id_user_system = null;
                        programmedTaskUtils::createTaskToUsersGlobal(SysConst::TASK_UPDATE_PASSWORD_EVAL, $globalUser, SysConst::SYSTEM_GLOBAL_USERS);
                    }
                }
            } catch (\Throwable $th) {
                \Log::error($th);
                $globalUser->id_user_system = null;
                programmedTaskUtils::createTaskToUsersGlobal(SysConst::TASK_UPDATE_PASSWORD_EVAL, $globalUser, SysConst::SYSTEM_GLOBAL_USERS);
            }
        }
    }

    public static function updatePGHPassword($userExt, $user_id){
        $user = User::find($user_id);
        $user->username = $userExt->username;
        $user->institutional_mail = $userExt->email;
        $user->password = $userExt->pass;
        $user->update();
    }

    public static function updateUnivPassword($token_type, $access_token, $user){
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

        $body = json_encode(['user' => $user]);

        $request = new \GuzzleHttp\Psr7\Request('POST', 'updatePass', $headers, $body);
        $response = $client->sendAsync($request)->wait();
        $jsonString = $response->getBody()->getContents();
        $data = json_decode($jsonString);

        return $data;
    }

    public static function updateCAPPassword($token_type, $access_token, $user){
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

        $body = json_encode(['user' => $user]);

        $request = new \GuzzleHttp\Psr7\Request('POST', 'updatePass', $headers, $body);
        $response = $client->sendAsync($request)->wait();
        $jsonString = $response->getBody()->getContents();
        $data = json_decode($jsonString);

        return $data;
    }

    public static function updateEvalPassword($token_type, $access_token, $user){
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

        $body = json_encode(['user' => $user]);

        $request = new \GuzzleHttp\Psr7\Request('POST', 'updatePass', $headers, $body);
        $response = $client->sendAsync($request)->wait();
        $jsonString = $response->getBody()->getContents();
        $data = json_decode($jsonString);

        return $data;
    }

    public static function syncExternalWithGlobalUsers(){
        $lUsersIds = User::pluck('id');

        $lGlobalUsersIds = globalUser::join('users_vs_systems', 'users_vs_systems.global_user_id', '=', 'global_users.id_global_user')
                                    ->whereIn('users_vs_systems.user_system_id', $lUsersIds)
                                    ->where('users_vs_systems.system_id', SysConst::SYSTEM_PGH)
                                    ->pluck('id_global_user');

        $lUsersUnivIds = globalUser::join('users_vs_systems', 'users_vs_systems.global_user_id', '=', 'global_users.id_global_user')
                                ->where('users_vs_systems.system_id', SysConst::SYSTEM_UNIVAETH)
                                ->whereIn('global_users.id_global_user', $lGlobalUsersIds)
                                ->select('users_vs_systems.user_system_id', 'users_vs_systems.global_user_id')
                                ->get();

        $lUsersCap = globalUser::join('users_vs_systems', 'users_vs_systems.global_user_id', '=', 'global_users.id_global_user')
                                ->where('users_vs_systems.system_id', SysConst::SYSTEM_CAP)
                                ->whereIn('global_users.id_global_user', $lGlobalUsersIds)
                                ->get();

        $lUsersEval = globalUser::join('users_vs_systems', 'users_vs_systems.global_user_id', '=', 'global_users.id_global_user')
                                ->where('users_vs_systems.system_id', SysConst::SYSTEM_EVALUACIONDESEMPENO)
                                ->whereIn('global_users.id_global_user', $lGlobalUsersIds)
                                ->get();

        try {
            //sync con univ
            $lGlobalUsersPghIds = globalUser::join('users_vs_systems', 'users_vs_systems.global_user_id', '=', 'global_users.id_global_user')
                                    ->whereIn('users_vs_systems.global_user_id', $lUsersUnivIds->pluck('global_user_id'))
                                    ->where('users_vs_systems.system_id', SysConst::SYSTEM_PGH)
                                    ->select('users_vs_systems.user_system_id', 'users_vs_systems.global_user_id')
                                    ->get();

            $lUsers = [];
            foreach($lUsersUnivIds as $user){
                $globalUser = globalUser::join('users_vs_systems', 'users_vs_systems.global_user_id', '=', 'global_users.id_global_user')
                                            ->where('users_vs_systems.global_user_id', $user->global_user_id)
                                            ->where('users_vs_systems.system_id', SysConst::SYSTEM_PGH)
                                            ->select('users_vs_systems.user_system_id', 'users_vs_systems.global_user_id')
                                            ->first();

                $userPgh = User::where('id', $globalUser->user_system_id)
                                ->select(
                                    'id',
                                    'username',
                                    'password',
                                    'institutional_mail',
                                    'first_name',
                                    'last_name',
                                    'full_name',
                                    'is_active',
                                    'is_delete'
                                    )
                                ->first();

                $userCap = $lUsersCap->where('global_user_id', $user->global_user_id)->first();
                $userEval = $lUsersEval->where('global_user_id', $user->global_user_id)->first();

                $userPgh->user_univ_id = $user->user_system_id;
                $userPgh->user_cap_id = !is_null($userCap) ? $userCap->user_system_id : null;
                $userPgh->user_eval_id = !is_null($userEval) ? $userEval->user_system_id : null;
                $userPgh->pass = $userPgh->password;
                $lUsers[] = $userPgh;
            }

            // Convierte el array en una cadena JSON
            $jsonDatos = json_encode($lUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            // Guarda la cadena JSON en un archivo
            \Storage::disk('local')->put('datosUNIV.json', $jsonDatos);            
        } catch (\Throwable $th) {
            \Log::error($th);
        }
    }
}
<?php namespace App\Utils;

use App\Constants\SysConst;
use App\Models\ProgrammedTasks\programmedTask;
use App\Models\GlobalUsers\globalUser;
use PhpParser\Node\Expr\Throw_;
use Exception;
use App\User;
class programmedTaskUtils {

    /**
     * Metodo para obtener la task si esta existe
     * @param mixed $task_type
     * @param mixed $cfg
     * @return mixed resultado de la query
     */
    public function getTaskExist($task_type, $cfg){
        $result = \DB::table('programmed_tasks')
                    ->where('task_type_id', $task_type)
                    ->where('cfg', $cfg)
                    ->where('status', SysConst::TASK_STATUS_PENDIENTE)
                    ->where('is_deleted', false)
                    ->first();

        return $result;
    }

    /**
     * Metodo para crear el cfg para la programmed task
     * el cfg es un json con la informacion necesaria para ejecutar la task
     * @param mixed $task_type
     * @param mixed $oUser el objeto oUser debe de contener el id_system_user que es el id de usuario del systema origen
     * @return mixed resultado de la creacion del cfg
     */
    public static function createCFGToGlobalUsers($task_type, $oUser, $fromSystem){
        $cfg = '';
        switch ($task_type) {
            case SysConst::TASK_INSERT_USERGLOBAL:
                $cfg = json_encode(array('global_user_id' => $oUser->id_global_user, 'user_system_id' => $oUser->id_user_system, 'fromSystem' => $fromSystem));
                break;
            case SysConst::TAKS_INSERT_PGH:
                $cfg = json_encode(array('global_user_id' => $oUser->id_global_user, 'user_system_id' => $oUser->id_user_system, 'fromSystem' => $fromSystem));
                break;
            case SysConst::TASK_INSERT_UNIV:
                $cfg = json_encode(array('global_user_id' => $oUser->id_global_user, 'user_system_id' => $oUser->id_user_system, 'fromSystem' => $fromSystem));
                break;
            case SysConst::TASK_INSERT_CAP:
                $cfg = json_encode(array('global_user_id' => $oUser->id_global_user, 'user_system_id' => $oUser->id_user_system, 'fromSystem' => $fromSystem));
                break;
            case SysConst::TASK_INSERT_EVAL:
                $cfg = json_encode(array('global_user_id' => $oUser->id_global_user, 'user_system_id' => $oUser->id_user_system, 'fromSystem' => $fromSystem));
                break;
            case SysConst::TASK_UPDATE_USERGLOBAL:
                $cfg = json_encode(array('global_user_id' => $oUser->id_global_user, 'user_system_id' => $oUser->id_user_system, 'fromSystem' => $fromSystem));
                break;
            case SysConst::TASK_UPDATE_PGH:
                $cfg = json_encode(array('global_user_id' => $oUser->id_global_user, 'user_system_id' => $oUser->id_user_system, 'fromSystem' => $fromSystem));
                break;
            case SysConst::TASK_UPDATE_UNIV:
                $cfg = json_encode(array('global_user_id' => $oUser->id_global_user, 'user_system_id' => $oUser->id_user_system, 'fromSystem' => $fromSystem));
                break;
            case SysConst::TASK_UPDATE_CAP:
                $cfg = json_encode(array('global_user_id' => $oUser->id_global_user, 'user_system_id' => $oUser->id_user_system, 'fromSystem' => $fromSystem));
                break;
            case SysConst::TASK_UPDATE_EVAL:
                $cfg = json_encode(array('global_user_id' => $oUser->id_global_user, 'user_system_id' => $oUser->id_user_system, 'fromSystem' => $fromSystem));
            default:
                # code...
                break;
        }

        return $cfg;
    }

    /**
     * Metodo para crear la task para lo que concierne a los usuarios globales
     * Insertar usuario global, actualizar usuario global, insertar universidad, actualizar universidad,
     * insertar cap, actualizar cap, insertar evaluacion, actualizar evaluacion
     * @param mixed $task_type el id del tipo de task
     * @param mixed $oUser el objeto oUser debe de contener el id_user_system que es el id de usuario del systema origen
     * @param mixed $fromSystem el id del sistema origen
     */
    public static function createTaskToUsersGlobal($task_type, $oUser, $fromSystem){
        $cfg = self::createCFGToGlobalUsers($task_type, $oUser, $fromSystem);

        $programedTask = new programmedTask();
        $programedTask->execute_on = null;
        $programedTask->donde_at = null;
        $programedTask->cfg = $cfg;
        $programedTask->task_type_id = $task_type;
        $programedTask->status = SysConst::TASK_STATUS_PENDIENTE;
        $programedTask->is_deleted = false;
        $programedTask->save();
    }

    public static function executeListTasks(){
        $lTask = programmedTask::where('status', SysConst::TASK_STATUS_PENDIENTE)
                    ->where('is_deleted', false)
                    ->get();

        foreach ($lTask as $task) {
            try {
                \DB::beginTransaction();
                \DB::connection('mysqlGlobalUsers')->beginTransaction();
                $task_type = $task->task_type_id;
                $cfg = json_decode($task->cfg);
                switch ($task_type) {
                    case SysConst::TASK_INSERT_USERGLOBAL:
                        $oUser = User::find($cfg->user_system_id);
                        $globalUser = GlobalUsersUtils::insertNewGlobalUser(SysConst::SYSTEM_PGH, $oUser->id, $oUser->username, $oUser->password, $oUser->email, $oUser->full_name, $oUser->external_id_n, $oUser->employee_num, $oUser->is_active, $oUser->is_delete);
                        
                        $loginUniv = GlobalUsersUtils::loginToUniv();
                        if($loginUniv->status == 'success'){
                            $result = GlobalUsersUtils::syncUserToUniv($loginUniv->token_type, $loginUniv->access_token, $oUser, SysConst::USERGLOBAL_INSERT);
                            if($result->status != 'success'){
                                try {
                                    \DB::beginTransaction();
                                    $oUser->id_user_system = $globalUser->id_global_user;
                                    $oUser->id_global_user = null;
                                    programmedTaskUtils::createTaskToUsersGlobal(SysConst::TASK_INSERT_UNIV, $oUser, null);
                                    \DB::commit();
                                } catch (\Throwable $th) {
                                    \Log::error($th);
                                    \DB::rollBack();
                                }
                            }
                        }else{
                            try {
                                \DB::beginTransaction();
                                $oUser->id_user_system = $globalUser->id_global_user;
                                $oUser->id_global_user = null;
                                programmedTaskUtils::createTaskToUsersGlobal(SysConst::TASK_INSERT_UNIV, $oUser, null);
                                \DB::commit();
                            } catch (\Throwable $th) {
                                \Log::error($th);
                                \DB::rollBack();
                            }
                        }
                        break;
                    case SysConst::TAKS_INSERT_PGH:
                        break;
                    case SysConst::TASK_INSERT_UNIV:
                        $oUser = globalUser::find($cfg->global_user_id);
                        $loginUniv = GlobalUsersUtils::loginToUniv();
                        if($loginUniv->status == 'success'){
                            $result = GlobalUsersUtils::syncUserToUniv($loginUniv->token_type, $loginUniv->access_token, $oUser, SysConst::USERGLOBAL_INSERT);
                            if(!$result->success){
                                throw new Exception($result->message);
                            }
                        }
                        break;
                    case SysConst::TASK_INSERT_CAP:
                        break;
                    case SysConst::TASK_INSERT_EVAL:
                        break;
                    case SysConst::TASK_UPDATE_USERGLOBAL:
                        $cfg = json_decode($task->cfg);
                        $result = GlobalUsersUtils::getUserFromSystem($cfg->user_system_id, $cfg->fromSystem);
                        if($result->status == 'success'){
                            $oUser = $result->data;
                            GlobalUsersUtils::globalUpdateFromSystem($oUser, $cfg->fromSystem);
                        }
                        break;
                    case SysConst::TASK_UPDATE_PGH:
                        $cfg = json_decode($task->cfg);
                        $userPGHId = GlobalUsersUtils::getSystemUserId($oUser->id_global_user, SysConst::SYSTEM_UNIVAETH);
                        if(!is_null($userPGHId)){
                            $oUser->id_user_system = $userPGHId;
                            GlobalUsersUtils::syncToPGH($oUser);
                        }
                        break;
                    case SysConst::TASK_UPDATE_UNIV:
                        $userGlobal = globalUser::where('id_global_user', $cfg->global_user_id)
                                                ->join('users_vs_systems', 'global_users.id_global_user', '=', 'global_user_id')
                                                ->where('system_id', SysConst::SYSTEM_PGH)
                                                ->first();
                        $oUser = User::find($userGlobal->user_system_id);
                        $userUnivId = GlobalUsersUtils::getSystemUserId($cfg->global_user_id, SysConst::SYSTEM_UNIVAETH);
                        if(!is_null($userUnivId)){
                            $oUser->id_user_system = $userUnivId;
                            $loginUniv = GlobalUsersUtils::loginToUniv();
                            if($loginUniv->status == 'success'){
                                $result = GlobalUsersUtils::syncUserToUniv($loginUniv->token_type, $loginUniv->access_token, $oUser, SysConst::USERGLOBAL_UPDATE);
                                if($result->status != 'success'){
                                    throw new Exception($result->message);
                                }
                            }
                        }
                        break;
                    case SysConst::TASK_UPDATE_CAP:
                        $userGlobal = globalUser::where('id_global_user', $cfg->global_user_id)
                                                ->join('users_vs_systems', 'global_users.id_global_user', '=', 'global_user_id')
                                                ->where('system_id', SysConst::SYSTEM_PGH)
                                                ->first();
                        $oUser = User::find($userGlobal->user_system_id);
                        $userCAPId = GlobalUsersUtils::getSystemUserId($oUser->id_global_user, SysConst::SYSTEM_CAP);
                        if(!is_null($userCAPId)){
                            $oUser->id_user_system = $userCAPId;
                            $loginCAP = GlobalUsersUtils::loginToCAP();
                            if($loginCAP->status == 'success'){
                                $result = GlobalUsersUtils::syncUserToCAP($loginCAP->token_type, $loginCAP->access_token, $oUser, SysConst::USERGLOBAL_UPDATE);
                                if(!$result->success){
                                    throw new Exception($result->message);
                                }
                            }
                        }
                        break;
                    case SysConst::TASK_UPDATE_EVAL:
                        $userGlobal = globalUser::where('id_global_user', $cfg->global_user_id)
                                                ->join('users_vs_systems', 'global_users.id_global_user', '=', 'global_user_id')
                                                ->where('system_id', SysConst::SYSTEM_PGH)
                                                ->first();
                        $oUser = User::find($userGlobal->user_system_id);
                        $userEvalId = GlobalUsersUtils::getSystemUserId($oUser->id_global_user, SysConst::SYSTEM_EVALUACIONDESEMPENO);
                        if(!is_null($userEvalId)){
                            $oUser->id_user_system = $userEvalId;
                            $loginEval = GlobalUsersUtils::loginToEval();
                            if($loginEval->status == 'success'){
                                $result = GlobalUsersUtils::syncUserToEval($loginEval->token_type, $loginEval->access_token, $oUser, SysConst::USERGLOBAL_UPDATE);
                                if(!$result->success){
                                    throw new Exception($result->message);
                                }
                            }
                        }
                        break;
                    case SysConst::TASK_INSERT_SYSTEM_VS_USER:
                        $oUser = globalUser::find($cfg->global_user_id);
                        $oUser->id_user_system = $cfg->id_user_system;
                        GlobalUsersUtils::insertSystemUser($oUser->id_global_user, $cfg->fromSystem, $oUser->id_user_system);
                        break;
                    default:
                        # code...
                        break;
                }
                $task->status = SysConst::TASK_STATUS_REALIZADO;
                $task->update();

                \DB::commit();
                \DB::connection('mysqlGlobalUsers')->commit();
            } catch (\Throwable $th) {
                \DB::rollback();
                \DB::connection('mysqlGlobalUsers')->rollback();
                \Log::error($th->getMessage());
                // $task->status = SysConst::TASK_STATUS_ERROR;
                // $task->update();    
            }
        }
    }
}
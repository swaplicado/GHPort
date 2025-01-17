<?php

namespace App\Http\Controllers\Api;

use App\Constants\SysConst;
use App\Http\Controllers\Controller;
use App\Utils\delegationUtils;
use Auth;
use Illuminate\Http\Request;
use App\Utils\ExportUtils;
use App\Http\Controllers\Pages\incidencesController;
use Log;
use App\Utils\EmployeeVacationUtils;

class AppPghController extends Controller
{
    /**
     * Index de eventos que regresa un json con el resultado de la 
     * consulta de la base de datos a los registros.
     * Puede filtrarse por rango de fechas y/o usuario(s).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/events",
     *     summary="Obtiene eventos de la base de datos",
     *     tags={"AppPgh"},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="user_ids",
     *         in="query",
     *         description="IDs de usuarios separados por coma",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Operación exitosa",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Event")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la solicitud"
     *     )
     * )
     */
    public function events(Request $request)
    {
        try {
            // Validaciones de los parámetros
            $validatedData = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date', // Validamos que end_date sea posterior o igual a start_date
                'id_user_boss' => 'nullable|integer',
                'last_sync_date'=> 'nullable|date'
            ]);

            // Recibe los posibles parámetros del request validados
            $startDate = $validatedData['start_date'] ?? null; // Ej: '2024-01-01'
            $endDate = $validatedData['end_date'] ?? null; // Ej: '2024-12-31'
            $last_sync_date = $validatedData['last_sync_date'] ?? null; // Ej: '2024-12-31'
            $id_user_boss = $validatedData['id_user_boss'] ?? null; // Array de IDs de usuarios
            $userIds = null;

            if ($id_user_boss) {
                $employees = collect(ExportUtils::getEmployees($id_user_boss, null));
                if ($employees) {
                    $userIds = $employees->pluck('id')->toArray();
                }
            }

            $config = \App\Utils\Configuration::getConfigurations();
            if ($config->appMobileWithMySelf) {
                $user_id = delegationUtils::getIdUser();
                if ($user_id) {
                    $userIds[] = $user_id;
                }
            }

            $events = ExportUtils::getEvents($startDate, $endDate, $userIds, $last_sync_date);

            foreach ($events as $key => $event) {
                $event->type_key = 'EVE';
                $event->type_class = 'EVENT';
                $event->requested_client = 1;
                $event->authorized_client = 1;
            }

            return response()->json([
                'status' => 'success',
                'data' => $events
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ],400);
        }
    }

    /**
     * Index de incidencias que regresa un json con el resultado de la
     * consulta de la base de datos a los registros.
     * Puede filtrarse por rango de fechas y/o usuario(s).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/incidents",
     *     summary="Obtiene incidencias de la base de datos",
     *     tags={"AppPgh"},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="user_ids",
     *         in="query",
     *         description="IDs de usuarios separados por coma",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Operación exitosa",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Incident")
     *         )
     *     ),
     */
    public function incidents(Request $request)
    {
        try {
            // Validaciones de los parámetros
            $validatedData = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date', // Validamos que end_date sea posterior o igual a start_date
                'id_user_boss' => 'nullable|integer',
                'last_sync_date'=> 'nullable|date',
                'status_creado' => 'nullable|boolean',
                'status_enviado' => 'nullable|boolean',
                'status_aprobado' => 'nullable|boolean',
                'status_rechazado' => 'nullable|boolean',
                'status_consumido' => 'nullable|boolean',
                'status_cancelado' => 'nullable|boolean',
            ]);

            // Recibe los posibles parámetros del request validados
            $startDate = $validatedData['start_date'] ?? null; // Ej: '2024-01-01'
            $endDate = $validatedData['end_date'] ?? null; // Ej: '2024-12-31'
            $last_sync_date = $validatedData['last_sync_date'] ?? null; // Ej: '2024-12-31'
            $id_user_boss = $validatedData['id_user_boss'] ?? null; // Array de IDs de usuarios
            $userIds = null;
            $status_creado = $validatedData['status_creado'] ?? null;
            $status_enviado = $validatedData['status_enviado'] ?? null;
            $status_aprobado = $validatedData['status_aprobado'] ?? null;
            $status_rechazado = $validatedData['status_rechazado'] ?? null;
            $status_consumido = $validatedData['status_consumido'] ?? null;
            $status_cancelado = $validatedData['status_cancelado'] ?? null;

            if ($id_user_boss) {
                $employees = collect(ExportUtils::getEmployees($id_user_boss, null));
                if ($employees) {
                    $userIds = $employees->pluck('id')->toArray();
                }
            }

            $config = \App\Utils\Configuration::getConfigurations();
            if ($config->appMobileWithMySelf) {
                $user_id = delegationUtils::getIdUser();
                if ($user_id) {
                    $userIds[] = $user_id;
                }
            }

            $lStatus = [];
            if ($status_creado !== null) {
                $lStatus[] = SysConst::APPLICATION_CREADO;
            }
            if ($status_enviado !== null) {
                $lStatus[] = SysConst::APPLICATION_ENVIADO;
            }
            if ($status_aprobado !== null) {
                $lStatus[] = SysConst::APPLICATION_APROBADO;
            }
            if ($status_rechazado !== null) {
                $lStatus[] = SysConst::APPLICATION_RECHAZADO;
            }
            if ($status_consumido !== null) {
                $lStatus[] = SysConst::APPLICATION_CONSUMIDO;
            }
            if ($status_cancelado !== null) {
                $lStatus[] = SysConst::APPLICATION_CANCELADO;
            }

            $incidents = ExportUtils::getIncidents($startDate, $endDate, $userIds, $last_sync_date, $lStatus);
            $lEventsType = collect(ExportUtils::getEventsType());

            foreach ($incidents as $incident) {
                $event = $lEventsType->firstWhere('id_incidence', $incident->id_incidence_tp);
                $incident->type_key = $event ? $event->type_key : null;
                $incident->type_class = $event ? $event->type_class : null;
            }

            return response()->json([
                'status' => 'success',
                'data' => $incidents
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Index de permisos de entrada o salida que regresa un json con el resultado
     * de la consulta a la base de datos a los registros.
     * Puede filtrarse por rango de fechas y/o usuario(s).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/entry-permissions",
     *     summary="Obtiene permisos de entrada o salida de la base de datos",
     *     tags={"AppPgh"},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="user_ids",
     *         in="query",
     *         description="IDs de usuarios separados por coma",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Operación exitosa",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/EntryPermission")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la solicitud"
     *     )
     * )
     */
    public function permissions(Request $request)
    {
        try {
            // Validaciones de los parámetros
            $validatedData = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date', // Validamos que end_date sea posterior o igual a start_date
                'id_user_boss' => 'nullable|integer',
                'last_sync_date'=> 'nullable|date',
                'status_creado' => 'nullable|boolean',
                'status_enviado' => 'nullable|boolean',
                'status_aprobado' => 'nullable|boolean',
                'status_rechazado' => 'nullable|boolean',
                'status_consumido' => 'nullable|boolean',
                'status_cancelado' => 'nullable|boolean',
            ]);

            // Recibe los posibles parámetros del request validados
            $startDate = $validatedData['start_date'] ?? null; // Ej: '2024-01-01'
            $endDate = $validatedData['end_date'] ?? null; // Ej: '2024-12-31'
            $last_sync_date = $validatedData['last_sync_date'] ?? null; // Ej: '2024-12-31'
            $id_user_boss = $validatedData['id_user_boss'] ?? null; // Array de IDs de usuarios
            $userIds = null;
            $status_creado = $validatedData['status_creado'] ?? null;
            $status_enviado = $validatedData['status_enviado'] ?? null;
            $status_aprobado = $validatedData['status_aprobado'] ?? null;
            $status_rechazado = $validatedData['status_rechazado'] ?? null;
            $status_consumido = $validatedData['status_consumido'] ?? null;
            $status_cancelado = $validatedData['status_cancelado'] ?? null;

            if ($id_user_boss) {
                $employees = collect(ExportUtils::getEmployees($id_user_boss, null));
                if ($employees) {
                    $userIds = $employees->pluck('id')->toArray();
                }
            }

            $config = \App\Utils\Configuration::getConfigurations();
            if ($config->appMobileWithMySelf) {
                $user_id = delegationUtils::getIdUser();
                if ($user_id) {
                    $userIds[] = $user_id;
                }
            }

            $lStatus = [];
            if ($status_creado !== null) {
                $lStatus[] = SysConst::APPLICATION_CREADO;
            }
            if ($status_enviado !== null) {
                $lStatus[] = SysConst::APPLICATION_ENVIADO;
            }
            if ($status_aprobado !== null) {
                $lStatus[] = SysConst::APPLICATION_APROBADO;
            }
            if ($status_rechazado !== null) {
                $lStatus[] = SysConst::APPLICATION_RECHAZADO;
            }
            if ($status_consumido !== null) {
                $lStatus[] = SysConst::APPLICATION_CONSUMIDO;
            }
            if ($status_cancelado !== null) {
                $lStatus[] = SysConst::APPLICATION_CANCELADO;
            }

            $entryPermissions = ExportUtils::getPermissions($startDate, $endDate, $userIds, $last_sync_date, $lStatus);
            $lEventsType = collect(ExportUtils::getEventsType());

            foreach ($entryPermissions as $permission) {
                $event = $lEventsType->where('id_permission_cl', $permission->cl_permission_id)
                                    ->where('id_permission_tp', $permission->type_permission_id)
                                    ->first();
                $permission->type_key = $event ? $event->type_key : null;
                $permission->type_class = $event ? $event->type_class : null;
            }

            return response()->json([
                'status' => 'success',
                'data' => $entryPermissions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Regresa un json con el status de la incidencia adosado al objeto recibido.
     * Recibe un array con las incidencias:
     * 
     *  "incidents": [
     *       {
     *           "full_name": "nombre de colaborador",
     *           "employee_num": numero de empleado
     *           "incidence_tp_name": "tipo de incidencia",
     *           "id_application": id de la incidencia,
     *           "event_type": "clase de incidencia (VACACIONES, INASISTENCIA, permission)",
     *           ...
     *       },
     *       ...
     *   ]
     * 
     * y regresa:
     * 
     *  "incidents": [
     *       {
     *           "full_name": "nombre de colaborador",
     *           "employee_num": numero de empleado
     *           "incidence_tp_name": "tipo de incidencia",
     *           "id_application": id de la incidencia,
     *           "event_type": "clase de incidencia (VACACIONES, INASISTENCIA, permission)",
     *           ...
     *           "system_result": {
     *               "id_applications_st": id del status,
     *               "applications_st_code": "Codigo del status",
     *               "applications_st_name": "nombre del status"
     *           }
     *       },
     *       ...
     *    ]
     *  
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function checkIncidentsStatus(Request $request) {
        try {
            $lIncidents = json_decode($request->getContent())->incidents;
            $lEventsType = collect(ExportUtils::getEventsType());
            foreach ($lIncidents as $key => $incident) {
                $event = $lEventsType->firstWhere('type_key', $incident->type_key);
                $oStatus = ExportUtils::getApplicationStatus($incident, $event->type_class);
                $incident->system_result = $oStatus;
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'data' => $lIncidents,
        ], 200);
    }

    /**
     * Autoriza vacaciones, incidencias y permisos, recibe un array con las incidencias:
     * "incidents": [
     *   {
     *     "id_application": id de la incidencias,
     *     "event_type": "Clase de incidencia"
     *     ...
     *   },
     *   ...
     * ]
     * y regresa las incidencias aprobadas:
     * "incidents": [
     *    {
     *       "id_application": id de la incidencias,
     *       "event_type": "Clase de incidencia"
     *       ...
     *       "system_result":"{\"success\":true,\"message\":\"Solicitud aprobada con \éxito\"}"
     *    },
     *    ...
     *  ]
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function authorizeIncidents(Request $request) {
        try {
            $lIncidents = json_decode($request->getContent())->incidents;
            $lEventsType = collect(ExportUtils::getEventsType());

            foreach ($lIncidents as $key => $incident) {
                $event = $lEventsType->firstWhere('type_key', $incident->type_key);

                switch ($event->type_class) {
                    case 'VACATION':
                        $incident->id_application = $incident->id;
                        $incident->system_result = ExportUtils::authorizeVacations($incident);
                        break;
                    case 'INCIDENT':
                        $incident->application_id = $incident->id;
                        $incident->system_result = ExportUtils::authorizeIncidence($incident);
                        break;
                    case 'PERMISSION':
                        $incident->permission_id = $incident->id;
                        $incident->system_result = ExportUtils::authorizePermission($incident);
                        break;
                    
                    default:
                        break;
                }
            }

        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'data' => $lIncidents
        ], 200);
    }

    /**
     * Rechaza vacaciones, incidencias y permisos, recibe un array con las incidencias:
     * "incidents": [
     *   {
     *     "id_application": id de la incidencias,
     *     "event_type": "Clase de incidencia"
     *     ...
     *   },
     *   ...
     * ]
     * y regresa las incidencias aprobadas:
     * "incidents": [
     *    {
     *       "id_application": id de la incidencias,
     *       "event_type": "Clase de incidencia"
     *       ...
     *       "system_result":"{\"success\":true,\"message\":\"Solicitud aprobada con \éxito\"}"
     *    },
     *    ...
     *  ]
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function rejectIncidents(Request $request) {
        try {
            $lIncidents = json_decode($request->getContent())->incidents;
            $lEventsType = collect(ExportUtils::getEventsType());
            foreach ($lIncidents as $key => $incident) {
                $event = $lEventsType->firstWhere('type_key', $incident->type_key);
                switch ($event->type_class) {
                    case 'VACATION':
                        $incident->id_application = $incident->id;
                        $incident->system_result = ExportUtils::rejectVacations($incident);
                        break;
                    case 'INCIDENT':
                        $incident->application_id = $incident->id;
                        $incident->system_result = ExportUtils::rejectIncidence($incident);
                        break;
                    case 'PERMISSION':
                        $incident->permission_id = $incident->id;
                        $incident->system_result = ExportUtils::rejectPermission($incident);
                        break;
                    
                    default:
                        break;
                }
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'data' => $lIncidents
        ], 200);
    }

    /**
     * Revisa si las incidencias recibidas estan autorizadas o no
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function incidentIsAuthorized(Request $request) {
        try {
            $lIncidents = json_decode($request->getContent())->incidents;
            $lEventsType = collect(ExportUtils::getEventsType());
            foreach ($lIncidents as $key => $incident) {
                $event = $lEventsType->firstWhere('type_key', $incident->type_key);
                $incident->system_result = ExportUtils::isAuthorized($incident, $event->type_class);
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'data' => $lIncidents
        ], 200);
    }

    /**
     * Revisa si las incidencias recibidas estan rechazadas o no
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function incidentIsRejected(Request $request) {
        try {
            $lIncidents = json_decode($request->getContent())->incidents;
            $lEventsType = collect(ExportUtils::getEventsType());
            foreach ($lIncidents as $key => $incident) {
                $event = $lEventsType->firstWhere('type_key', $incident->type_key);
                $incident->system_result = ExportUtils::isRejected($incident, $event->type_class);
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'data' => $lIncidents
        ], 200);
    }

    public function employees(Request $request) {
        try {
            $config = \App\Utils\Configuration::getConfigurations();
            $id_user_boss = $request->id_user_boss;
            $last_sync_date = $request->last_sync_date;
            $employees = ExportUtils::getEmployees($id_user_boss, $last_sync_date);

            foreach ($employees as $key => $employee) {
                $oUser = EmployeeVacationUtils::getEmployeeDataForMyVacation($employee->id);
                $employee->tot_vacation_remaining = $oUser->tot_vacation_remaining;
                $employee->mySelf = 0;
                $employee->rol_id = 0;
            }

            if ($config->appMobileWithMySelf) {
                $oUser = delegationUtils::getUser();
                $vacation_data = EmployeeVacationUtils::getEmployeeDataForMyVacation($oUser->id);
                $oUser->tot_vacation_remaining = $vacation_data->tot_vacation_remaining;
                array_push($employees, [ 
                        "id" => $oUser->id,
                        "first_name" => $oUser->first_name,
                        "last_name" => $oUser->last_name,
                        "full_name" => $oUser->full_name,
                        "org_chart_job_name" => $oUser->org_chart_job_name,
                        "job_name" => $oUser->job_name,
                        "department_name" => $oUser->department_name,
                        "updated_at" => $oUser->updated_at,
                        "created_at" => $oUser->created_at,
                        "tot_vacation_remaining" => $oUser->tot_vacation_remaining,
                        "mySelf" => 1,
                        "rol_id" => $oUser->rol_id
                    ]
                );
            }

            return response()->json([
                'status' => 'success',
                'data' => $employees
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function eventsType() {
        try {
            $eventsType = ExportUtils::getEventsType();

            return response()->json([
                'status' => 'success',
                'data' => $eventsType
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function holidays(Request $request) {
        try {
            $start_date = $request->start_date;
            $last_sync_date = $request->last_sync_date;
            $holidays = ExportUtils::getHolidays($start_date, $last_sync_date);

            return response()->json([
                'status' => 'success',
                'data' => $holidays
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Trabajo en proceso
     */
    public function createAndSendIncident(Request $request) {
        try {
            $oIncident = (object)$request->incident;
            $lEventsType = collect(ExportUtils::getEventsType());
            $event = $lEventsType->firstWhere('app_id', $oIncident->incidentType);
            
            switch ($event->type_class) {
                case 'VACATION':
                    $oIncident->incidentType = $event->id_incidence;
                    $result = json_decode(ExportUtils::createAndSendVacation($oIncident));
                    break;
                case 'INCIDENT':
                    $oIncident->incidentType = $event->id_incidence;
                    $result = json_decode(ExportUtils::createAndSendIncidence($oIncident));
                    break;
                case 'PERMISSION':
                    $oIncident->id_permission_cl = $event->id_permission_cl;
                    $oIncident->id_permission_tp = $event->id_permission_tp;
                    $result = json_decode(ExportUtils::createAndSendPermission($oIncident));
                    break;
                default:
                    throw new \Exception("Tipo de evento no soportado.");
            }
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }

        // Manejo de la respuesta según el valor de `$result->success`
        if ($result->success) {
            return response()->json([
                'status' => 'success',
                'message' => $result->message
            ], 200);
        } else {
            // Devuelve un código HTTP 500 si `$result->success` es false
            return response()->json([
                'status' => 'error',
                'message' => $result->message
            ], 500); // Aquí cambias el código HTTP
        }
    }
}
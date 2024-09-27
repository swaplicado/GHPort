<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\ExportUtils;
use App\Http\Controllers\Pages\incidencesController;
use Log;

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
                'user_ids' => 'nullable|array', // Validamos que sea un array
                'user_ids.*' => 'integer', // Cada elemento del array debe ser un entero
            ]);

            // Recibe los posibles parámetros del request validados
            $startDate = $validatedData['start_date'] ?? null; // Ej: '2024-01-01'
            $endDate = $validatedData['end_date'] ?? null; // Ej: '2024-12-31'
            $userIds = $validatedData['user_ids'] ?? null; // Array de IDs de usuarios

            $events = ExportUtils::getEvents($startDate, $endDate, $userIds);

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
                'user_ids' => 'nullable|array', // Validamos que sea un array
                'user_ids.*' => 'integer', // Cada elemento del array debe ser un entero
            ]);

            // Recibe los posibles parámetros del request validados
            $startDate = $validatedData['start_date'] ?? null; // Ej: '2024-01-01'
            $endDate = $validatedData['end_date'] ?? null; // Ej: '2024-12-31'
            $userIds = $validatedData['user_ids'] ?? null; // Array de IDs de usuarios

            $incidents = ExportUtils::getIncidents($startDate, $endDate, $userIds);

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
                'user_ids' => 'nullable|array', // Validamos que sea un array
                'user_ids.*' => 'integer', // Cada elemento del array debe ser un entero
            ]);

            // Recibe los posibles parámetros del request validados
            $startDate = $validatedData['start_date'] ?? null; // Ej: '2024-01-01'
            $endDate = $validatedData['end_date'] ?? null; // Ej: '2024-12-31'
            $userIds = $validatedData['user_ids'] ?? null; // Array de IDs de usuarios

            $entryPermissions = ExportUtils::getPermissions($startDate, $endDate, $userIds);

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
            foreach ($lIncidents as $key => $incident) {
                $type = $incident->event_type;
                $oStatus = ExportUtils::getApplicationStatus($incident, $type);
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
            
            foreach ($lIncidents as $key => $incident) {
                $type = $incident->event_type;
                switch ($type) {
                    case 'VACACIONES':
                        $incident->system_result = ExportUtils::authorizeVacations($incident);
                        break;
                    case 'INASISTENCIA':
                        $incident->system_result = ExportUtils::authorizeIncidence($incident);
                        break;
                    case 'permission':
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
            
            foreach ($lIncidents as $key => $incident) {
                $type = $incident->event_type;
                switch ($type) {
                    case 'VACACIONES':
                        $incident->system_result = ExportUtils::rejectVacations($incident);
                        break;
                    case 'INASISTENCIA':
                        $incident->system_result = ExportUtils::rejectIncidence($incident);
                        break;
                    case 'permission':
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
            
            foreach ($lIncidents as $key => $incident) {
                $type = $incident->event_type;
                $incident->system_result = ExportUtils::isAuthorized($incident, $type);
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
            
            foreach ($lIncidents as $key => $incident) {
                $type = $incident->event_type;
                $incident->system_result = ExportUtils::isRejected($incident, $type);
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
}
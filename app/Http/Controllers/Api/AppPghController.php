<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\ExportUtils;
use App\Http\Controllers\Pages\incidencesController;

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

            return response()->json($events);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
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

            return response()->json($incidents);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
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

            return response()->json($entryPermissions);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Index de autorizaciones que regresa un json con el resultado de la
     * consulta a la base de datos a los registros.
     * Puede filtrarse por rango de fechas y/o usuario(s).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Put(
     *     path="/api/authorize",
     *     summary="Obtiene autorizaciones de la base de datos",
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
     *             @OA\Items(ref="#/components/schemas/Authorization")
     *         )
     * */
    public function authorization(Request $request)
    {
        try {
            // Extraer data del request:
            $idUser = $request->id_user;
            $l_rows = $request->rows;
            $oIncidentsController = new incidencesController();
            foreach ($l_rows as $key => $row) {
                switch ($row->event_type) {
                    case 'incident':
                        // Lógica para autorizar application
                        
                        $oIncidentsController->sendAndAuthorizeById($row->row_id);
                        break;

                    case 'permission':
                        // Lógica para autorizar permisos
                        break;

                    default:
                        // Acción no reconocida
                        break;
                }
            }

            return response()->json("Hola");

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}

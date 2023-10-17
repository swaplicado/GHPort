<?php

namespace App\Http\Controllers\Pages;


use App\Constants\SysConst;
use App\Http\Controllers\Controller;
use App\Models\Adm\EventAssign;
use App\Models\Adm\Group;
use App\Models\Adm\GroupAssign;
use App\User;
use App\Utils\EmployeeVacationUtils;
use Carbon\Carbon;
use App\Models\Adm\Event;
use Illuminate\Http\Request;

class EventsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $lEvents = \DB::table('events')
                        ->where('is_deleted',0)
                        ->orderBy('start_date')
                        ->orderBy('priority')
                        ->select('id_event AS idEvent', 'name AS nameEvent', 'start_date AS sDate', 'end_date AS eDate', 'priority AS priority')
                        ->get();
        
        $now = Carbon::now();
        $initialCalendarDate = $now->subMonths(1)->toDateString();

        $lHolidays = \DB::table('holidays')
                        ->where('fecha', '>', Carbon::now()->subDays(30)->toDateString())
                        ->where('is_deleted', 0)
                        ->pluck('fecha');
        
        $lEvents = \DB::table('events_assigns')
                        ->join('events', 'events.id_event','=', 'events_assigns.id_event_assign')
                        ->groupBy('event_id')
                        ->
                        ->get();
        
        $constants = [
            'SEMANA' => SysConst::SEMANA,
            'QUINCENA' => SysConst::QUINCENA,
            'APPLICATION_CREADO' => SysConst::APPLICATION_CREADO,
            'APPLICATION_ENVIADO' => SysConst::APPLICATION_ENVIADO,
            'APPLICATION_RECHAZADO' => SysConst::APPLICATION_RECHAZADO,
            'APPLICATION_APROBADO' => SysConst::APPLICATION_APROBADO,
            'TYPE_VACACIONES' => SysConst::TYPE_VACACIONES,
            'TYPE_INASISTENCIA' => SysConst::TYPE_INASISTENCIA,
            'TYPE_INASISTENCIA_ADMINISTRATIVA' => SysConst::TYPE_INASISTENCIA_ADMINISTRATIVA,
            'TYPE_PERMISO_SIN_GOCE' => SysConst::TYPE_PERMISO_SIN_GOCE,
            'TYPE_PERMISO_CON_GOCE' => SysConst::TYPE_PERMISO_CON_GOCE,
            'TYPE_PERMISO_PATERNIDAD' => SysConst::TYPE_PERMISO_PATERNIDAD,
            'TYPE_PRESCRIPCIÓN_MEDICA' => SysConst::TYPE_PRESCRIPCIÓN_MEDICA,
            'TYPE_TEMA_LABORAL' => SysConst::TYPE_TEMA_LABORAL,
            'TYPE_CUMPLEAÑOS' => SysConst::TYPE_CUMPLEAÑOS,
            'TYPE_HOMEOFFICE' => SysConst::TYPE_HOMEOFFICE,
        ];

        $lTemp_special = [];

        return view('events.index')->with('lEvents',$lEvents)
                                   ->with('constants', $constants)
                                   ->with('lTemp', $lTemp_special)
                                   ->with('initialCalendarDate', $initialCalendarDate)
                                   ->with('lHolidays', $lHolidays);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $name = $request->name_event;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $priority = $request->priority;
        $lDays = $request->lDays;
        $takedDays = $request->takedDays;
        $returnDate = $request->returnDate;
        $tot_calendar_days = $request->tot_calendar_days;
   
        try {

            \DB::beginTransaction();

            $event = new Event();
            $event->name = $name;
            $event->start_date = $start_date;
            $event->end_date = $end_date;
            $event->ldays = json_encode($lDays);
            $event->total_days = $takedDays;
            $event->return_date = $returnDate;
            $event->tot_calendar_days = $tot_calendar_days;
            $event->priority = $priority;
            $event->is_deleted = false;
            $event->created_by = \Auth::user()->id;
            $event->updated_by = \Auth::user()->id;
            $event->save();

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al crear el evento', 'icon' => 'error']);
        }

        $lEvents = \DB::table('events')
                        ->where('is_deleted',0)
                        ->orderBy('start_date')
                        ->orderBy('priority')
                        ->select('id_event AS idEvent', 'name AS nameEvent', 'start_date AS sDate', 'end_date AS eDate', 'priority AS priority')
                        ->get();

        return json_encode(['success' => true, 'lEvents' => $lEvents]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function getUsersAssigned(Request $request){
        try {
            $lUsersAssigned = \DB::table('events_assigns')
                                ->join('users', 'users.id', '=', 'events_assigns.user_id_n')
                                ->where( 'event_id', $request->event_id)
                                ->where('users.id', '!=', 1)
                                ->select('users.id','event_id', 'full_name', 'full_name_ui')
                                ->orderBy('full_name_ui')
                                ->get();
            
            $Ausers = [];

            foreach($lUsersAssigned as $users){
                array_push($Ausers, $users->id);
            }

            $lUsers = \DB::table('users')
                        ->where('is_delete', 0)
                        ->where('is_active', 1)
                        ->where('id', '!=', 1)
                        ->whereNotIn( 'id', $Ausers)
                        ->select('users.id', 'full_name', 'full_name_ui')
                        ->orderBy('full_name_ui')
                        ->get();
    
                             
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al obtener el registro', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lUsers' => $lUsers, 'lUsersAssigned' => $lUsersAssigned]);
    }

    public function getGroupAssigned(Request $request){
        try {
            $lGroupsAssigned = \DB::table('events_assigns')
                ->join('groups', 'groups.id_group', '=', 'events_assigns.group_id_n')
                ->where( 'event_id', $request->event_id)
                ->select('group_id_n AS id_group', 'name')
                ->orderBy('name')
                ->get();
            
            $Agroups = [];

            foreach ($lGroupsAssigned as $groups){
                array_push($Agroups, $groups->id_group);
            }

            $lGroups = \DB::table('groups')
                        ->where('is_deleted', 0)
                        ->whereNotIn( 'id_group', $Agroups)
                        ->select('groups.id_group', 'name')
                        ->orderBy('name')
                        ->get();
                     
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al obtener el registro', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lGroups' => $lGroups, 'lGroupsAssigned' => $lGroupsAssigned]);
    }

    public function saveAssignGroup(Request $request){
        try {
            $lGroupsAssigned_id = collect($request->lGroupsAssigned)->pluck('id_group');
    
            \DB::table('events_assigns')
                                ->where('event_id',$request->event_id)
                                ->where('user_id_n',null)
                                ->delete();

            $lGroup = Group::whereIn('id_group', $lGroupsAssigned_id)->get();
    
            foreach($lGroup as $groups){
                try {
                    \DB::beginTransaction();
                    $eventAssign = new EventAssign();
                    $eventAssign->event_id = $request->event_id;
                    $eventAssign->group_id_n = $groups->id_group;
                    $eventAssign->created_by = \Auth::user()->id;
                    $eventAssign->updated_by = \Auth::user()->id;
                    $eventAssign->save();
                    \DB::commit();
                } catch (\Throwable $th) {
                    \DB::rollback();
                }
            }

        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al guardar los registros', 'icon' => 'error']);
        }

        return json_encode(['success' => true]);
    }

    public function saveAssignUser(Request $request){
        try {
            $lUserAssigned_id = collect($request->lUsersAssigned)->pluck('id');
    
            \DB::table('events_assigns')
                                ->where('event_id',$request->event_id)
                                ->where('group_id_n',null)
                                ->delete();

            $lUsers = User::whereIn('id', $lUserAssigned_id)->get();
    
            foreach($lUsers as $users){
                try {
                    \DB::beginTransaction();
                    $eventAssign = new EventAssign();
                    $eventAssign->event_id = $request->event_id;
                    $eventAssign->user_id_n = $users->id;
                    $eventAssign->created_by = \Auth::user()->id;
                    $eventAssign->updated_by = \Auth::user()->id;
                    $eventAssign->save();
                    \DB::commit();
                } catch (\Throwable $th) {
                    \DB::rollback();
                }
            }

        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al guardar los registros', 'icon' => 'error']);
        }

        return json_encode(['success' => true]);
    }
}

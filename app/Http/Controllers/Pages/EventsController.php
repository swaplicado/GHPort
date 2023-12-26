<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Constants\SysConst;
use App\Models\Adm\EventAssign;
use App\Models\Adm\Group;
use App\Models\Adm\GroupAssign;
use App\User;
use App\Utils\EmployeeVacationUtils;
use Carbon\Carbon;
use App\Models\Adm\Event;

class EventsController extends Controller
{
    public function index()
    {
        $lEvents = \DB::table('cat_events')
                        ->where('is_deleted',0)
                        ->orderBy('start_date')
                        ->orderBy('priority')
                        ->select(
                            'id_event AS idEvent',
                            'name AS name',
                            'start_date AS sDate',
                            'end_date AS eDate',
                            'priority AS priority',
                            'ldays',
                        )
                        ->get();

        foreach ($lEvents as $event) {
            $lDays = json_decode($event->ldays);
            foreach ($lDays as $day) {
                if($day->taked){
                    $event->lDates[] = $day->date;
                }
            }
        }
        
        $now = Carbon::now();
        $initialCalendarDate = $now->subMonths(1)->toDateString();

        $lHolidays = \DB::table('holidays')
                        ->where('fecha', '>', Carbon::now()->subDays(30)->toDateString())
                        ->where('is_deleted', 0)
                        ->pluck('fecha');
        
        $lEventsAssigns = \DB::table('events_assigns as ea')
                        ->join('cat_events as e', 'e.id_event','=', 'ea.id_event_assign')
                        ->leftJoin('users as u', 'u.id', '=', 'ea.user_id_n')
                        ->leftJoin('groups as g', 'g.id_group', '=', 'ea.user_id_n')
                        ->select(
                            'ea.id_event_assign',
                            'e.name as event',
                            'u.full_name as user',
                            'g.name as group'
                        )
                        ->get();

        $constants = [
            'SEMANA' => SysConst::SEMANA,
            'QUINCENA' => SysConst::QUINCENA,
        ];
        
        return view('events.events')->with('lEvents',$lEvents)
                                    ->with('lEventsAssigns', $lEventsAssigns)
                                    ->with('initialCalendarDate', $initialCalendarDate)
                                    ->with('lHolidays', $lHolidays)
                                    ->with('constants', $constants);
    }

    public function store(Request $request)
    {
        $name = $request->name;
        $start_date = $request->startDate;
        $end_date = $request->endDate;
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

        $lEvents = \DB::table('cat_events')
                        ->where('is_deleted',0)
                        ->orderBy('start_date')
                        ->orderBy('priority')
                        ->select(
                            'id_event AS idEvent',
                            'name AS name',
                            'start_date AS sDate',
                            'end_date AS eDate',
                            'priority AS priority',
                            'ldays',
                        )
                        ->get();

        foreach ($lEvents as $event) {
            $lDays = json_decode($event->ldays);
            foreach ($lDays as $day) {
                if($day->taked){
                    $event->lDates[] = $day->date;
                }
            }
        }

        return json_encode(['success' => true, 'lEvents' => $lEvents]);
    }

    public function updateEvent(Request $request){
        $idEvent = $request->idEvent;
        $name = $request->name;
        $start_date = $request->startDate;
        $end_date = $request->endDate;
        $priority = $request->priority;
        $lDays = $request->lDays;
        $takedDays = $request->takedDays;
        $returnDate = $request->returnDate;
        $tot_calendar_days = $request->tot_calendar_days;

        try {
            \DB::beginTransaction();
            
            $event = Event::findOrFail($idEvent);
            $event->name = $name;
            $event->start_date = $start_date;
            $event->end_date = $end_date;
            $event->ldays = json_encode($lDays);
            $event->total_days = $takedDays;
            $event->return_date = $returnDate;
            $event->tot_calendar_days = $tot_calendar_days;
            $event->priority = $priority;
            $event->is_deleted = false;
            $event->updated_by = \Auth::user()->id;
            $event->update();

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al crear el evento', 'icon' => 'error']);
        }

        $lEvents = \DB::table('cat_events')
                        ->where('is_deleted',0)
                        ->orderBy('start_date')
                        ->orderBy('priority')
                        ->select(
                            'id_event AS idEvent',
                            'name AS name',
                            'start_date AS sDate',
                            'end_date AS eDate',
                            'priority AS priority',
                            'ldays',
                        )
                        ->get();

        foreach ($lEvents as $event) {
            $lDays = json_decode($event->ldays);
            foreach ($lDays as $day) {
                if($day->taked){
                    $event->lDates[] = $day->date;
                }
            }
        }

        return json_encode(['success' => true, 'lEvents' => $lEvents]);
    }

    public function deleteEvent(Request $request){
        $idEvent = $request->idEvent;
        try {
            \DB::beginTransaction();
            $event = Event::findOrFail($idEvent);
            $event->is_deleted = true;
            $event->update();
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'error']);
        }

        $lEvents = \DB::table('cat_events')
                        ->where('is_deleted',0)
                        ->orderBy('start_date')
                        ->orderBy('priority')
                        ->select(
                            'id_event AS idEvent',
                            'name AS name',
                            'start_date AS sDate',
                            'end_date AS eDate',
                            'priority AS priority',
                            'ldays',
                        )
                        ->get();

        foreach ($lEvents as $event) {
            $lDays = json_decode($event->ldays);
            foreach ($lDays as $day) {
                if($day->taked){
                    $event->lDates[] = $day->date;
                }
            }
        }
                        
        return json_encode(['success' => true, 'lEvents' => $lEvents]);
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
        $lGroupsAssigned_id = collect($request->lGroupsAssigned)->pluck('id_group')->toArray();
        $lGroupsNoAssigned_id = collect($request->lGroupsNoAssigned)->pluck('id_group')->toArray();
        try {
            \DB::beginTransaction();
            $lNoAssigned = EventAssign::where('event_id',$request->idEvent)
                                ->where('user_id_n',null)
                                ->whereIn('group_id_n', $lGroupsNoAssigned_id)
                                ->get();

            foreach($lNoAssigned as $noAssigned){
                $noAssigned->delete();
            }

            foreach($lGroupsAssigned_id as $groupAssigned){ 
                $eventAssign = EventAssign::where('event_id',$request->idEvent)
                                            ->where('group_id_n',$groupAssigned)
                                            ->where('user_id_n',null)
                                            ->first();

                if(!$eventAssign){
                    $eventAssign = new EventAssign();
                    $eventAssign->event_id = $request->idEvent;
                    $eventAssign->group_id_n = $groupAssigned;
                    $eventAssign->save();
                }
            }
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'error']);
        }

        return json_encode(['success' => true]);
    }

    public function saveAssignUser(Request $request){
        $lEmployeesAssigned_id = collect($request->lEmployeesAssigned)->pluck('id_employee')->toArray();
        $lEmployeesNoAssigned_id = collect($request->lEmployeesNoAssigned)->pluck('id_employee')->toArray();
        try {
            \DB::beginTransaction();
            $lNoAssigned = EventAssign::where('event_id',$request->idEvent)
                                ->where('group_id_n',null)
                                ->whereIn('user_id_n', $lEmployeesNoAssigned_id)
                                ->get();

            foreach($lNoAssigned as $noAssigned){
                $noAssigned->delete();
            }

            foreach($lEmployeesAssigned_id as $employeeAssigned){
                $eventAssign = EventAssign::where('event_id',$request->idEvent)
                                            ->where('user_id_n',$employeeAssigned)
                                            ->where('group_id_n',null)
                                            ->first();

                if(!$eventAssign){
                    $eventAssign = new EventAssign();
                    $eventAssign->event_id = $request->idEvent;
                    $eventAssign->user_id_n = $employeeAssigned;
                    $eventAssign->save();
                }
            }

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'error']);
        }

        return json_encode(['success' => true]);
    }

    public function getEventAssigned(Request $request){
        try {
            $idEvent = $request->idEvent;
    
            $lUserAssigned = \DB::table('events_assigns as ea')
                                ->join('users as u', 'u.id', '=', 'ea.user_id_n')
                                ->where('ea.event_id', $idEvent)
                                ->where('ea.is_deleted', 0)
                                ->where('ea.is_closed', 0)
                                ->select(
                                    'u.id as id_employee',
                                    'u.full_name as employee'
                                )
                                ->orderBy('employee')
                                ->get();
    
            $lGroupsAssigned = \DB::table('events_assigns as ea')
                                ->join('groups as g', 'g.id_group', '=', 'ea.group_id_n')
                                ->where('ea.event_id', $idEvent)
                                ->where('ea.is_deleted', 0)
                                ->where('ea.is_closed', 0)
                                ->select(
                                    'id_group',
                                    'name as group'
                                )
                                ->orderBy('group')
                                ->get();
    
            $lUsersNoAssigned = \DB::table('users')
                        ->where('is_delete', 0)
                        ->where('is_active', 1)
                        ->whereNotIn('id', $lUserAssigned->pluck('id_employee')->toArray())
                        ->where('id', '!=', 1)
                        ->select(
                            'id as id_employee',
                            'full_name as employee'
                        )
                        ->orderBy('employee')
                        ->get();
    
            $lGroupsNoAssigned = \DB::table('groups')
                            ->where('is_deleted', 0)
                            ->whereNotIn('id_group', $lGroupsAssigned->pluck('id_group')->toArray())
                            ->select(
                                'id_group',
                                'name as group'
                            )
                            ->orderBy('group')
                            ->get();
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'error']);
        }

        return json_encode([
            'success' => true,
            'lEmployeesAssigned' => $lUserAssigned,
            'lEmployeesNoAssigned' => $lUsersNoAssigned,
            'lGroupsAssigned' => $lGroupsAssigned,
            'lGroupsNoAssigned' => $lGroupsNoAssigned
        ]);
    }
}
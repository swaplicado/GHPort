@extends('layouts.principal')

@section('headStyles')
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        function GlobalData(){
            this.lEvents = <?php echo json_encode($lEvents) ?>;

            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "" ); ?>;

            this.indexesEventsTable = {
                'event_id': 0,
                'user_id': 1,
                'event': 2,
                'colaborador': 3,
                'area': 4,
            };
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content')
<div id="usersInEventsApp">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h3>
                <b>Eventos de mis colaboradores</b>
                @include('layouts.manual_button')
            </h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered" id="eventsTable" style="width: 100%">
                <thead class="thead-light">
                    <th>event_id</th>
                    <th>user_id</th>
                    <th>Evento</th>
                    <th>Colaborador</th>
                    <th>Área funcional</th>
                    <tbody>
                        <tr v-for="ev in lEvents">
                            <td>@{{ev.id_event}}</td>
                            <td>@{{ev.id_user}}</td>
                            <td>@{{ev.event}}</td>
                            <td>@{{ev.employee}}</td>
                            <td>@{{ev.area}}</td>
                        </tr>
                    </tbody>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var self;
</script>

@include('layouts.table_jsControll', [
                                        'table_id' => 'eventsTable',
                                        'colTargets' => [0,1,2],
                                        'colTargetsSercheable' => [],
                                        'rowGroup' => [2],
                                    ] )

<script type="text/javascript" src="{{ asset('myApp/usersInEvents/vue_usersInEvents.js') }}"></script>
@endsection
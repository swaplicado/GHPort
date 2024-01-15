@extends('layouts.principal')

@section('headStyles')
<link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.min.css")}}">
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
<link href="myApp/Utils/SDatePicker/css/datepicker.min.css" rel="stylesheet" />
<link href="myApp/Utils/SDatePicker/css/datepicker-bs4.min.css" rel="stylesheet" />
<link href="myApp/Utils/SDatePicker/css/datepicker-bulma.min.css" rel="stylesheet" />
<link href="myApp/Utils/SDatePicker/css/datepicker-foundation.min.css" rel="stylesheet" />

<style>
    .swal2-title {
        font-size: 24px !important;
    }
</style>

@endsection

@section('headJs')
<script src="{{ asset("daterangepicker/jquery.daterangepicker.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        function GlobalData(){
           //rutas
            this.users = <?php echo json_encode($dataUser); ?>;
            this.rute_get_work_personal = <?php echo json_encode(route("get_word_record_personal")); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="allDataPersonal">
    
    <div class="card-header">
        <h3>
            <b>Constancia laboral</b>
            @include('layouts.manual_button')
        </h3>
    </div>
    <div class="card-body">
        <br>
        @include('data_personal.record_table', ['table_id' => 'table_Incidences', 'table_ref' => 'table_Incidences'])
    </div>
</div>
@endsection

@section('scripts')

<script>
    moment.locale('es');
    $(document).ready(function () {
        
    });
</script>
@include('layouts.table_jsControll', [
                                        'table_id' => 'table_Incidences',
                                        'colTargets' => [],
                                        // 'colTargets' => [0,2,3,4,7,16,17],
                                        'colTargetsSercheable' => [],
                                        'noDom' => true,
                                        'select' => true,
                                        'crear_modal' => true,
                                        'edit_modal' => true,
                                        'delete' => true,
                                        'send' => true
                                    ] )
<script>
    $(document).ready(function (){
        
    });
</script>
<script type="text/javascript" src="{{ asset('myApp/DataPersonal/vue_data_personal.js') }}"></script>

@endsection
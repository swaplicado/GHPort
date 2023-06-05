@extends('layouts.principal')

@section('headStyles')
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        function GlobalData(){
            this.lannUsersChilds = <?php echo json_encode($lannUsersChilds); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="annAllUsers">

    <div class="card-header">
        <h3>
            <b> Aniversarios y cumpleaños de todos mis colaboradores</b>
        </h3>
    </div>
    <div class="card-body">
        <br>
        <br>
        <div class="table-responsive">
            <table class="table table-bordered" id="table_annUsers" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <th>Empleado</th>
                    <th>Área funcional</th>
                    <th>Aniversario</th>
                    <th>Cumpleaños</th>
                </thead>
                <tbody>
                    <tr v-for="ann in lannUsersChilds">
                        <td>@{{ann.name}}</td>
                        <td>@{{ann.area}}</td>
                        <td>@{{ann.ann}}</td>
                        <td>@{{ann.birth}}</td>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@section('scripts')
@include('layouts.table_jsControll', [
                                        'table_id' => 'table_annUsers',
                                        'colTargets' => [],
                                        'colTargetsSercheable' => [],
                                        // 'noSearch' => true,
                                        // 'noDom' => true,
                                        // 'noPaging' => true,
                                        // 'noInfo' => true,
                                        // 'noColReorder' => true,
                                        // 'noSort' => true
                                    ] )
    <script type="text/javascript" src="{{ asset('myApp/Adm/vue_annAllUsersChilds.js') }}"></script>
@endsection
@extends('layouts.principal')

@section('headJs')
    <script>
        function GlobalData(){
            this.lLogs = <?php echo json_encode($lLogs); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="logs">
    <div class="card-header">
        <h3>
            <b>BITÁCORAS</b>
            <a href="http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:bitacoras" target="_blank">
                <span class="bx bx-question-mark btn3d" style="display: inline-block; margin-left: 10px; background-color: #e4e4e4"></span>
            </a>
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4" style="padding-top: 1%;" v-for="log in lLogs">
                <a :href="log.route" target="_blank">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">@{{log.name}}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script type="text/javascript" src="{{ asset('myApp/Adm/vue_logs.js') }}"></script>
@endsection
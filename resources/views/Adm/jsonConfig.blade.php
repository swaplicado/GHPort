@extends('layouts.principal')

@section('headStyles')
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
<style>
    #sortable { list-style-type: none; margin: 0; padding: 0; }
    #sortable li { margin: 0 3px 3px 3px; padding-left: 1.5em; font-size: 1.4em; background-color: #C4C4C4; }
    #sortable li span { position: absolute; margin-left: -1.3em; }
</style>
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        function GlobalData(){
            this.lOrgchart = <?php echo json_encode($lOrgchart); ?>;
            this.route = <?php echo json_encode(route('update_config_json')); ?>;
            this.manualRoute = [];
            this.idRoot = <?php echo json_encode($data->root_node); ?>;
            this.idDefault = <?php echo json_encode($data->default_node); ?>;
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:tiposolicitudesespeciales" ); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="specialType">

    

    <div class="card-header">
        <h3>
            <b>Configuración</b>
            @include('layouts.manual_button')
        </h3>
    </div>
    <div class="card-body">
        <br>
        <br>
        <div class="col-md-8 offset-md-2">
            <div>
                <label for="selArea">Nodo raíz:</label>
                <select class="select2-class" id="selRoot" name="selRoot" v-model="selRoot" style="width: 90%;"></select>
            </div>
            <div>
                <label for="selArea">Nodo predeterminado:</label>
                <select class="select2-class" id="selDefault" name="selDefault" v-model="selDefault" style="width: 90%;"></select>
            </div>
            <br>
            <br>
            <div>
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" v-on:click="save();">Guardar</a>
            </div>
        </div>
            
    </div>
</div>
@endsection

@section('scripts')
    
    <script>
        var self;
    </script>
    <script type="text/javascript" src="{{ asset('myApp/Adm/vue_jsonConfig.js') }}"></script>
    @include('layouts.manual_jsControll')
@endsection
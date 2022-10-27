@extends('layouts.principal')

@section('headStyles')

@endsection

@section('headJs')
<script>
    function GlobalData(){
        this.lMails = <?php echo json_encode($lMails); ?>;
        this.year = <?php echo json_encode($year); ?>;
        this.sendMailRoute = <?php echo json_encode(route('mailLog_sendMail')); ?>;
        this.deleteMailRoute = <?php echo json_encode(route('mailLog_delete')); ?>;
        this.filterYearRoute = <?php echo json_encode(route('mailLog_filterYear')); ?>;
        this.constants = <?php echo json_encode($constants); ?>;
        this.indexes = {
            'id':0,
            'sys_mail_st_id':1,
            'date_mail':2,
            'Estatus': 3,
            'Type_mail':4,
            'to_user':5
        };
    }
    var oServerData = new GlobalData();
</script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="mailLogs">
    <div class="card-header">
        <h3>
            <b>E-Mails Log</b>
            <a href="#">
                <span class="bx bx-question-mark btn3d" style="display: inline-block; margin-left: 10px; background-color: #e4e4e4"></span>
            </a>
        </h3>
    </div>
    <div class="card-body">
        @include('layouts.table_buttons', ['send' => true, 'delete' => true])
        <div class="col-md-7" style="float: right; text-align: right; padding-right: 0 !important;">
            <label for="rqStatus">Filtrar por estatus: </label>
            <select class="form-control inline" name="rqStatus" id="mailStatus" style="width: 30%;">
                <option value="3" selected>No enviados</option>
                <option value="1">En proceso</option>
                <option value="2">Enviados</option>
            </select>
            @include('layouts.table_buttons', ['filterYear' => true])
        </div>
        <br>
        <br>
        <table class="table table-bordered" id="table_mails" style="width: 100%;">
            <thead>
                <th>mailLog_id</th>
                <th>sys_mail_st_id</th>
                <th>Fecha e-mail</th>
                <th>Estatus</th>
                <th>Tipo de e-mail</th>
                <th>Enviar a</th>
            </thead>
            <tbody>
                <tr v-for="mail in lMails">
                    <td>@{{mail.id_mail_log}}</td>
                    <td>@{{mail.sys_mails_st_id}}</td>
                    <td>@{{mail.date_log}}</td>
                    <td>@{{mail.mail_st_name}}</td>
                    <td>@{{mail.mail_tp_name}}</td>
                    <td>@{{mail.full_name_ui}}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    moment.locale('es');
    $(document).ready(function () {
        $.fn.dataTable.ext.search.push(
            function( settings, data, dataIndex ) {
                let registerVal = parseInt( $('#mailStatus').val(), 10 );
                let filter = 0;

                switch (registerVal) {
                    case 3:
                        filter = parseInt( data[oServerData.indexes.sys_mail_st_id] );
                        return filter === 3;
                        
                    case 2:
                        filter = parseInt( data[oServerData.indexes.sys_mail_st_id] );
                        return filter === 2;

                    case 1:
                        filter = parseInt( data[oServerData.indexes.sys_mail_st_id] );
                        return filter === 1;

                    default:
                        break;
                }

                return false;
            }
        );
    });
</script>
@include('layouts.table_jsControll', [
                                        'table_id' => 'table_mails',
                                        'colTargets' => [0],
                                        'colTargetsSercheable' => [1],
                                        'select' => true,
                                        // 'noSort' => true,
                                        'send' => true,
                                        'delete' => true,
                                        'order' => [[2, 'desc']],
                                    ] )
<script>
$(document).ready(function (){
    $('#mailStatus').change( function() {
        table['table_mails'].draw();
    });
});
</script>
<script type="text/javascript" src="{{ asset('myApp/mails/vue_mailLog.js') }}"></script>
@endsection
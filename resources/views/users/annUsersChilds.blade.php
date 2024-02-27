@extends('layouts.principal')

@section('headStyles')
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
<link href={{asset('myApp/Utils/singleDateRangePicker/daterangepicker.css')}} rel="stylesheet" />
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        function GlobalData(){
            this.lannUsersChilds = <?php echo json_encode($lannUsersChilds); ?>;
            this.startDate = <?php echo json_encode($startDate) ?>;
            this.endDate = <?php echo json_encode($endDate) ?>;
            
            this.indexes = {
                'name': 0,
                'area': 1,
                'aniv': 2,
                'birthday': 3
            }
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="annUsers">

    <div class="card-header">
        <h3>
            <b> Aniversarios y cumpleaños de mis colaboradores directos</b>
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-5">
                <div class="row">
                    <div class="col-md-3">
                        <label for="daterange">Filtrar por aniversario:</label>
                    </div>
                    <div class="col-md-8">
                        <input type="text" name="daterange" id="daterange" class="form-control"/>
                    </div>
                    <div class="col-md-1">
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="row">
                    <div class="col-md-3">
                        <label for="daterange">Filtrar por cumpleaños:</label>
                    </div>
                    <div class="col-md-8">
                        <input type="text" name="daterangeBirthday" id="daterangeBirthday" class="form-control"/>
                    </div>
                    <div class="col-md-1">
                    </div>
                </div>
            </div>
            <div class="col-md-1">
                <div class="row">
                    <button class="btn btn-primary" onclick="showAll();">
                        Quitar filtro de fecha
                    </button>
                </div>
            </div>
        </div>
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
<script type="text/javascript" src="{{ asset('myApp/Utils/singleDateRangePicker/daterangepicker.js') }}"></script>

<script>
    var oDatePickerAniv;
    var startAniv = '';
    var endAniv = '';
    var filterByAniv = false;
    
    var oDatePickerBirthay;
    var startBirthday = '';
    var endBirthday = '';
    var filterByBirthday = false;

    var oShowAll = true;
    moment.locale('es');
    $(function() {
        oDatePickerAniv = $('input[name="daterange"]').daterangepicker({
                                opens: 'left',
                                locale: {
                                    format: 'DD [de] MMMM [de] YYYY',
                                    applyLabel: 'Aplicar',
                                    cancelLabel: 'Cancelar',
                                    fromLabel: 'Desde',
                                    toLabel: 'Hasta',
                                    customRangeLabel: 'Rango personalizado',
                                    weekLabel: 'S',
                                    daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
                                    monthNames: ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'],
                                    firstDay: 1
                                },
                                startDate: moment(oServerData.startDate),
                                endDate: moment(oServerData.endDate),
                            }, function(start, end, label) {
                                startAniv = start.format('YYYY-MM-DD');
                                endAniv = end.format('YYYY-MM-DD');
                                filterAniv();
                                console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
                            });

        oDatePickerBirthay = $('input[name="daterangeBirthday"]').daterangepicker({
                                opens: 'left',
                                locale: {
                                    format: 'DD [de] MMMM [de] YYYY',
                                    applyLabel: 'Aplicar',
                                    cancelLabel: 'Cancelar',
                                    fromLabel: 'Desde',
                                    toLabel: 'Hasta',
                                    customRangeLabel: 'Rango personalizado',
                                    weekLabel: 'S',
                                    daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
                                    monthNames: ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'],
                                    firstDay: 1
                                },
                                startDate: moment(oServerData.startDate),
                                endDate: moment(oServerData.endDate),
                            }, function(start, end, label) {
                                startBirthday = start.format('YYYY-MM-DD');
                                endBirthday = end.format('YYYY-MM-DD');
                                filterBirthday();
                                console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
                            });

        oDatePickerAniv.val('');
        oDatePickerBirthay.val('');

        oDatePickerBirthay.on('apply.daterangepicker', function(ev, picker) {
            filterBirthday();
        })

        oDatePickerAniv.on('apply.daterangepicker', function(ev, picker) {
            filterAniv();
        })

        $('#daterange').on('change', function() {
            filterAniv();
        });

        $('#daterangeBirthday').on('change', function() {
            filterBirthday();
        });

    });
</script>
<script>
    function filterAniv(){
        oShowAll = false;
        filterByAniv = true;
        filterByBirthday = false;
        oDatePickerBirthay.val('');
        table['table_annUsers'].draw();
    }

    function filterBirthday(){
        oShowAll = false;
        filterByAniv = false;
        filterByBirthday = true;
        oDatePickerAniv.val('');
        table['table_annUsers'].draw();
    }

    function showAll(){
        oShowAll = true;
        filterByAniv = false;
        filterByBirthday = false;
        oDatePickerAniv.val('');
        oDatePickerBirthay.val('');
        table['table_annUsers'].draw();
    }
</script>
<script>
    $(document).ready(function () {
        $.fn.dataTable.ext.search.push(
            function( settings, data, dataIndex ) {
                let colAnivDate = moment(moment(data[oServerData.indexes['aniv']]).format('MM-DD'));
                let oStartAniv = moment(moment(startAniv).format('MM-DD'));
                let oEndAniv = moment(moment(endAniv).format('MM-DD'));
                let oColAnivDate = moment(moment(colAnivDate).format('MM-DD'));

                let colBirthdayDate = moment(data[oServerData.indexes['birthday']]).format('MM-DD');
                let oStartBirthday = moment(moment(startBirthday).format('MM-DD'));
                let oEndBirthday = moment(moment(endBirthday).format('MM-DD'));
                let oColBirthdayDate = moment(moment(colBirthdayDate).format('MM-DD'));

                if(!oShowAll){
                    if(filterByAniv){
                        return oColAnivDate.isBetween(oStartAniv, oEndAniv);
                    }
                }else{
                    return true;
                }

                if(!oShowAll){
                    if(filterByBirthday){
                        return oColBirthdayDate.isBetween(oStartBirthday, oEndBirthday);
                    }    
                }else{
                    return true;
                }
            }
        );
    });
</script>
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
    <script type="text/javascript" src="{{ asset('myApp/Adm/vue_annUsersChilds.js') }}"></script>
@endsection
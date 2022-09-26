@extends('layouts.principal')

@section('headStyles')
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('.select2-class').select2({
                dropdownParent: $('#editModal')
            });
        })
    </script>
    <script>
        function GlobalData(){
            this.lAreas = <?php echo json_encode($lAreas); ?>;
            this.lUsers = <?php echo json_encode($lUsers); ?>;
            this.updateRoute = <?php echo json_encode( route('update_assignArea') ); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="assignArea">

<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Area: @{{area}}</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <label for="selUser">Usuario encargado:</label>
                <select class="select2-class" id="selUser" name="selUser" style="width: 90%;"></select>
                <label for="selArea">Area superior:</label>
                <select class="select2-class" id="selArea" name="selArea" style="width: 90%;"></select>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" v-on:click="save();">Guardar</a>
            </div>
        </div>
    </div>
</div>

    <div class="card-body">
        <button id="btn_edit" type="button" class="btn3d btn-warning" style="border-radius: 50%; padding: 5px 10px;" title="Editar registro">
            <span class="icon bx bx-edit-alt"></span>
        </button>
        <br>
        <div class="table-responsive">
            <table class="table table-bordered display" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Area_id</th>
                        <th>father_area_id</th>
                        <th>user_id</th>
                        <th>Area</th>
                        <th>Responsable area</th>
                        <th>Area superior</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="area in lAreas">
                        <td>@{{area.id_area}}</td>
                        <td>@{{area.father_area_id}}</td>
                        <td>@{{area.user_id}}</td>
                        <td>@{{area.area}}</td>
                        <td>@{{area.head_user}}</td>
                        <td>@{{area.father_area}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script type="text/javascript">
        var table;
        $(document).ready(function() {
            table = $('#dataTable').DataTable({
                "language": {
                    "sProcessing":     "Procesando...",
                    "sLengthMenu":     "Mostrar _MENU_ registros",
                    "sZeroRecords":    "No se encontraron resultados",
                    "sEmptyTable":     "Ningún dato disponible en esta tabla",
                    "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                    "sInfoPostFix":    "",
                    "sSearch":         "Buscar:",
                    "sUrl":            "",
                    "sInfoThousands":  ",",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": {
                        "sFirst":    "Primero",
                        "sLast":     "Último",
                        "sNext":     "Siguiente",
                        "sPrevious": "Anterior"
                    },
                    "oAria": {
                        "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    }
                },
                "colReorder": true,
                "responsive": true,
                "dom": 'Bfrtip',
                "lengthMenu": [
                    [ 10, 25, 50, 100, -1 ],
                    [ 'Mostrar 10', 'Mostrar 25', 'Mostrar 50', 'Mostrar 100', 'Mostrar todo' ]
                ],
                "columnDefs": [
                    {
                        "targets": [0,1,2],
                        "visible": false,
                        "searchable": false,
                    }
                ],
                "buttons": [
                        'pageLength',
                        {
                            extend: 'copy',
                            text: 'Copiar'
                        }, 
                        'csv', 
                        'excel', 
                        {
                            extend: 'print',
                            text: 'Imprimir'
                        }
                    ],
                    "initComplete": function(){ 
                        $("#dataTable").show(); 
                    }
            });

            $('#dataTable tbody').on('click', 'tr', function () {
                if ($(this).hasClass('selected')) {
                    $(this).removeClass('selected');
                }
                else {
                    table.$('tr.selected').removeClass('selected');
                    $(this).addClass('selected');
                }
            });

            $('#btn_edit').click(function () {
                if (table.row('.selected').data() == undefined) {
                    SGui.showError("Debe seleccionar un renglón");
                    return;
                }

                app.showModal(table.row('.selected').data());
            });
        } );
    </script>
    <script type="text/javascript" src="{{ asset('myApp/Adm/vueAssignArea.js') }}"></script>
@endsection
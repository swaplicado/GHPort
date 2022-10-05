<script>
    var table;
    $(document).ready(function() {
        table = $('#{{$table_id}}').DataTable({
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
                    "colReorder": true,
                    "initComplete": function(){ 
                        $("#{{$table_id}}").show(); 
                    }
                });
    
        $('#{{$table_id}} tbody').on('click', 'tr', function () {
            if ($(this).hasClass('selected')) {
                $(this).removeClass('selected');
            }
            else {
                table.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
            }
        });
    
        /**
         * Editar un registro con formulario
         */
        @if(isset($edit_form))
            $('#btn_edit').click(function () {
                if (table.row('.selected').data() == undefined) {
                    SGui.showError("Debe seleccionar un renglón");
                    return;
                }
        
                var id = table.row('.selected').data()[0];
                var url = '{{route($editar, ":id")}}';
                url = url.replace(':id',id);
                window.location.href = url;
            });
        @endif
    
        /**
         * Editar un registro con vue modal
         */
        @if(isset($edit_modal))
            $('#btn_edit').click(function () {
                if (table.row('.selected').data() == undefined) {
                    SGui.showError("Debe seleccionar un renglón");
                    return;
                }
        
                app.showModal(table.row('.selected').data());
            });
        @endif
    });
</script>
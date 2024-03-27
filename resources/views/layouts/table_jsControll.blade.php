<script>
    var table = new Object();
    table["{{$table_id}}"] = '';
    $(document).ready(function() {
        table['{{$table_id}}'] = $('#{{$table_id}}').DataTable({
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
                        },
                        'select': {
                            'rows': {
                                _: "%d renglones seleccionados",
                                0: "Haz clic en un renglón para seleccionarlo",
                                1: "1 renglón seleccionado"
                            }
                        }
                    },
                    // "scrollX": true,
                    "responsive": false,
                    @if(isset($selectMulti))
                        "select": "multi",
                    @endif
                    @if(isset($noInfo))
                        "info": false,
                    @endif
                    @if(isset($noSearch))
                        "searching": false,
                    @endif
                    @if(isset($noPaging))
                        "paging": false,
                    @endif
                    @if(isset($noColReorder))
                        "colReorder": false,
                    @else
                        "colReorder": true,
                    @endif
                    @if(isset($noOrdering))
                        "ordering": false,
                    @endif
                    @if(isset($noSort))
                        "bSort": false,
                    @endif
                    @if(!isset($noDom))
                        "dom": 'Bfrtip',
                    @endif
                    @if(isset($order))
                        "order": <?php echo json_encode($order) ?>,
                    @endif
                    @if(isset($responsive))
                        "responsive": true,
                    @endif
                    @if(isset($lengthMenu))
                        "lengthMenu": <?php echo json_encode($lengthMenu) ?>,
                    @else
                        "lengthMenu": [
                            [ 10, 25, 50, 100, -1 ],
                            [ 'Mostrar 10', 'Mostrar 25', 'Mostrar 50', 'Mostrar 100', 'Mostrar todo' ]
                        ],
                    @endif
                    @if(isset($ordering))
                        "ordering": true,
                    @endif
                    "columnDefs": [
                        {
                            "targets": <?php echo json_encode($colTargets) ?>,
                            "visible": false,
                            "searchable": false,
                            "orderable": false,
                        },
                        {
                            "targets": <?php echo json_encode($colTargetsSercheable) ?>,
                            "visible": false,
                            "searchable": true,
                            "orderable": false,
                        },
                        {
                            @if(isset($colTargetsNoOrder))
                                "targets": <?php echo json_encode($colTargetsNoOrder) ?>,
                                "visible": true,
                                "orderable": false,
                                // "targets": "no-sort",
                            @endif
                        }
                    ],
                    "buttons": [
                            'pageLength',
                            {
                                extend: 'copy',
                                text: 'Copiar'
                            }, 
                            'csv', 
                            @if(isset($exportOptions))
                                {
                                    extend: 'excel',
                                    text: 'Excel',
                                    action: function(e, dt, button, config) {
                                        var data = dt.buttons.exportData({
                                            columns: ':not(.exclude)' // Excluir columnas con la clase 'exclude'
                                        });

                                        // Obtener los nombres de las columnas y aplicar formato en negrita
                                        var columns = dt.columns(':not(.exclude)').header().toArray().map(function(node) {
                                            return {v: node.innerText, s: {font: {bold: true}}};
                                        });

                                        // Insertar los encabezados de las columnas al inicio de los datos
                                        data.body.unshift(columns);

                                        // Crear el libro de Excel
                                        var workbook = XLSX.utils.book_new();
                                        var sheet = XLSX.utils.aoa_to_sheet(data.body);

                                        // Ajustar automáticamente el ancho de las columnas al contenido
                                        var wscols = data.body.map(function(row) {
                                            return row.map(function(cell) {
                                                return {wch: cell.toString().length};
                                            });
                                        })[0];
                                        sheet['!cols'] = wscols;

                                        // Agregar la hoja al libro
                                        XLSX.utils.book_append_sheet(workbook, sheet, 'Hoja 1');

                                        // Escribir el libro en formato de array
                                        var wbout = XLSX.write(workbook, {bookType:'xlsx', type:'array'});

                                        // Descargar el archivo
                                        saveAs(new Blob([wbout], {type: 'application/octet-stream'}), 'PortalGh.xlsx');
                                    }
                                },
                            @else
                                'excel',
                            @endif
                            {
                                extend: 'print',
                                text: 'Imprimir'
                            },
                        ],
                    "initComplete": function(){ 
                        // $("#{{$table_id}}").show();
                        $("#{{$table_id}}").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
                    },

                    @if(isset($rowGroup))
                        "rowGroup": {
                            dataSrc: {{$rowGroup[0]}}, // Índice de la columna por la cual deseas agrupar
                            @if(isset($rowGroupNotSelectable))
                                select: false,
                            @endif
                        },
                    @endif

                    @if(isset($rowsGroup))
                        "rowGroup": {
                            dataSrc: <?php echo json_encode($rowsGroup) ?>, // Índice de la columna por la cual deseas agrupar
                            @if(isset($rowGroupNotSelectable))
                                select: false
                            @endif
                        },
                    @endif

                    @if(isset($rowGroupNotSelectable))
                        "rowCallback": function(row, data) {
                            if ($(row).hasClass('group')) {
                                $(row).find('input[type="checkbox"]').prop('disabled', true); // Deshabilita la selección en el renglón de agrupación
                            }
                        }
                    @endif
                });

        @if(isset($selectRowGroup))
            $('#{{$table_id}} tbody').on('click', 'tr.dtrg-group', function () {
                if(!$(this).hasClass('noSelectableRow')){
                    if ($(this).hasClass('selected')) {
                        $(this).removeClass('selected');
                    }
                    else {
                        // table['{{$table_id}}'].$('tr.selected').removeClass('selected');
                        $(this).addClass('selected');
                    }
                }
            });
        @endif

        @if(isset($noSelectableRow))
            $('#{{$table_id}} tbody').on('click', 'tr', function () {
                if ($(this).hasClass('noSelectableRow')) {
                    return false; // Evitar la selección si la fila tiene la clase 'noSelectableRow'
                }
            });
        @endif

        @if(isset($select))
            $('#{{$table_id}} tbody').on('click', 'tr', function () {
                if(!$(this).hasClass('noSelectableRow')){
                    if ($(this).hasClass('selected')) {
                        $(this).removeClass('selected');
                    }
                    else {
                        table['{{$table_id}}'].$('tr.selected').removeClass('selected');
                        $(this).addClass('selected');
                    }
                }
            });
        @endif

        @if(isset($rowGroupNotSelectable))
            // Escucha el evento de selección
            $('#{{$table_id}} tbody').on('click', 'tr', function() {
                var row = table['{{$table_id}}'].row(this);
                if (!$(this).hasClass('dtrg-group')) {
                    row.select(); // Solo selecciona el renglón si no tiene la clase 'group'
                }else{
                    $(this).removeClass('selected');
                }
            });
        @endif
    
        /**
         * Editar un registro con formulario
         */
        @if(isset($edit_form))
            $('#btn_edit').click(function () {
                if (table['{{$table_id}}'].row('.selected').data() == undefined) {
                    SGui.showError("Debe seleccionar un renglón");
                    return;
                }
        
                var id = table['{{$table_id}}'].row('.selected').data()[0];
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
                if (table['{{$table_id}}'].row('.selected').data() == undefined) {
                    SGui.showError("Debe seleccionar un renglón");
                    return;
                }
        
                app.showModal(table['{{$table_id}}'].row('.selected').data());
            });
        @endif

        /**
         * Crear un registro con vue modal
         */
        @if(isset($crear_modal))
            $('#btn_crear').click(function () {        
                app.showModal();
            });
        @endif

        /**
         * Borrar un registro con vue
         */
        @if(isset($delete))
            $('#btn_delete').click(function  () {
                if (table['{{$table_id}}'].row('.selected').data() == undefined) {
                    SGui.showError("Debe seleccionar un renglón");
                    return;
                }
                app.deleteRegistry(table['{{$table_id}}'].row('.selected').data());
            });
        @endif

        /**
         * Enviar un registro con vue
         */
        @if(isset($send))
            $('#btn_send').click(function  () {
                if (table['{{$table_id}}'].row('.selected').data() == undefined) {
                    SGui.showError("Debe seleccionar un renglón");
                    return;
                }
                app.sendRegistry(table['{{$table_id}}'].row('.selected').data());
            });
        @endif

        /**
         * Aprobar un registro con vue
         */
        @if(isset($accept))
            $('#btn_accept').click(function  () {
                if (table['{{$table_id}}'].row('.selected').data() == undefined) {
                    SGui.showError("Debe seleccionar un renglón");
                    return;
                }
                app.showAcceptRegistry(table['{{$table_id}}'].row('.selected').data());
            });
        @endif

        /**
         * Rechazar un registro con vue
         */
        @if(isset($reject))
            $('#btn_reject').click(function  () {
                if (table['{{$table_id}}'].row('.selected').data() == undefined) {
                    SGui.showError("Debe seleccionar un renglón");
                    return;
                }
                app.showRejectRegistry(table['{{$table_id}}'].row('.selected').data());
            });
        @endif
        
        @if(isset($show))
            $('#btn_show').click(function () {
                if(table['{{$table_id}}'].row('.selected').data() == undefined){
                    SGui.showError("Debe seleccionar un renglón");
                    return;
                }

                app.showDataModal(table['{{$table_id}}'].row('.selected').data());
            });
        @endif

        @if(isset($cancel))
            $('#btn_cancel').click(function () {
                if(table['{{$table_id}}'].row('.selected').data() == undefined){
                    SGui.showError("Debe seleccionar un renglón");
                    return;
                }

                app.cancelRegistry(table['{{$table_id}}'].row('.selected').data());
            });
        @endif
    });
</script>
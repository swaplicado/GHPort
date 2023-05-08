var app = new Vue({
    el: '#incidencesApp',
    data: {
        oData: oServerData,
        lPermissions: oServerData.lPermissions,
        oCopylPermissions: structuredClone(oServerData.lPermissions),
        oDateUtils: new SDateUtils(),
        vacationUtils: new vacationUtils(),
        indexes_permissions: oServerData.indexes_permissions,
        oUser: oServerData.oUser,
        lTypes: oServerData.lTypes,
        lTypesToFilter: [],
        filter_type_id: oServerData.lTypes[0].id_permission_tp,
        select_changed: false,
        type_id: null,
        lTemp: oServerData.lTemp,
        showCalendar: false,
        is_singleDate: false,
        startDate: null,
        endDate: null,
        returnDate: null,
        totCalendarDays: null,
        takedDays: null,
        showDatePickerSimple: false,
        lDays: [],
        comments: null,
        valid: true,
        idApplication: null,
        oApplication: oServerData.oApplication,
        isEdit: false,
        type_name: null,
        is_normal: true,
        is_past: false,
        is_season_special: false,
        lSpecialTypes: [],
        isRevision: false,
        table_name: oServerData.table_name,
        lEmployees: oServerData.lEmployees,
        needRenderTablePermission: false,
        renderTableReqPermission: false,
    },
    updated() {
        this.$nextTick(function() {
            if (typeof self.$refs.table_Permissions != 'undefined' && self.needRenderTablePermission) {
                this.createTable('table_Permissions', [0, 2, 3, 4, 16], [1, 5, 6]);
                let dataTpFilter = [];
                for (let i = 0; i < this.lTypes.length; i++) {
                    dataTpFilter.push({ id: this.lTypes[i].id_permission_tp, text: this.lTypes[i].permission_tp_name });
                }

                $('#myPermission_tp_filter').select2({
                    data: dataTpFilter,
                });

                self.filterIncidenceTable();
            }
        })
    },
    mounted() {
        self = this;

        let dataTpFilter = [];
        let dataTp = [];
        for (let i = 0; i < this.lTypes.length; i++) {
            dataTp.push({ id: this.lTypes[i].id_permission_tp, text: this.lTypes[i].permission_tp_name });
            dataTpFilter.push({ id: this.lTypes[i].id_permission_tp, text: this.lTypes[i].permission_tp_name });
        }

        $('.select2-class-modal').select2({
            dropdownParent: $('#iModal')
        });

        $('.select2-class').select2({});

        $('#permission_tp_filter').select2({
            data: dataTpFilter,
        }).on('select2:select', function(e) {
            self.filter_type_id = e.params.data.id;
        });

        $('#permission_tp').select2({
            placeholder: 'Selecciona tipo de permiso',
            data: dataTp,
        }).on('select2:select', function(e) {
            self.type_id = e.params.data.id;
        });

        $('#permission_tp').val('').trigger('change');

        if (!!this.oApplication && !!this.oUser) {
            let data = [this.oApplication.id_application];
            this.showDataModal(data);
        }
    },
    methods: {
        initRequestincidences() {
            this.isRevision = true;
            this.oUser = null;
            table['table_ReqIncidences'].draw();
        },

        initGestionIncidences() {
            this.needRenderTableIncidences = true;
            this.isRevision = false;
            this.oUser = null;
            this.setSelectEmployees();
        },

        createTable(table_name, colTargets = [], colTargetsSercheable = []) {
            table[table_name] = $('#' + table_name).DataTable({
                "language": {
                    "sProcessing": "Procesando...",
                    "sLengthMenu": "Mostrar _MENU_ registros",
                    "sZeroRecords": "No se encontraron resultados",
                    "sEmptyTable": "Ningún dato disponible en esta tabla",
                    "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "sInfoPostFix": "",
                    "sSearch": "Buscar:",
                    "sUrl": "",
                    "sInfoThousands": ",",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": {
                        "sFirst": "Primero",
                        "sLast": "Último",
                        "sNext": "Siguiente",
                        "sPrevious": "Anterior"
                    },
                    "oAria": {
                        "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    }
                },
                "responsive": false,
                "dom": 'Bfrtip',
                "columnDefs": [{
                        "targets": colTargets,
                        "visible": false,
                        "searchable": false,
                        "orderable": false,
                    },
                    {
                        "targets": colTargetsSercheable,
                        "visible": false,
                        "searchable": true,
                        "orderable": false,
                    },
                    {
                        "orderable": false,
                        "targets": "no-sort",
                    }
                ],
                "buttons": [

                ],
                "paging": false,
                "dom": 'Bfrtip',
                "initComplete": function() {
                    $("#" + table_name).wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
                    $('#' + table_name + ' tbody').on('click', 'tr', function() {
                        if ($(this).hasClass('selected')) {
                            $(this).removeClass('selected');
                        } else {
                            table[table_name].$('tr.selected').removeClass('selected');
                            $(this).addClass('selected');
                        }
                    });

                    /**
                     * Editar un registro con vue modal
                     */
                    $('#btn_edit').click(function() {
                        if (table[table_name].row('.selected').data() == undefined) {
                            SGui.showError("Debe seleccionar un renglón");
                            return;
                        }

                        app.showModal(table[table_name].row('.selected').data());
                    });

                    /**
                     * Crear un registro con vue modal
                     */
                    $('#btn_crear').click(function() {
                        app.showModal();
                    });

                    /**
                     * Borrar un registro con vue
                     */
                    $('#btn_delete').click(function() {
                        if (table[table_name].row('.selected').data() == undefined) {
                            SGui.showError("Debe seleccionar un renglón");
                            return;
                        }
                        app.deleteRegistry(table[table_name].row('.selected').data());
                    });

                    /**
                     * Enviar un registro con vue
                     */
                    $('#btn_send').click(function() {
                        if (table[table_name].row('.selected').data() == undefined) {
                            SGui.showError("Debe seleccionar un renglón");
                            return;
                        }
                        app.sendRegistry(table[table_name].row('.selected').data());
                    });
                },
            });


            this.reDrawTableIncidences('table_Incidences', this.lIncidences);
            this.needRenderTableIncidences = false;
        },

        arraysEqual(a, b) {
            if (a.length !== b.length) {
                return false;
            }
            return a.every(element => b.includes(element));
        },

        createCalendar(val, enable = true) {
            $('#clear').trigger('click');
            if (!!val) {
                this.showCalendar = true;

                this.is_singleDate = true;
                initCalendar(
                    null,
                    true,
                    true,
                    this.oUser.payment_frec_id,
                    this.lTemp,
                    oServerData.lHolidays,
                    this.oUser.birthday_n,
                    this.oUser.benefits_date,
                    enable,
                );
            }
        },

        setClass(val) {
            let lDataTypes = [];
            lDataTypes = this.lTypes.filter(function(item) {
                return item.incidence_cl_id == val;
            });

            let dataTypes = [];
            for (let i = 0; i < lDataTypes.length; i++) {
                dataTypes.push({ id: lDataTypes[i].id_incidence_tp, text: lDataTypes[i].incidence_tp_name });
            }

            $('#incident_type').empty().trigger("change");

            $('#incident_type').select2({
                placeholder: 'Selecciona tipo de incidencia',
                data: dataTypes,
            });

            $('#incident_type').val('').trigger('change');
            this.type_id = null;
        },

        getApplication() {
            SGui.showWaiting(15000);
            return new Promise((resolve) =>
                axios.post(this.oData.routeGetIncidence, {
                    'application_id': this.idApplication,
                })
                .then(result => {
                    let data = result.data;
                    if (data.success) {
                        this.oApplication = data.oApplication;
                        // swal.close();
                        resolve(data.oApplication);
                    } else {
                        SGui.showMessage('', data.message, data.icon);
                        resolve(null);
                    }
                })
                .catch(function(error) {
                    console.log(error);
                    SGui.showError(error);
                    resolve(error);
                })
            );
        },

        async showModal(data = null) {
            $('#clear').trigger('click');
            $('#permission_type').empty().trigger("change");
            this.cleanData();
            if (data != null) {
                this.isEdit = true;
                this.idApplication = data[this.indexes_incidences.id_application];
                await this.getApplication();
                this.class_id = this.oApplication.id_incidence_cl;
                this.type_id = this.oApplication.id_incidence_tp;
                this.class_name = this.oApplication.incidence_cl_name;
                this.type_name = this.oApplication.incidence_tp_name;
                this.valid = this.oApplication.request_status_id == this.oData.constants.APPLICATION_CREADO;
                $('#date-range-001').val(this.oApplication.start_date);
                $('#date-range-002').val(this.oApplication.end_date);
                this.createCalendar(this.type_id, this.valid);
                this.showCalendar = true;
                this.startDate = this.oDateUtils.formatDate(this.oApplication.start_date, 'ddd DD-MMM-YYYY');
                this.endDate = this.oDateUtils.formatDate(this.oApplication.end_date, 'ddd DD-MMM-YYYY');
                $('#date-range-001').val(this.oApplication.start_date).trigger('change');
                $('#date-range-002').val(this.oApplication.end_date).trigger('change');
                this.returnDate = this.oDateUtils.formatDate(this.oApplication.return_date, 'ddd DD-MMM-YYYY');
                this.totCalendarDays = this.oApplication.tot_calendar_days;
                this.takedDays = this.oApplication.total_days;
                this.lDays = this.formatlDays(this.oApplication.ldays);
                this.comments = this.oApplication.emp_comments_n;
                this.is_normal = this.oApplication.is_normal;
                this.is_past = this.oApplication.is_past;
                this.is_season_special = this.oApplication.is_season_special;
                Swal.close();
            } else {
                if (this.HasIncidencesCreated()) {
                    SGui.showMessage('', 'No puede crear otra incidencia si tiene incidencias creadas pendientes de enviar', 'warning');
                    return;
                }
            }
            $('#modal_incidences').modal('show');
        },

        setMyReturnDate() {
            if (this.endDate != null && this.endDate != undefined && this.endDate != '') {
                this.MyReturnDate = datepicker.getDate('dd-mm-yyyy');
                this.returnDate = this.oDateUtils.formatDate(this.MyReturnDate, 'ddd DD-MMM-YYYY');
            }
            this.showDatePickerSimple = false;
        },

        editMyReturnDate() {
            datepicker.setDate({ clear: !0 });
            if (this.endDate != null && this.endDate != undefined && this.endDate != '') {
                datepicker.setOptions({ minDate: moment(this.endDate, 'ddd DD-MMM-YYYY').add(1, 'days').format("DD-MM-YYYY") });
            } else {
                datepicker.setOptions({ minDate: null });
            }
            this.showDatePickerSimple = true;
        },

        setTakedDay(index, checkbox_id) {
            let checked = $('#' + checkbox_id).is(":checked");
            this.lDays[index].taked = checked;
            checked ? this.takedDays++ : this.takedDays--;
        },

        setApplication(route) {
            let copylDays = structuredClone(this.lDays);
            for (let index = 0; index < copylDays.length; index++) {
                copylDays[index].date = moment(copylDays[index].date, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD');
            }

            axios.post(route, {
                    'id_application': this.idApplication,
                    'startDate': moment(this.startDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                    'endDate': moment(this.endDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                    'comments': this.comments,
                    'takedDays': this.takedDays,
                    'returnDate': moment(this.returnDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                    'tot_calendar_days': this.totCalendarDays,
                    'employee_id': this.oUser.id,
                    'lDays': copylDays,
                    'incident_type_id': this.type_id,
                    'incident_class_id': this.class_id,
                    'is_normal': this.is_normal,
                    'is_past': this.is_past,
                    'is_season_special': this.is_season_special,
                })
                .then(result => {
                    let data = result.data;
                    if (data.success) {
                        this.oCopylIncidences = data.lIncidences;
                        this.reDrawTableIncidences('table_Incidences', data.lIncidences);
                        SGui.showOk();
                        $('#modal_incidences').modal('hide');
                    } else {
                        SGui.showMessage('', data.message, data.icon);
                    }
                })
                .catch(function(error) {
                    console.log(error);
                    SGui.showError(error);
                });
        },

        save() {
            SGui.showWaiting(15000);

            if (this.startDate == null || this.startDate == "") {
                SGui.showMessage('', 'Debe ingresar una fecha de inicio');
                return;
            }

            if (this.endDate == null || this.endDate == "") {
                SGui.showMessage('', 'Debe ingresar una fecha de fin');
                return;
            }

            if (this.isEdit) {
                this.setApplication(this.oData.routeUpdate);
            } else {
                this.setApplication(this.oData.routeCreate);
            }
        },

        cleanData() {
            // this.lTemp = oServerData.lTemp;
            this.oApplication = null;
            this.class_id = null;
            this.type_id = null;
            this.showCalendar = false;
            this.is_singleDate = false;
            this.startDate = null;
            this.endDate = null;
            this.returnDate = null;
            this.totCalendarDays = null;
            this.takedDays = null;
            this.showDatePickerSimple = false;
            this.lDays = [];
            this.comments = null;
            this.valid = true;
            this.idApplication = null;
            this.isEdit = false;
            // this.isRevision = false;
        },

        reDrawTableIncidences(table_name, lIncidences) {
            var dataIncidences = [];
            for (let incident of lIncidences) {
                dataIncidences.push(
                    [
                        incident.id_application,
                        incident.request_status_id,
                        incident.emp_comments_n,
                        incident.sup_comments_n,
                        incident.user_apr_rej_id,
                        incident.id_incidence_cl,
                        incident.id_incidence_tp,
                        incident.incidence_tp_name,
                        incident.folio_n,
                        (incident.date_send_n != null ?
                            this.oDateUtils.formatDate(incident.date_send_n, 'ddd DD-MMM-YYYY') :
                            this.oDateUtils.formatDate(incident.updated_at, 'ddd DD-MMM-YYYY')
                        ),
                        incident.user_apr_rej_name,
                        (incident.request_status_id == this.oData.constants.APPLICATION_APROBADO) ?
                        this.oDateUtils.formatDate(incident.approved_date_n, 'ddd DD-MMM-YYYY') :
                        ((incident.request_status_id == this.oData.constants.APPLICATION_RECHAZADO) ?
                            this.oDateUtils.formatDate(incident.rejected_date_n, 'ddd DD-MMM-YYYY') :
                            ''),
                        this.oDateUtils.formatDate(incident.start_date, 'ddd DD-MMM-YYYY'),
                        this.oDateUtils.formatDate(incident.end_date, 'ddd DD-MMM-YYYY'),
                        this.oDateUtils.formatDate(incident.return_date, 'ddd DD-MMM-YYYY'),
                        incident.total_days,
                        'SUBTIPO',
                        incident.applications_st_name,
                    ]
                );
            }
            table[table_name].clear().draw();
            table[table_name].rows.add(dataIncidences).draw();
        },

        HasIncidencesCreated() {
            for (let rec of this.oCopylIncidences) {
                if (rec.request_status_id == 1) {
                    return true;
                }
            }

            return false;
        },

        deleteRegistry(data) {
            if (data[this.indexes_incidences.applications_st_name] != 'CREADO') {
                SGui.showMessage('', 'Solo se pueden eliminar incidencias con el estatus CREADO', 'warning');
                return;
            }

            Swal.fire({
                title: '¿Desea eliminar la incidencia ' + data[this.indexes_incidences.folio_n] + ' ?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deleteIncidence(data[this.indexes_incidences.id_application]);
                }
            })
        },

        deleteIncidence(application_id) {
            SGui.showWaiting(15000);
            axios.post(this.oData.routeDelete, {
                    'application_id': application_id,
                })
                .then(response => {
                    var data = response.data;
                    if (data.success) {
                        this.oCopylIncidences = data.lIncidences;
                        this.reDrawTableIncidences('table_Incidences', data.lIncidences);
                        SGui.showOk();
                    } else {
                        SGui.showMessage('', data.message, data.icon);
                    }
                })
                .catch(function(error) {
                    console.log(error);
                    SGui.showError(error);
                });
        },

        checkSpecial() {
            let message = "";
            let is_special = false;
            this.is_normal = true;
            this.is_past = false;
            this.is_season_special = false;

            if (moment(this.endDate, 'ddd DD-MMM-YYYY').isBefore(moment(this.today)) || moment(this.endDate, 'ddd DD-MMM-YYYY').isSame(moment(this.today)) || moment(this.startDate, 'ddd DD-MMM-YYYY').isBefore(moment(this.today)) || moment(this.startDate, 'ddd DD-MMM-YYYY').isSame(moment(this.today))) {
                message = message + "Se tomarán días pasados.\n";
                is_special = true;
                this.is_normal = false;
                this.is_past = true;
                this.lSpecialTypes.push('Con días pasados');
            }

            for (let oSeason of this.lTemp) {
                for (let day of oSeason.lDates) {
                    if (moment(day, 'YYYY-MM-DD').isBetween(moment(this.startDate, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD'), moment(this.endDate, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD'), undefined, '[]')) {
                        message = message + 'Estas tomando días en temporada especial ' + oSeason.name + "\n";
                        is_special = true;
                        this.is_normal = false;
                        this.is_season_special = true;
                        this.lSpecialTypes.push('Con días en temporada especial');
                        break;
                    }
                }
            }

            if (this.is_normal) {
                this.lSpecialTypes.push('Normal');
            }

            return [is_special, message];
        },

        sendRegistry(data) {
            if (data[this.indexes_incidences.applications_st_name] != 'CREADO') {
                SGui.showMessage('', 'Solo se pueden enviar incidencias con el estatus CREADO', 'warning');
                return
            }
            Swal.fire({
                title: '¿Desea enviar la incidencia ' + data[this.indexes_incidences.folio_n] + ' ?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.sendIncident(data[this.indexes_incidences.id_application]);
                }
            })
        },

        sendIncident(application_id) {
            SGui.showWaiting(15000);
            axios.post(this.oData.routeGestionSendIncidence, {
                    'application_id': application_id,
                })
                .then(response => {
                    var data = response.data;
                    if (data.success) {
                        this.oCopylIncidences = data.lIncidences;
                        this.reDrawTableIncidences('table_Incidences', data.lIncidences);
                        SGui.showOk();
                    } else {
                        SGui.showMessage('', data.message, data.icon);
                    }
                })
                .catch(function(error) {
                    console.log(error);
                    SGui.showError(error);
                });
        },

        /**
         * Metodo utilizado en la vista de revisor para obtener el usuario de la incidencia
         */
        getEmployee(user_id) {
            return new Promise((resolve) =>
                axios.post(this.oData.routeGetEmployee, {
                    'user_id': user_id,
                })
                .then(result => {
                    let data = result.data;
                    if (data.success) {
                        this.oUser = data.oUser;
                        this.lTemp = data.lTemp;
                        resolve(data.oUser);
                    } else {
                        SGui.showMessage('', data.message, data.icon);
                        resolve(null);
                    }
                })
                .catch(function(error) {
                    console.log(error);
                    SGui.showError(error);
                    resolve(error);
                })
            );
        },

        approbeIncidence() {
            SGui.showWaiting(15000);
            axios.post(this.oData.routeApprobe, {
                    'application_id': this.oApplication.id_application,
                    'comments': this.comments,
                    'returnDate': moment(this.returnDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                })
                .then(result => {
                    let data = result.data;
                    if (data.success) {
                        this.oCopylIncidences = data.lIncidences;
                        this.reDrawTableIncidences('table_ReqIncidences', data.lIncidences);
                        $('#modal_incidences').modal('hide');
                        SGui.showOk();
                    } else {
                        SGui.showMessage('', data.message, data.icon);
                    }
                })
                .catch(function(error) {
                    console.log(error);
                    SGui.showError(error);
                });
        },

        rejectIncidence() {
            SGui.showWaiting(15000)
            axios.post(this.oData.routeReject, {
                    'application_id': this.oApplication.id_application,
                    'comments': this.comments,
                    'returnDate': moment(this.returnDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                })
                .then(result => {
                    let data = result.data;
                    if (data.success) {
                        this.oCopylIncidences = data.lIncidences;
                        this.reDrawTableIncidences('table_ReqIncidences', data.lIncidences);
                        $('#modal_incidences').modal('hide');
                        SGui.showOk();
                    } else {
                        SGui.showMessage('', data.message, data, icon);
                    }
                })
                .catch(function(error) {
                    console.log(error);
                    SGui.showError(error);
                })
        },

        /**
         * Show modal para la vista de revision de incidencias
         * @param {*} data 
         */
        async showDataModal(data) {
            // SGui.showWaiting(15000);
            this.cleanData();
            $('#clear').trigger('click');
            this.isRevision = true;
            this.idApplication = data[this.indexes_incidences.id_application];
            await this.getApplication();
            await this.getEmployee(this.oApplication.user_id);
            this.class_id = this.oApplication.id_incidence_cl;
            this.type_id = this.oApplication.id_incidence_tp;
            this.class_name = this.oApplication.incidence_cl_name;
            this.type_name = this.oApplication.incidence_tp_name;
            this.showCalendar = true;
            this.startDate = this.oDateUtils.formatDate(this.oApplication.start_date, 'ddd DD-MMM-YYYY');
            this.endDate = this.oDateUtils.formatDate(this.oApplication.end_date, 'ddd DD-MMM-YYYY');
            // $('#date-range-001').val(this.oApplication.start_date).trigger('change');
            // $('#date-range-002').val(this.oApplication.end_date).trigger('change');
            $('#date-range-001').val(this.oApplication.start_date);
            $('#date-range-002').val(this.oApplication.end_date);
            this.createCalendar(this.type_id, false);
            this.returnDate = this.oDateUtils.formatDate(this.oApplication.return_date, 'ddd DD-MMM-YYYY');
            this.totCalendarDays = this.oApplication.tot_calendar_days;
            this.takedDays = this.oApplication.total_days;
            this.lDays = this.formatlDays(this.oApplication.ldays);
            this.comments = this.oApplication.emp_comments_n;
            this.is_normal = this.oApplication.is_normal;
            this.is_past = this.oApplication.is_past;
            this.is_season_special = this.oApplication.is_season_special;
            this.valid = this.oApplication.request_status_id == this.oData.constants.APPLICATION_ENVIADO;
            Swal.close();
            if (this.oApplication.request_status_id == this.oData.constants.APPLICATION_APROBADO) {
                Swal.fire({
                    title: '',
                    html: 'Esta solicitud ya ha sido aprobada por: ' +
                        '<br>' +
                        this.oApplication.revisor +
                        '<br>' +
                        'Con fecha: ' +
                        '<br>' +
                        this.oDateUtils.formatDate(this.oApplication.approved_date_n, 'ddd DD-MMM-YYYY'),
                    icon: 'info',
                });
            } else if (this.oApplication.request_status_id == this.oData.constants.APPLICATION_RECHAZADO) {
                Swal.fire({
                    title: '',
                    html: 'Esta solicitud ya ha sido rechazada por: ' +
                        '<br>' +
                        this.oApplication.revisor +
                        '<br>' +
                        'Con fecha: ' +
                        '<br>' +
                        this.oDateUtils.formatDate(this.oApplication.rejected_date_n, 'ddd DD-MMM-YYYY'),
                    icon: 'info',
                });
            }
            $('#modal_incidences').modal('show');
        },

        getAllEmployees() {
            SGui.showWaiting(15000);
            axios.get(this.oData.routeGetAllEmployees, {

                })
                .then(result => {
                    let data = result.data;
                    if (data.success) {
                        this.lEmployees = data.lEmployees;
                        this.setSelectEmployees();
                        SGui.showWaiting(15000);
                    } else {
                        SGui.showMessage('', data.message, data.icon);
                    }
                })
                .catch(function(error) {
                    console.log(error);
                    SGui.showError(error);
                });
        },

        setGestionIncidences() {
            SGui.showWaiting(15000);
            this.getEmployeeData();
            this.needRenderTableIncidences = true;
        },

        getEmployeeData() {
            this.cleanData();
            this.oUser = null;
            let user_id = null;
            if (!!$('#selectEmp').val()) {
                user_id = $('#selectEmp').val();
            }
            axios.post(this.oData.routeGetEmployee, {
                    'user_id': user_id,
                })
                .then(result => {
                    let data = result.data;
                    if (data.success) {
                        this.oUser = data.oUser;
                        this.lIncidences = data.lIncidences;
                        this.lTemp = data.lTemp;
                        SGui.showOk();
                    } else {
                        SGui.showMessage('', data.message, data.icon);
                    }
                })
                .catch(function(error) {
                    console.log(error);
                    SGui.showError(error);
                });
        },

        setSelectEmployees() {
            if (!!$('#selectEmp')) {
                let dataEmp = []
                for (let i = 0; i < this.lEmployees.length; i++) {
                    dataEmp.push({ id: this.lEmployees[i].id, text: this.lEmployees[i].employee });
                }
                $('#selectEmp').empty().trigger('change');
                $('#selectEmp').select2({
                    data: dataEmp
                })

                $('#selectEmp').val('').trigger('change');
            }
        },

        filterIncidenceTable() {
            table['table_Incidences'].draw();
        },
    }
});
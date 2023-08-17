var app = new Vue({
    el: '#permissionsApp',
    data: {
        oData: oServerData,
        initialCalendarDate: oServerData.initialCalendarDate,
        lPermissions: oServerData.lPermissions,
        oCopylPermissions: structuredClone(oServerData.lPermissions),
        oDateUtils: new SDateUtils(),
        vacationUtils: new vacationUtils(),
        indexes_permission: oServerData.indexes_permission,
        oUser: oServerData.oUser,
        lSuperviser: oServerData.lSuperviser,
        myManagers: oServerData.myManagers,
        selectedmanager: null,
        lEmployees: oServerData.lEmployees,
        lTemp: oServerData.lTemp,
        lTypes: oServerData.lTypes,
        lClass: oServerData.lClass,
        isRevision: false,
        isEdit: false,
        filter_type_id: 0,
        type_id: null,
        class_id: null,
        comments: null,
        emp_comments: null,
        oPermission: oServerData.oPermission,
        startDate: null,
        endDate: null,
        valid: true,
        permission_id: null,
        hours: 0,
        minutes: "00",
        needRenderTableIncidences: false,
        time: null,
        type_name: null,
        permission_time: oServerData.permission_time,
        status_incidence: 0,
        max_hours: 0,
        max_minutes: 0,
    },
    watch: {

    },
    updated() {
        this.$nextTick(function() {
            if (typeof self.$refs.table_permissions != 'undefined' && self.needRenderTableIncidences) {
                this.createTable('table_permissions', [0, 2, 3, 4, 6, 17], [1, 5]);
                let dataTypeFilter = [{ id: '0', text: 'Todos' }];
                let dataType = [];
                let dataClass = [];
                for (let i = 0; i < this.lTypes.length; i++) {
                    dataType.push({ id: this.lTypes[i].id_permission_tp, text: this.lTypes[i].permission_tp_name });
                    dataTypeFilter.push({ id: this.lTypes[i].id_permission_tp, text: this.lTypes[i].permission_tp_name });
                }

                for (let i = 0; i < this.lClass.length; i++) {
                    dataClass.push({ id: this.lClass[i].id_permission_cl, text: this.lClass[i].permission_cl_name });
                }

                $('#myPermission_tp_filter').select2({
                    data: dataTypeFilter,
                }).on('select2:select', function(e) {
                    // self.filter_type_id = e.params.data.id;
                    self.filterPermissionsTable();
                });

                $('#myStatus_myPermission').change(function() {
                    self.filterPermissionsTable();
                });

                self.filterPermissionsTable();

                $('#permission_type').select2({
                    placeholder: 'Selecciona tipo de permiso',
                    data: dataType,
                }).on('select2:select', function(e) {
                    self.type_id = e.params.data.id;
                });

                $('#permission_cl').select2({
                    placeholder: 'Selecciona clase de permiso',
                    data: dataClass,
                }).on('select2:select', function(e) {
                    self.class_id = e.params.data.id;
                    self.cambiarValor();
                });

                $('#permission_cl').val('').trigger('change');
            }
        })
    },
    mounted() {
        self = this;

        $('.select2-class-modal').select2({
            dropdownParent: $('#modal_permission')
        });

        $('.select2-class').select2({});

        let dataTypeFilter = [{ id: '0', text: 'Todos' }];
        let dataType = [];
        let dataClass = [];
        for (let i = 0; i < this.lTypes.length; i++) {
            dataType.push({ id: this.lTypes[i].id_permission_tp, text: this.lTypes[i].permission_tp_name });
            dataTypeFilter.push({ id: this.lTypes[i].id_permission_tp, text: this.lTypes[i].permission_tp_name });
        }

        for (let i = 0; i < this.lClass.length; i++) {
            dataClass.push({ id: this.lClass[i].id_permission_cl, text: this.lClass[i].permission_cl_name });
        }

        $('#permission_tp_filter').select2({
            data: dataTypeFilter,
        }).on('select2:select', function(e) {
            self.filter_type_id = e.params.data.id;
        });

        $('#permission_type').select2({
            placeholder: 'Selecciona tipo de permiso',
            data: dataType,
        }).on('select2:select', function(e) {
            self.type_id = e.params.data.id;
        });

        $('#permission_type').val('').trigger('change');

        $('#permission_cl').select2({
            placeholder: 'Selecciona clase de permiso',
            data: dataClass,
        }).on('select2:select', function(e) {
            self.class_id = e.params.data.id;
            self.cambiarValor();
        });

        $('#permission_cl').val('').trigger('change');

        if (!!this.oPermission && !!this.oUser) {
            let data = [this.oPermission.id_hours_leave];
            this.showDataModal(data);
        }

        if (!!this.myManagers) {
            var dataMyManagers = [];
            for (let i = 0; i < this.myManagers.length; i++) {
                dataMyManagers.push({ id: this.myManagers[i].id, text: this.myManagers[i].full_name_ui });
            }

            $('#selManager')
                .select2({
                    placeholder: 'selecciona',
                    data: dataMyManagers,
                });

            $('#selManager').val('').trigger('change');
        }

        $('#status_ReqPermission').on('change', function() {
            self.status_incidence = this.value;
        });
    },
    methods: {
        initGestionPermissions() {
            this.needRenderTableIncidences = true;
            this.isRevision = false;
            this.oUser = null;
            this.setSelectEmployees();
        },

        initRequestPermissions() {
            this.isRevision = true;
            this.oUser = null;
            table['table_ReqPermissions'].draw();
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


            this.reDrawTablePermissions('table_permissions', this.lPermissions);
            this.needRenderTableIncidences = false;
        },

        createCalendar(enable = true) {
            $('#clear').trigger('click');
            initCalendar(
                this.initialCalendarDate,
                true,
                true,
                this.oUser.payment_frec_id,
                this.lTemp,
                oServerData.lHolidays,
                this.oUser.birthday_n,
                this.oUser.benefits_date,
                enable,
            );
        },

        checkMaxPermissionTime() {
            let time = (this.hours * 60) + parseInt(this.minutes);

            if (time > this.permission_time) {
                let horas = Math.floor(this.permission_time / 60);
                let minutosRestantes = this.permission_time % 60;
                SGui.showMessage('', 'No puede ingresar mas de ' + horas + ' y ' + minutosRestantes + ' minutos.');

                this.hours = horas;
                this.minutes = minutosRestantes;

                this.minutes = Math.floor(this.minutes);
                this.minutes = (this.minutes).toString();
                this.minutes = this.minutes.padStart(2, "0");

                return false;
            }

            return true;
        },

        formatValueMinutes() {
            if (!(!!this.minutes)) {
                this.minutes = "00";
            }

            if (this.minutes < 0) {
                this.minutes = "00";
                SGui.showMessage('', 'Los minutos deben ser mayor o igual a 0', 'info');
            }

            if (this.minutes.length > 2) {
                this.minutes = this.minutes.slice(0, 2);
            }

            let res = this.checkMaxPermissionTime();

            if (this.minutes > 59) {
                this.minutes = 59;
                SGui.showMessage('', 'Los minutos deben ser menor a 60', 'info');
            }

            // if(this.hours > 2){
            //     this.minutes = "00";
            //     SGui.showMessage('', 'No puede ingresar mas de 3 horas');
            // }

            this.minutes = Math.floor(this.minutes);
            this.minutes = (this.minutes).toString();
            this.minutes = this.minutes.padStart(2, "0");

            if (!res) {
                return;
            }
        },

        formatValueHours() {
            if (!(!!this.hours)) {
                this.hours = 0;
            }

            if (this.hours < 0) {
                this.hours = 0;
                SGui.showMessage('', 'Las horas deben ser mayor o igual a 0', 'info');
            }

            let res = this.checkMaxPermissionTime();

            // if(this.hours > 3){
            //     this.hours = 3;
            //     SGui.showMessage('', 'No puede ingresar mas de 3 horas');
            // }

            // if(this.hours > 2){
            //     this.minutes = "00";
            // }

            this.hours = Math.floor(this.hours);

            if (!res) {
                return;
            }
        },

        focusHours() {
            this.hours = null;
        },

        focusMinutes() {
            this.minutes = null;
        },

        getPermission() {
            SGui.showWaiting(15000);
            return new Promise((resolve) =>
                axios.post(this.oData.routeGetPermission, {
                    'permission_id': this.permission_id,
                })
                .then(result => {
                    let data = result.data;
                    if (data.success) {
                        this.oPermission = data.oPermission;
                        this.type_name = this.oPermission.permission_tp_name;
                        resolve(data.oPermission);
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

        cleanData() {
            this.permission_id = null;
            this.hours = 0;
            this.minutes = "00";
            this.type_id = null;
            $('#permission_type').val('').trigger('change');
            this.class_id = null;
            $('#permission_cl').val('').trigger('change');
            this.comments = null;
            this.isEdit = false;
        },

        async showModal(data = null) {
            $('#clear').trigger('click');
            this.cleanData();
            if (data != null) {
                this.isEdit = true;
                this.permission_id = data[this.indexes_permission.id];
                await this.getPermission();
                this.hours = this.oPermission.hours;
                this.minutes = this.oPermission.min;
                this.type_id = this.oPermission.type_permission_id;
                $('#permission_type').val(this.type_id).trigger('change');
                this.class_id = this.oPermission.cl_permission_id;
                $('#permission_cl').val(this.class_id).trigger('change');
                this.valid = this.oPermission.request_status_id == this.oData.constants.APPLICATION_CREADO;
                $('#date-range-001').val(this.oPermission.start_date);
                $('#date-range-002').val(this.oPermission.end_date);
                this.createCalendar(this.valid);
                this.startDate = this.oDateUtils.formatDate(this.oPermission.start_date, 'ddd DD-MMM-YYYY');
                this.endDate = this.oDateUtils.formatDate(this.oPermission.end_date, 'ddd DD-MMM-YYYY');
                $('#date-range-001').val(this.oPermission.start_date).trigger('change');
                $('#date-range-002').val(this.oPermission.end_date).trigger('change');
                this.comments = this.oPermission.emp_comments_n;
                Swal.close();
            } else {
                if (this.HasPermissionsCreated()) {
                    SGui.showMessage('', 'No puede crear otra incidencia si tiene incidencias creadas pendientes de enviar', 'warning');
                    return;
                }
                this.createCalendar();
            }
            $('#modal_permission').modal('show');
        },

        HasPermissionsCreated() {
            for (let rec of this.oCopylPermissions) {
                if (rec.request_status_id == 1) {
                    return true;
                }
            }
            return false;
        },

        async save() {
            if (!(!!this.class_id)) {
                SGui.showMessage('', 'Debe ingresar la clase de permiso');
                return;
            }
            if (!(!!this.type_id)) {
                SGui.showMessage('', 'Debe ingresar el tipo de permiso');
                return;
            }

            if (this.startDate == null || this.startDate == "") {
                SGui.showMessage('', 'Debe ingresar una fecha');
                return;
            }

            if ((this.hours < 1 && this.minutes < 1) || this.hours == null && this.minutes == null) {
                SGui.showMessage('', 'Debe ingresar tiempo de permiso');
                return;
            }

            SGui.showWaiting(15000);

            if (this.isEdit) {
                this.setApplication(this.oData.routeUpdate);
            } else {
                this.setApplication(this.oData.routeCreate);
            }
        },

        setApplication(route) {
            axios.post(route, {
                    'permission_id': this.permission_id,
                    'startDate': moment(this.startDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                    'comments': this.comments,
                    'class_id': this.class_id,
                    'type_id': this.type_id,
                    'employee_id': this.oUser.id,
                    'hours': this.hours,
                    'minutes': this.minutes,
                })
                .then(result => {
                    let data = result.data;
                    if (data.success) {
                        this.oCopylPermissions = data.lPermissions;
                        this.reDrawTablePermissions('table_permissions', data.lPermissions);
                        SGui.showOk();
                        $('#modal_permission').modal('hide');
                    } else {
                        SGui.showMessage('', data.message, data.icon);
                    }
                })
                .catch(function(error) {

                });
        },

        reDrawTablePermissions(table_name, lPermissions) {
            var dataPermissions = [];
            for (let permission of lPermissions) {
                dataPermissions.push(
                    [
                        permission.id_hours_leave,
                        permission.request_status_id,
                        permission.emp_comments_n,
                        permission.sup_comments_n,
                        permission.user_apr_rej_id,
                        permission.type_permission_id,
                        permission.cl_permission_id,
                        permission.employee,
                        permission.permission_tp_name,
                        permission.permission_cl_name,
                        permission.time,
                        permission.folio_n,
                        (permission.date_send_n != null ?
                            this.oDateUtils.formatDate(permission.date_send_n, 'ddd DD-MMM-YYYY') :
                            this.oDateUtils.formatDate(permission.updated_at, 'ddd DD-MMM-YYYY')
                        ),
                        permission.user_apr_rej_name,
                        (permission.request_status_id == this.oData.constants.APPLICATION_APROBADO) ?
                        this.oDateUtils.formatDate(permission.approved_date_n, 'ddd DD-MMM-YYYY') :
                        ((permission.request_status_id == this.oData.constants.APPLICATION_RECHAZADO) ?
                            this.oDateUtils.formatDate(permission.rejected_date_n, 'ddd DD-MMM-YYYY') :
                            ''),
                        this.oDateUtils.formatDate(permission.start_date, 'ddd DD-MMM-YYYY'), !this.isRevision ? permission.applications_st_name :
                        (permission.request_status_id == 2 ? 'NUEVO' : permission.applications_st_name),
                        permission.date_send_n
                    ]
                );
            }
            table[table_name].clear().draw();
            table[table_name].rows.add(dataPermissions).draw();
        },

        deleteRegistry(data) {
            if (data[this.indexes_permission.Estatus] != 'CREADO') {
                SGui.showMessage('', 'Solo se pueden eliminar permisos con el estatus CREADO', 'warning');
                return;
            }

            Swal.fire({
                title: '¿Desea eliminar el permiso ' + data[this.indexes_permission.Folio] + ' ?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deletePermission(data[this.indexes_permission.id]);
                }
            })
        },

        deletePermission(permission_id) {
            SGui.showWaiting(15000);
            axios.post(this.oData.routeDelete, {
                    'permission_id': permission_id,
                    'employee_id': this.oUser.id,
                })
                .then(response => {
                    var data = response.data;
                    if (data.success) {
                        this.oCopylPermissions = data.lPermissions;
                        this.reDrawTablePermissions('table_permissions', data.lPermissions);
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

        getAllEmployees() {
            SGui.showWaiting(15000);
            let is_checked = document.getElementById('checkBoxAllEmployees').checked;

            if (is_checked) {
                axios.post(this.oData.routeGetAllEmployees, {

                    })
                    .then(result => {
                        let data = result.data;
                        if (data.success) {
                            this.lEmployees = data.lEmployees;
                            this.setSelectEmployees();
                            SGui.showOk();
                        } else {
                            SGui.showMessage('', data.message, data.icon);
                        }
                    })
                    .catch(function(error) {
                        console.log(error);
                        SGui.showError(error);
                    });
            } else {
                axios.post(this.oData.routeGetDirectEmployees, {

                    })
                    .then(result => {
                        let data = result.data;
                        if (data.success) {
                            this.lEmployees = data.lEmployees;
                            this.setSelectEmployees();
                            SGui.showOk();
                        } else {
                            SGui.showMessage('', data.message, data.icon);
                        }
                    })
                    .catch(function(error) {
                        console.log(error);
                        SGui.showError(error);
                    });
            }
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

        setGestionPermissions() {
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
                        this.lPermissions = data.lPermissions;
                        this.oCopylPermissions = data.lPermissions;
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

        filterPermissionsTable() {
            table['table_permissions'].draw();
        },

        sendAuthorize() {
            if (table['table_permissions'].row('.selected').data() == undefined) {
                SGui.showError("Debe seleccionar un renglón");
                return;
            }

            let data = table['table_permissions'].row('.selected').data();

            SGui.showWaiting(15000);

            axios.post(this.oData.routeSendAuthorize, {
                    'permission_id': data[this.indexes_permission.id],
                })
                .then(result => {
                    let data = result.data;
                    if (data.success) {
                        this.oCopylPermissions = data.lPermissions;
                        this.reDrawTablePermissions('table_permissions', data.lPermissions);
                        SGui.showOk();
                        this.checkMail(data.mailLog_id, this.oData.routeCheckMail);
                    } else {
                        SGui.showMessage('', data.message, data.icon);
                    }
                })
                .catch(function(error) {
                    console.log(error);
                    SGui.showError(error);
                })
        },

        sendRegistry(data) {
            if (data[this.indexes_permission.Estatus] != 'CREADO') {
                SGui.showMessage('', 'Solo se pueden enviar permisos con el estatus CREADO', 'warning');
                return
            }

            let message = '<b>Se enviará a:</b>' +
                '<br>' +
                '<ul>';

            for (const user of this.lSuperviser) {
                message = message + '<li>' + user.full_name_ui + '</li>';
            }
            message = message + '</ul>';

            Swal.fire({
                title: '¿Desea enviar el permiso ' + data[this.indexes_permission.Folio] + ' ?',
                html: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.sendPermission(data[this.indexes_permission.id]);
                }
            })
        },

        sendPermission(permission_id) {
            SGui.showWaiting(15000);
            axios.post(this.oData.routeGestionSendIncidence, {
                    'permission_id': permission_id,
                    'employee_id': this.oUser.id,
                })
                .then(response => {
                    var data = response.data;
                    if (data.success) {
                        this.oCopylPermissions = data.lPermissions;
                        this.reDrawTablePermissions('table_permissions', data.lPermissions);
                        SGui.showOk();
                        this.checkMail(data.mailLog_id, this.oData.routeCheckMail);
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

        /**
         * Show modal para la vista de revision de incidencias
         * @param {*} data 
         */
        async showDataModal(data) {
            $('#clear').trigger('click');
            this.cleanData();
            this.isRevision = true;
            this.permission_id = data[this.indexes_permission.id];
            await this.getPermission();
            await this.getEmployee(this.oPermission.user_id);
            this.hours = this.oPermission.hours;
            this.minutes = this.oPermission.min;
            this.time = this.oPermission.time;
            this.type_id = this.oPermission.type_permission_id;
            $('#permission_type').val(this.type_id).trigger('change');
            this.class_id = this.oPermission.cl_permission_id;
            $('#permission_cl').val(this.class_id).trigger('change');
            this.valid = this.oPermission.request_status_id == this.oData.constants.APPLICATION_ENVIADO;
            $('#date-range-001').val(this.oPermission.start_date);
            $('#date-range-002').val(this.oPermission.end_date);
            this.createCalendar(false);
            this.startDate = this.oDateUtils.formatDate(this.oPermission.start_date, 'ddd DD-MMM-YYYY');
            this.endDate = this.oDateUtils.formatDate(this.oPermission.end_date, 'ddd DD-MMM-YYYY');
            $('#date-range-001').val(this.oPermission.start_date).trigger('change');
            $('#date-range-002').val(this.oPermission.end_date).trigger('change');
            this.emp_comments = this.oPermission.emp_comments_n;
            this.comments = this.oPermission.sup_comments_n;
            Swal.close();
            if (this.oPermission.request_status_id == this.oData.constants.APPLICATION_APROBADO) {
                Swal.fire({
                    title: '',
                    html: 'Esta solicitud ya ha sido aprobada por: ' +
                        '<br>' +
                        this.oPermission.user_apr_rej_name +
                        '<br>' +
                        'Con fecha: ' +
                        '<br>' +
                        this.oDateUtils.formatDate(this.oPermission.approved_date_n, 'ddd DD-MMM-YYYY'),
                    icon: 'info',
                });
            } else if (this.oPermission.request_status_id == this.oData.constants.APPLICATION_RECHAZADO) {
                Swal.fire({
                    title: '',
                    html: 'Esta solicitud ya ha sido rechazada por: ' +
                        '<br>' +
                        this.oPermission.user_apr_rej_name +
                        '<br>' +
                        'Con fecha: ' +
                        '<br>' +
                        this.oDateUtils.formatDate(this.oPermission.rejected_date_n, 'ddd DD-MMM-YYYY'),
                    icon: 'info',
                });
            }
            $('#modal_permission').modal('show');
        },

        approbePermission() {
            SGui.showWaiting(15000);
            axios.post(this.oData.routeApprobe, {
                    'permission_id': this.oPermission.id_hours_leave,
                    'comments': this.comments,
                    'manager_id': this.selectedmanager,
                })
                .then(result => {
                    let data = result.data;
                    if (data.success) {
                        this.oCopylPermissions = data.lPermissions;
                        this.reDrawTablePermissions('table_ReqPermissions', data.lPermissions);
                        $('#modal_permission').modal('hide');
                        SGui.showOk();
                        this.checkMail(data.mailLog_id, this.oData.routeCheckMail);
                    } else {
                        SGui.showMessage('', data.message, data.icon);
                    }
                })
                .catch(function(error) {
                    console.log(error);
                    SGui.showError(error);
                });
        },

        rejectPermission() {
            SGui.showWaiting(15000)
            axios.post(this.oData.routeReject, {
                    'permission_id': this.oPermission.id_hours_leave,
                    'comments': this.comments,
                    'manager_id': this.selectedmanager,
                })
                .then(result => {
                    let data = result.data;
                    if (data.success) {
                        this.oCopylPermissions = data.lPermissions;
                        this.reDrawTablePermissions('table_ReqPermissions', data.lPermissions);
                        $('#modal_permission').modal('hide');
                        SGui.showOk();
                        this.checkMail(data.mailLog_id, this.oData.routeCheckMail);
                    } else {
                        SGui.showMessage('', data.message, data.icon);
                    }
                })
                .catch(function(error) {
                    console.log(error);
                    SGui.showError(error);
                })
        },

        sleep(milliseconds) {
            return new Promise((resolve) => setTimeout(resolve, milliseconds));
        },

        async checkMail(mail_log_id, route) {
            var checked = false;
            for (var i = 0; i < 10; i++) {
                await this.sleep(3000);

                if (!checked) {
                    axios.post(route, {
                            'mail_log_id': mail_log_id,
                        })
                        .then(response => {
                            var data = response.data;
                            if (data.status == 2) {
                                checked = true;
                                SGui.showMessage('', 'E-mail enviado con éxito', 'success');
                            } else if (data.status == 3) {
                                checked = true;
                                SGui.showMessage('', 'Ocurrio un error al enviar el e-mail, notifique a su colaborador', 'error');
                            }
                        })
                        .catch(function(error) {
                            console.log(error);
                        });
                }

                if (checked) {
                    break;
                }
            }
        },

        seeLikeManager() {
            this.selectedmanager = parseInt($('#selManager').val());
            if (!(!!this.selectedmanager)) {
                SGui.showMessage('', 'Debe seleccionar un supervisor', 'info');
                return;
            }
            SGui.showWaiting(15000);
            let manager_id = $('#selManager').val();
            let manager_name = $('#selManager').find(':selected').text();
            axios.post(this.oData.routeSeeLikeManager, {
                    'manager_id': parseInt(manager_id),
                    'manager_name': manager_name,
                })
                .then(result => {
                    let data = result.data;
                    if (data.success) {
                        SGui.showOk();
                        this.reDrawTablePermissions('table_ReqPermissions', data.lPermissions);
                    } else {
                        SGui.showMessage('', data.message, data.icon);
                    }
                })
                .catch(function(error) {
                    console.log(error);
                    SGui.showError(error);
                })
        },

        cleanManager() {
            SGui.showWaiting(15000);
            axios.post(this.oData.routeSeeLikeManager, {
                    'manager_id': null,
                    'manager_name': null,
                })
                .then(result => {
                    let data = result.data;
                    if (data.success) {
                        SGui.showOk();
                        this.reDrawTablePermissions('table_ReqPermissions', data.lPermissions);
                        $('#selManager').val('').trigger('change');
                        this.selectedmanager = null;
                    } else {
                        SGui.showMessage('', data.message, data.icon);
                    }
                })
                .catch(function(error) {
                    console.log(error);
                    SGui.showError(error);
                })
        },

        cancelRegistry(data) {
            Swal.fire({
                title: '¿Desea cancelar el permiso?',
                html: '<b>Colaborador: </b>' +
                    data[this.indexes_permission.empleado] +
                    '<br>' +
                    '<b>permiso:</b> ' +
                    data[this.indexes_permission.Permiso] +
                    '<b>Tiempo:</b> ' +
                    data[this.indexes_permission.tiempo],
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'No',
                confirmButtonText: 'Si'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.cancelPermission(data[this.indexes_permission.id]);
                }
            })
        },

        cancelPermission(application_id) {
            SGui.showWaiting(15000);

            let route = this.oData.routePermission_cancel;
            axios.post(route, {
                    'application_id': application_id,
                })
                .then(result => {
                    let data = result.data;
                    if (data.success) {
                        this.oCopylPermissions = data.lPermissions;
                        this.reDrawTablePermissions('table_ReqPermissions', data.lPermissions);
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
        cambiarValor() {
            const id_permission_cl = document.getElementById('permission_cl').value;
            for (let i = 0; i < this.lClass.length; i++) {
                if (this.lClass[i].id_permission_cl == id_permission_cl) {
                    this.permission_time = this.lClass[i].max_minutes
                    this.hours = 0;
                    this.minutes = '00';

                    this.max_hours = Math.floor(this.permission_time / 60);

                }
            }
        },
    }
});
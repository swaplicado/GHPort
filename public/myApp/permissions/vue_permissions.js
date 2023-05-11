var app = new Vue({
    el: '#incidencesApp',
    data: {
        oData: oServerData,
        lPermissions: oServerData.lPermissions,
        oCopylPermissions: structuredClone(oServerData.lPermissions),
        oDateUtils: new SDateUtils(),
        vacationUtils: new vacationUtils(),
        indexes_permission: oServerData.indexes_permission,
        oUser: oServerData.oUser,
        lTemp: oServerData.lTemp,
        lTypes: oServerData.lTypes,
        isRevision: false,
        isEdit: false,
        filter_type_id: 0,
        type_id: null,
        comments: null,
        emp_comments: null,
        oPermission: oServerData.oPermission,
        startDate: null,
        endDate: null,
        valid: true,
        permission_id: null,
        hours: 0,
        minutes: "00",
    },
    watch: {

    },
    updated() {
        
    },
    mounted() {
        var self = this;

        $('.select2-class-modal').select2({
            dropdownParent: $('#modal_permission')
        });

        $('.select2-class').select2({});

        let dataTypeFilter = [{id: '0', text: 'Todos'}];
        let dataType = [];
        for (let i = 0; i < this.lTypes.length; i++) {
            dataType.push({id: this.lTypes[i].id_permission_tp, text: this.lTypes[i].permission_tp_name });
            dataTypeFilter.push({id: this.lTypes[i].id_permission_tp, text: this.lTypes[i].permission_tp_name });
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
    },
    methods: {
        createCalendar(enable = true){
            $('#clear').trigger('click');
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
        },

        formatValueMinutes(){
            if(this.minutes < 0 || !(!!this.minutes)){
                this.minutes = "00";
            }
            
            if(this.minutes > 59){
                this.minutes = 59;
            }

            if(this.hours > 2){
                this.minutes = "00";
            }

            if(this.minutes.length > 2){
                this.minutes = this.minutes.slice(0, 2);
            }

            this.minutes = Math.floor(this.minutes);
            this.minutes = (this.minutes).toString();
            this.minutes = this.minutes.padStart(2, "0");
        },

        formatValueHours(){
            if(this.hours < 0 || !(!!this.hours)){
                this.hours = 0;
            }
            
            if(this.hours > 3){
                this.hours = 3;
            }

            if(this.hours > 2){
                this.minutes = "00";
            }

            this.hours = Math.floor(this.hours);
        },

        focusHours(){
            this.hours = null;
        },

        focusMinutes(){
            this.minutes = null;
        },

        getPermission(){
            SGui.showWaiting(15000);
            return new Promise((resolve) => 
                axios.post(this.oData.routeGetPermission, {
                    'permission_id': this.permission_id,
                })
                .then( result => {
                    let data = result.data;
                    if(data.success){
                        this.oPermission = data.oPermission;
                        resolve(data.oPermission);
                    }else{
                        SGui.showMessage('', data.message, data.icon);
                        resolve(null);
                    }
                })
                .catch( function(error){
                    console.log(error);
                    SGui.showError(error);
                    resolve(error);
                })
            );
        },

        cleanData(){

        },

        async showModal(data = null){
            $('#clear').trigger('click');
            this.cleanData();
            if(data != null){
                this.isEdit = true;
                this.permission_id = data[this.indexes_permission.id];
                await this.getPermission();
                this.hours = this.oPermission.hours;
                this.minutes = this.oPermission.min;
                this.type_id = this.oPermission.type_permission_id;
                $('#permission_type').val(this.type_id).trigger('change');
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
            }else{
                if(this.HasPermissionsCreated()){
                    SGui.showMessage('', 'No puede crear otra incidencia si tiene incidencias creadas pendientes de enviar', 'warning');
                    return;
                }
                this.createCalendar();
            }
            $('#modal_permission').modal('show');
        },

        HasPermissionsCreated(){
            for(let rec of this.oCopylPermissions){
                if(rec.request_status_id == 1){
                    return true;
                }
            }
            return false;
        },

        async save(){
            if(this.startDate == null || this.startDate == ""){
                SGui.showMessage('', 'Debe ingresar una fecha');
                return;
            }

            SGui.showWaiting(15000);

            if(this.isEdit){
                this.setApplication(this.oData.routeUpdate);
            }else{
                this.setApplication(this.oData.routeCreate);
            }
        },

        setApplication(route){
            axios.post(route,{
                'permission_id': this.permission_id,
                'startDate': moment(this.startDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                'comments': this.comments,
                'type_id': this.type_id,
                'employee_id': this.oUser.id,
                'hours': this.hours,
                'minutes': this.minutes,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.oCopylPermissions = data.lPermissions;
                    this.reDrawTablePermissions('table_permissions', data.lPermissions);
                    SGui.showOk();
                    $('#modal_permission').modal('hide');
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(error){

            });
        },

        reDrawTablePermissions(table_name, lPermissions){
            var dataPermissions = [];
            for(let permission of lPermissions){
                dataPermissions.push(
                    [
                        permission.id_hours_leave,
                        permission.request_status_id,
                        permission.emp_comments_n,
                        permission.sup_comments_n,
                        permission.user_apr_rej_id,
                        permission.type_permission_id,
                        permission.employee,
                        permission.permission_tp_name,
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
                        this.oDateUtils.formatDate(permission.start_date, 'ddd DD-MMM-YYYY'),
                        !this.isRevision ? permission.applications_st_name : 
                            (permission.request_status_id == 2 ? 'NUEVO' : permission.applications_st_name)
                    ]
                );
            }
            table[table_name].clear().draw();
            table[table_name].rows.add(dataPermissions).draw();
        },

        deleteRegistry(data){
            if(data[this.indexes_permission.Estatus] != 'CREADO'){
                SGui.showMessage('','Solo se pueden eliminar permisos con el estatus CREADO', 'warning');
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

        deletePermission(permission_id){
            SGui.showWaiting(15000);
            axios.post(this.oData.routeDelete, {
                'permission_id': permission_id,
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    this.oCopylPermissions = data.lPermissions;
                    this.reDrawTablePermissions('table_permissions', data.lPermissions);
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function(error) {
                console.log(error);
                SGui.showError(error);
            });
        },
    }
});
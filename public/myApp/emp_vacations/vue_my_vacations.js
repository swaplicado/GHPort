var app = new Vue({
    el: '#myVacations',
    data: {
        oData: oServerData,
        vacationUtils: new vacationUtils(),
        indexes: oServerData.indexes,
        oUser: oServerData.oUser,  //No modificar, mejor modificar oCopyUser
        oCopyUser: oServerData.oUser,
        lHolidays: oServerData.lHolidays,
        startDate: null,
        endDate: null,
        returnDate: null,
        comments: null,
        idRequest: null,
        status: null,
        takedDays: 0,
        lDays: [],
        lRec: [],
        year: oServerData.year,
        actual_vac_days: oServerData.oUser.actual_vac_days,
        prop_vac_days: oServerData.oUser.prop_vac_days,
        prox_vac_days: oServerData.oUser.prox_vac_days,
        take_rest_days: false,
        take_holidays: false
    },
    mounted(){
        
    },
    methods: {
        showModal(data = null){
            if(data != null){
                this.comments = data[this.indexes.comments];
                this.idRequest = data[this.indexes.id];
                this.status = data[this.indexes.status];
                this.take_holidays = parseInt(data[this.indexes.take_holidays]);
                this.take_rest_days = parseInt(data[this.indexes.take_rest_days]);
                $('#date-range200').val(data[this.indexes.start_date]).trigger('change');
			    $('#date-range201').val(data[this.indexes.end_date]).trigger('change');
            }else{
                if(this.HasRequestCreated()){
                    SGui.showMessage('', 'No puede crear otra solicitud de vacaciones si tiene solicitudes creadas pendientes de enviar', 'warning');
                    return;
                }
                this.startDate = null;
                this.endDate = null;
                this.returnDate = null;
                this.comments = null;
                this.idRequest = null;
                this.takedDays = 0;
                this.lDays = [];
                this.status = null;
                this.take_rest_days = false;
                this.take_holidays = false;
                $('#clear').trigger('click');
            }
            $('#modal_solicitud').modal('show');
        },

        getDataDays(){
            var result = this.vacationUtils.getTakedDays(
                            this.lHolidays,
                            this.oUser.payment_frec_id,
                            this.startDate,
                            this.endDate,
                            this.oData.const,
                            this.take_rest_days,
                            this.take_holidays
                        );

            this.returnDate = result[0];
            this.takedDays = result[1];
            this.lDays = result[2];
        },

        formatDate(sDate){
            return moment(sDate).format('YYYY-MM-DD');
        },

        requestVac(){
            if(this.startDate == null || this.startDate == '' || this.endDate == null || this.endDate == ''){
                SGui.showMessage('', 'Debe ingresar las fecha de inicio y fin de vacaciones', 'warning');
                return;
            }

            if(this.idRequest == null){
                var route = this.oData.requestVacRoute;
            }else{
                if(this.status != 'CREADO'){
                    SGui.showMessage('','Solo se pueden editar solicitudes con el estatus CREADO', 'warning');
                    return;
                }
                var route = this.oData.updateRequestVacRoute;
            }
            
            SGui.showWaiting(15000);
            axios.post(route, {
                'id_application': this.idRequest,
                'startDate': this.startDate,
                'endDate': this.endDate,
                'comments': this.comments,
                'takedDays': this.takedDays,
                // 'lDays': this.lDays,
                'take_holidays': this.take_holidays,
                'take_rest_days': this.take_rest_days,
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    this.actual_vac_days = data.oUser.actual_vac_days,
                    // this.prop_vac_days = data.oUser.prop_vac_days,
                    this.prox_vac_days = data.oUser.prox_vac_days
                    $('#modal_solicitud').modal('hide');
                    SGui.showOk();
                    this.oCopyUser = data.oUser;
                    this.reDrawVacationsTable(data);
                    this.reDrawRequestTable(data.oUser);
                    table['table_myRequest'].$('tr.selected').removeClass('selected');
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function(error) {
                console.log(error);
                SGui.showError(error);
            });
        },

        filterYear(){
            SGui.showWaiting(5000);
            axios.post(this.oData.filterYearRoute, {
                'year': this.year,
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    SGui.showOk();
                    this.oCopyUser = data.oUser;
                    this.reDrawRequestTable(data);
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function(error) {
                console.log(error);
                SGui.showError(error);
            });
        },

        deleteRequest(request_id){
            SGui.showWaiting(15000);
            axios.post(this.oData.deleteRequestRoute, {
                'id_application': request_id,
                'year': this.year,
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    this.actual_vac_days = data.oUser.actual_vac_days,
                    // this.prop_vac_days = data.oUser.prop_vac_days,
                    this.prox_vac_days = data.oUser.prox_vac_days
                    SGui.showOk();
                    this.oCopyUser = data.oUser;
                    this.reDrawVacationsTable(data);
                    this.reDrawRequestTable(data.oUser);
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function(error) {
                console.log(error);
                SGui.showError(error);
            });
        },

        reDrawRequestTable(data){
            var dataReq = [];
            for(let rec of data.applications){
                dataReq.push(
                    [
                        rec.id_application,
                        rec.request_status_id,
                        rec.take_holidays,
                        rec.take_rest_days,
                        this.formatDate(rec.created_at),
                        ((rec.request_status_id == this.oData.APPLICATION_APROBADO) ?
                            rec.approved_date_n :
                                ((rec.request_status_id == this.oData.APPLICATION_RECHAZADO) ?
                                    rec.approved_date_n :
                                        '')),
                        rec.start_date,
                        rec.end_date,
                        rec.returnDate,
                        rec.takedDays,
                        rec.applications_st_name,
                        rec.emp_comments_n
                    ]
                );
            }
            table['table_myRequest'].clear().draw();
            table['table_myRequest'].rows.add(dataReq).draw();
        },

        reDrawVacationsTable(data){
            var dataVac = [];
            for(let vac of data.oUser.vacation){
                dataVac.push(
                    [
                        vac.date_start + ' a ' + vac.date_end,
                        vac.id_anniversary,
                        vac.vacation_days,
                        vac.num_vac_taken,
                        vac.expired,
                        vac.request,
                        vac.remaining
                    ]
                );
            }
            dataVac.push(
                [
                    '',
                    'Total',
                    data.oUser.tot_vacation_days,
                    data.oUser.tot_vacation_taken,
                    data.oUser.tot_vacation_expired,
                    data.oUser.tot_vacation_request,
                    data.oUser.tot_vacation_remaining
                ]
            );
            table['vacationsTable'].clear().draw();
            table['vacationsTable'].rows.add(dataVac).draw();
        },

        deleteRegistry(data){
            if(data[this.indexes.status] != 'CREADO'){
                SGui.showMessage('','Solo se pueden eliminar solicitudes con el estatus CREADO', 'warning');
                return;
            }

            Swal.fire({
                title: '¿Desea eliminar la solicitud para las fechas?',
                text: 'Inicio: ' + data[this.indexes.start_date] + '\n' + 'Fin: ' +  data[this.indexes.end_date],
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deleteRequest(data[this.indexes.id]);
                }
            })
        },

        sendRegistry(data){
            if(data[this.indexes.status] != 'CREADO'){
                SGui.showMessage('','Solo se pueden enviar solicitudes con el estatus CREADO', 'warning');
                return
            }
            Swal.fire({
                title: '¿Desea enviar la solicitud para las fechas?',
                text: 'Inicio: ' + data[this.indexes.start_date] + ' ' + 'Fin: ' +  data[this.indexes.end_date],
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.startDate = data[this.indexes.start_date];
                    this.endDate = data[this.indexes.end_date];
                    this.comments = data[this.indexes.comments];
                    this.idRequest = data[this.indexes.id];
                    this.status = data[this.indexes.status];
                    this.getDataDays();
                    this.sendRequest(data[this.indexes.id]);
                }
            })
        },

        sendRequest(request_id){
            SGui.showWaiting(15000);
            axios.post(this.oData.sendRequestRoute, {
                'id_application': request_id,
                'year': this.year,
                'lDays': this.lDays,
                'returnDate': this.returnDate
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    this.actual_vac_days = data.oUser.actual_vac_days,
                    // this.prop_vac_days = data.oUser.prop_vac_days,
                    this.prox_vac_days = data.oUser.prox_vac_days
                    SGui.showMessage('', data.message, data.icon);
                    this.oCopyUser = data.oUser;
                    this.reDrawVacationsTable(data);
                    this.reDrawRequestTable(data.oUser);
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function(error) {
                console.log(error);
                SGui.showError(error);
            });
        },

        HasRequestCreated(){
            // console.log(this.oCopyUser);
            for(let rec of this.oCopyUser.applications){
                if(rec.request_status_id == 1){
                    return true;
                }
            }

            return false;
        }
    },
})
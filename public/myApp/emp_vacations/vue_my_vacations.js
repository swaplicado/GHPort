var app = new Vue({
    el: '#myVacations',
    data: {
        oData: oServerData,
        oDateUtils: new SDateUtils(),
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
        take_holidays: false,
        applicationsEA: []
    },
    mounted(){
        
    },
    methods: {
        async showModal(data = null){
            await this.getEmpApplicationsEA(this.oUser.id);
            if(data != null){
                this.comments = data[this.indexes.comments];
                this.idRequest = data[this.indexes.id];
                this.status = data[this.indexes.status];
                this.take_holidays = parseInt(data[this.indexes.take_holidays]);
                this.take_rest_days = parseInt(data[this.indexes.take_rest_days]);
                $('#date-range200').val(moment(data[this.indexes.start_date], 'ddd DD-MMM-YYYY').format('YYYY-MM-DD')).trigger('change');
			    $('#date-range201').val(moment(data[this.indexes.end_date], 'ddd DD-MMM-YYYY').format('YYYY-MM-DD')).trigger('change');
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
                $('#two-inputs').data('dateRangePicker').redraw();
            }
            $('#modal_solicitud').modal('show');
        },

        getDataDays(){
            var result = this.vacationUtils.getTakedDays(
                            this.lHolidays,
                            this.oUser.payment_frec_id,
                            moment(this.startDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                            moment(this.endDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                            this.oData.const,
                            this.take_rest_days,
                            this.take_holidays
                        );

            this.returnDate = this.oDateUtils.formatDate(result[0], 'ddd DD-MMM-YYYY');
            this.takedDays = result[1];
            this.lDays = result[2];
        },

        formatDate(sDate){
            return moment(sDate).format('ddd DD-MM-YYYY');
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
                'startDate': moment(this.startDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                'endDate': moment(this.endDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                'comments': this.comments,
                'takedDays': this.takedDays,
                // 'lDays': this.lDays,
                'take_holidays': this.take_holidays,
                'take_rest_days': this.take_rest_days,
                'returnDate': moment(this.returnDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                'tot_calendar_days': (moment(this.endDate, 'ddd DD-MMM-YYYY').diff(moment(this.startDate, 'ddd DD-MMM-YYYY'), 'days') + 1)
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    this.actual_vac_days = data.oUser.actual_vac_days;
                    // this.prop_vac_days = data.oUser.prop_vac_days,
                    this.prox_vac_days = data.oUser.prox_vac_days;
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
                    // this.oCopyUser = data.oUser;
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
                    this.actual_vac_days = data.oUser.actual_vac_days;
                    // this.prop_vac_days = data.oUser.prop_vac_days,
                    this.prox_vac_days = data.oUser.prox_vac_days;
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
                        rec.emp_comments_n,
                        this.oDateUtils.formatDate(rec.created_at, 'ddd DD-MMM-YYYY'),
                        rec.folio_n,
                        ((rec.request_status_id == this.oData.const.APPLICATION_APROBADO) ?
                            this.oDateUtils.formatDate(rec.approved_date_n, 'ddd DD-MMM-YYYY') :
                                ((rec.request_status_id == this.oData.const.APPLICATION_RECHAZADO) ?
                                    this.oDateUtils.formatDate(rec.approved_date_n, 'ddd DD-MMM-YYYY') :
                                        '')),
                        this.oDateUtils.formatDate(rec.start_date, 'ddd DD-MMM-YYYY'),
                        this.oDateUtils.formatDate(rec.end_date, 'ddd DD-MMM-YYYY'),
                        this.oDateUtils.formatDate(rec.return_date, 'ddd DD-MMM-YYYY'),
                        rec.total_days,
                        rec.applications_st_name,
                        rec.sup_comments_n,
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
                        this.oDateUtils.formatDate(vac.date_start) + ' a ' + this.oDateUtils.formatDate(vac.date_end),
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
                html: '<b>Inicio:</b> ' + data[this.indexes.start_date] + '<br>' + '<b>Fin:</b> ' +  data[this.indexes.end_date],
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
                html: '<b>Inicio:</b> ' + data[this.indexes.start_date] + '<br>' + '<b>Fin:</b> ' +  data[this.indexes.end_date],
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.startDate = moment(data[this.indexes.start_date], 'ddd DD-MMM-YYYY');
                    this.endDate = moment(data[this.indexes.end_date], 'ddd DD-MMM-YYYY');
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
                    this.actual_vac_days = data.oUser.actual_vac_days;
                    // this.prop_vac_days = data.oUser.prop_vac_days,
                    this.prox_vac_days = data.oUser.prox_vac_days;
                    SGui.showMessage('', data.message, data.icon);
                    this.oCopyUser = data.oUser;
                    this.reDrawVacationsTable(data);
                    this.reDrawRequestTable(data.oUser);
                    this.checkMail(data.mail_log_id, this.oData.checkMailRoute);
                    // (async () => {
                    //     console.log(data);
                    //   })();
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
            for(let rec of this.oCopyUser.applications){
                if(rec.request_status_id == 1){
                    return true;
                }
            }

            return false;
        },

        async checkMail(mail_log_id, route){
            var checked = false;
            for(var i = 0; i<10; i++){
                await this.sleep(3000);

                if(!checked){
                    axios.post(route, {
                        'mail_log_id': mail_log_id,
                    })
                    .then(response => {
                        var data = response.data;
                        if(data.status == 2){
                            checked = true;
                            SGui.showMessage('', 'E-mail enviado con éxito', 'success');
                        }else if(data.status == 3){
                            checked = true;
                            SGui.showMessage('', 'Ocurrio un error al enviar el e-mail, notifique a su supervisor', 'error');
                        }
                    })
                    .catch(function(error) {
                        console.log(error);
                    });
                }

                if(checked){
                    break;
                }
            }
        },

        sleep(milliseconds) {
            return new Promise((resolve) => setTimeout(resolve, milliseconds));
        },

        getEmpApplicationsEA(user_id){
            SGui.showWaiting(3000);
            return new Promise((resolve) => 
            axios.post(this.oData.applicationsEARoute, {
                'user_id':  user_id
            })
            .then(response => {
                let data = response.data;
                if(data.success){
                    dateRangePickerArrayApplications = data.arrAplications;
                    this.applicationsEA = data.arrAplications;
                    swal.close()
                    resolve(dateRangePickerArrayApplications);
                }else{
                    SGui.showMessage('', data.message, data.icon);
                    swal.close()
                    resolve(null);
                }
            })
            .catch( function (error){
                console.log(error);
                swal.close()
                resolve(error);
            }));
        }
    },
})
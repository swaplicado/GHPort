var app = new Vue({
    el: '#requestVacations',
    data: {
        oData: oServerData,
        oDateUtils: new SDateUtils(),
        indexes: oServerData.indexes,
        lEmployees: oServerData.lEmployees,
        year: oServerData.year,
        lHolidays: oServerData.lHolidays,
        idRequest: null,
        idUser: null,
        comments: null,
        status: null,
        takedDays: 0,
        lDays: [],
        startDate: null,
        endDate: null,
        returnDate: null,
        isApprove: false,
        idApplication: oServerData.idApplication,
        take_rest_days: false,
        take_holidays: false,
        vacationUtils: new vacationUtils(),
        applicationsEA: []
    },
    mounted(){
        
    },
    methods: {
        formatDate(sDate){
            return moment(sDate).format('ddd DD-MM-YYYY');
        },

        getReturnDate(data){
            var result = this.vacationUtils.getTakedDays(
                this.lHolidays,
                data[this.indexes.payment_frec_id],
                moment(this.startDate, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD'),
                moment(this.endDate, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD'),
                this.oData.const,
                this.take_rest_days,
                this.take_holidays
            );

            this.returnDate = this.oDateUtils.formatDate(result[0]);
            this.takedDays = result[1];
            this.lDays = result[2];
        },

        async showAcceptRegistry(data){
            if(parseInt(data[this.indexes.request_status_id]) != this.oData.const.APPLICATION_ENVIADO){
                SGui.showMessage('', 'Solo se pueden aprobar solicitudes nuevas', 'warning');
                return;
            }
            await this.getEmpApplicationsEA(data[this.indexes.user_id]);
            this.comments = data[this.indexes.sup_comments];
            this.idRequest = data[this.indexes.id];
            this.idUser = data[this.indexes.user_id];
            this.status = data[this.indexes.applications_st_name];
            this.startDate = data[this.indexes.start_date];
            this.endDate = data[this.indexes.end_date];
            this.takedDays = data[this.indexes.total_days];
            this.isApprove = true;
            this.take_holidays = parseInt(data[this.indexes.take_holidays]);
            this.take_rest_days = parseInt(data[this.indexes.take_rest_days]);
            $('#date-range200').val(moment(data[this.indexes.start_date], 'ddd DD-MMM-YYYY').format('YYYY-MM-DD')).trigger('change');
            $('#date-range201').val(moment(data[this.indexes.end_date], 'ddd DD-MMM-YYYY').format('YYYY-MM-DD')).trigger('change');
            this.getReturnDate(data);

            $('#modal_solicitud').modal('show');
        },

        async showRejectRegistry(data){
            if(parseInt(data[this.indexes.request_status_id]) != this.oData.const.APPLICATION_ENVIADO){
                SGui.showMessage('', 'Solo se pueden rechazar solicitudes nuevas', 'warning');
                return;
            }
            await this.getEmpApplicationsEA(data[this.indexes.user_id]);
            this.comments = data[this.indexes.sup_comments];
            this.idRequest = data[this.indexes.id];
            this.idUser = data[this.indexes.user_id];
            this.status = data[this.indexes.applications_st_name];
            this.startDate = data[this.indexes.start_date];
            this.endDate = data[this.indexes.end_date];
            this.takedDays = data[this.indexes.total_days];
            this.isApprove = false;
            this.take_holidays = parseInt(data[this.indexes.take_holidays]);
            this.take_rest_days = parseInt(data[this.indexes.take_rest_days]);
            $('#date-range200').val(moment(data[this.indexes.start_date], 'ddd DD-MMM-YYYY').format('YYYY-MM-DD')).trigger('change');
            $('#date-range201').val(moment(data[this.indexes.end_date], 'ddd DD-MMM-YYYY').format('YYYY-MM-DD')).trigger('change');
            this.getReturnDate(data);

            $('#modal_solicitud').modal('show');
        },

        acceptRequest(){
            SGui.showWaiting(10000);
            var route = this.oData.acceptRequestRoute;
            axios.post(route, {
                'id_application': this.idRequest,
                'id_user': this.idUser,
                'comments': this.comments,
                'year': this.year,
                'lDays': this.lDays,
                'returnDate': moment(this.returnDate, 'ddd DD-MM-YYYY').format('YYYY-MM-DD')
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    $('#modal_solicitud').modal('hide');
                    SGui.showMessage('', data.message, data.icon);
                    this.reDrawRequestTable(data.lEmployees);
                    table['table_requestVac'].$('tr.selected').removeClass('selected');
                    this.checkMail(data.mail_log_id, this.oData.checkMailRoute);
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function(error) {
                console.log(error);
                SGui.showError(error);
            });
        },

        rejectRequest(){
            SGui.showWaiting(10000);
            var route = this.oData.rejectRequestRoute;
            axios.post(route, {
                'id_application': this.idRequest,
                'id_user': this.idUser,
                'comments': this.comments,
                'year': this.year,
                'lDays': this.lDays,
                'returnDate': moment(this.returnDate, 'ddd DD-MM-YYYY').format('YYYY-MM-DD')
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    $('#modal_solicitud').modal('hide');
                    SGui.showMessage('', data.message, data.icon);
                    this.reDrawRequestTable(data.lEmployees);
                    table['table_requestVac'].$('tr.selected').removeClass('selected');
                    this.checkMail(data.mail_log_id, this.oData.checkMailRoute);
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
            for(var emp of data){
                for(rec of emp.applications){
                    dataReq.push(
                        [
                            rec.id_application,
                            rec.user_id,
                            emp.payment_frec_id,
                            rec.request_status_id,
                            rec.take_holidays,
                            rec.take_rest_days,
                            rec.sup_comments_n,
                            rec.user_apr_rej_id,
                            rec.folio_n,
                            rec.user_apr_rej_name,
                            emp.employee,
                            this.oDateUtils.formatDate(rec.created_at, 'ddd DD-MMM-YYYY'),
                            ((rec.request_status_id == this.oData.const.APPLICATION_APROBADO) ?
                                this.oDateUtils.formatDate(rec.approved_date_n, 'ddd DD-MMM-YYYY') :
                                ((rec.request_status_id == this.oData.const.APPLICATION_RECHAZADO) ?
                                    this.oDateUtils.formatDate(rec.updated_at, 'ddd DD-MMM-YYYY') :
                                    '')),
                            this.oDateUtils.formatDate(rec.start_date, 'ddd DD-MMM-YYYY'),
                            this.oDateUtils.formatDate(rec.end_date, 'ddd DD-MMM-YYYY'),
                            this.oDateUtils.formatDate(rec.returnDate, 'ddd DD-MMM-YYYY'),
                            rec.total_days,
                            rec.request_status_id == 2 ? 'NUEVO' : rec.applications_st_name,
                            rec.emp_comments_n
                        ]
                    );
                }
            }
            table['table_requestVac'].clear().draw();
            table['table_requestVac'].rows.add(dataReq).draw();
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
                    this.reDrawRequestTable(data.lEmployees);
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function(error) {
                console.log(error);
                SGui.showError(error);
            });
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
                            SGui.showMessage('', 'Ocurrio un error al enviar el e-mail, notifique a su colaborador', 'error');
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
                        this.applicationsEA = data.arrAplications;
                        dateRangePickerArrayApplications = data.arrAplications;
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
                })
            );
        }
    },
})
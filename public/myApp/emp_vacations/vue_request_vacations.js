var app = new Vue({
    el: '#requestVacations',
    data: {
        oData: oServerData,
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
    },
    mounted(){
        
    },
    methods: {
        formatDate(sDate){
            return moment(sDate).format('YYYY-MM-DD');
        },

        getReturnDate(data){
            var result = this.vacationUtils.getTakedDays(
                this.lHolidays,
                data[this.indexes.payment_frec_id],
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

        showAcceptRegistry(data){
            if(parseInt(data[this.indexes.request_status_id]) != this.oData.const.APPLICATION_ENVIADO){
                SGui.showMessage('', 'Solo se pueden aprobar solicitudes nuevas', 'warning');
                return;
            }
            this.comments = data[this.indexes.comments];
            this.idRequest = data[this.indexes.id];
            this.idUser = data[this.indexes.user_id];
            this.status = data[this.indexes.applications_st_name];
            this.startDate = data[this.indexes.start_date];
            this.endDate = data[this.indexes.end_date];
            this.takedDays = data[this.indexes.total_days];
            this.isApprove = true;
            this.take_holidays = parseInt(data[this.indexes.take_holidays]);
            this.take_rest_days = parseInt(data[this.indexes.take_rest_days]);
            $('#date-range200').val(data[this.indexes.start_date]).trigger('change');
            $('#date-range201').val(data[this.indexes.end_date]).trigger('change');
            this.getReturnDate(data);

            $('#modal_solicitud').modal('show');
        },

        showRejectRegistry(data){
            if(parseInt(data[this.indexes.request_status_id]) != this.oData.const.APPLICATION_ENVIADO && parseInt(data[this.indexes.request_status_id]) != this.oData.const.APPLICATION_APROBADO){
                SGui.showMessage('', 'Solo se pueden rechazar solicitudes nuevas o aprobadas', 'warning');
                return;
            }
            this.comments = data[this.indexes.comments];
            this.idRequest = data[this.indexes.id];
            this.idUser = data[this.indexes.user_id];
            this.status = data[this.indexes.applications_st_name];
            this.startDate = data[this.indexes.start_date];
            this.endDate = data[this.indexes.end_date];
            this.takedDays = data[this.indexes.total_days];
            this.isApprove = false;
            this.take_holidays = parseInt(data[this.indexes.take_holidays]);
            this.take_rest_days = parseInt(data[this.indexes.take_rest_days]);
            $('#date-range200').val(data[this.indexes.start_date]).trigger('change');
            $('#date-range201').val(data[this.indexes.end_date]).trigger('change');
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
                'returnDate': this.returnDate
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    $('#modal_solicitud').modal('hide');
                    SGui.showMessage('', data.message, data.icon);
                    this.reDrawRequestTable(data.lEmployees);
                    table['table_requestVac'].$('tr.selected').removeClass('selected');
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
                'returnDate': this.returnDate
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    $('#modal_solicitud').modal('hide');
                    SGui.showMessage('', data.message, data.icon);
                    this.reDrawRequestTable(data.lEmployees);
                    table['table_requestVac'].$('tr.selected').removeClass('selected');
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
                            emp.employee,
                            this.formatDate(rec.created_at),
                            ((rec.request_status_id == this.oData.const.APPLICATION_APROBADO) ?
                                rec.approved_date_n :
                                ((rec.request_status_id == this.oData.const.APPLICATION_RECHAZADO) ?
                                this.formatDate(rec.updated_at) :
                                    '')),
                            rec.start_date,
                            rec.end_date,
                            rec.returnDate,
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
    },
})
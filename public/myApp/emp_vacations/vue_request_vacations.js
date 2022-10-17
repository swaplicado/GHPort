var app = new Vue({
    el: '#requestVacations',
    data: {
        oData: oServerData,
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
    },
    mounted(){
        
    },
    methods: {
        formatDate(sDate){
            return moment(sDate).format('YYYY-MM-DD');
        },

        getReturnDate(data){
            this.lDays = [];
            var diffDays = moment(this.endDate).diff(moment(this.startDate), 'days');
            var oDate = moment(this.startDate);
            this.returnDate = moment(this.endDate).add('1', 'days').format('YYYY-MM-DD');
            if(data[2] == this.oData.const.SEMANA){
                for(var i = 0; i < 31; i++){
                    switch (moment(this.returnDate).weekday()) {
                        case 0:
                            this.returnDate = moment(this.returnDate).add('1', 'days').format('YYYY-MM-DD');
                            break;
                        default:
                            break;
                    }
    
                    if(!this.lHolidays.includes(this.returnDate)){
                        break;
                    }else{
                        this.returnDate = moment(this.returnDate).add('1', 'days').format('YYYY-MM-DD');
                    }
                }
    
                for (let i = 0; i <= diffDays; i++) {
                    if(oDate.weekday() != 0 && !this.lHolidays.includes(oDate.format('YYYY-MM-DD'))){
                        this.lDays.push(oDate.format('YYYY-MM-DD'));
                    }
                    oDate.add('1', 'days');
                }
            }else{
                for(var i = 0; i < 31; i++){
                    switch (moment(this.returnDate).weekday()) {
                        case 6:
                            this.returnDate = moment(this.returnDate).add('2', 'days').format('YYYY-MM-DD');
                            break;
                        case 0:
                            this.returnDate = moment(this.returnDate).add('1', 'days').format('YYYY-MM-DD');
                            break;
                        default:
                            break;
                    }

                    if(!this.lHolidays.includes(this.returnDate)){
                        break;
                    }else{
                        this.returnDate = moment(this.returnDate).add('1', 'days').format('YYYY-MM-DD');
                    }
                }

                for (let i = 0; i <= diffDays; i++) {
                    if(oDate.weekday() != 0 && oDate.weekday() != 6 && !this.lHolidays.includes(oDate.format('YYYY-MM-DD'))){
                        this.lDays.push(oDate.format('YYYY-MM-DD'));
                    }
                    oDate.add('1', 'days');
                }
            }
        },

        showAcceptRegistry(data){
            if(parseInt(data[5]) != this.oData.const.APPLICATION_ENVIADO){
                SGui.showMessage('', 'Solo se pueden aprobar solicitudes nuevas', 'warning');
                return;
            }
            this.comments = data[12];
            this.idRequest = data[0];
            this.idUser = data[1];
            this.status = data[11];
            this.startDate = data[3];
            this.endDate = data[4];
            this.takedDays = data[10];
            this.isApprove = true;
            
            this.getReturnDate(data);

            $('#modal_solicitud').modal('show');
        },

        showRejectRegistry(data){
            if(parseInt(data[5]) != this.oData.const.APPLICATION_ENVIADO && parseInt(data[5]) != this.oData.const.APPLICATION_APROBADO){
                SGui.showMessage('', 'Solo se pueden rechazar solicitudes nuevas o aprobadas', 'warning');
                return;
            }
            this.comments = data[12];
            this.idRequest = data[0];
            this.idUser = data[1];
            this.status = data[11];
            this.startDate = data[3];
            this.endDate = data[4];
            this.takedDays = data[10];
            this.isApprove = false;

            this.getReturnDate(data);

            $('#modal_solicitud').modal('show');
        },

        acceptRequest(){
            SGui.showWaiting(5000);
            var route = this.oData.acceptRequestRoute;
            axios.post(route, {
                'id_application': this.idRequest,
                'id_user': this.idUser,
                'comments': this.comments,
                'year': this.year
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    $('#modal_solicitud').modal('hide');
                    SGui.showOk();
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
            SGui.showWaiting(5000);
            var route = this.oData.rejectRequestRoute;
            axios.post(route, {
                'id_application': this.idRequest,
                'id_user': this.idUser,
                'comments': this.comments,
                'year': this.year
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    $('#modal_solicitud').modal('hide');
                    SGui.showOk();
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
                            rec.start_date,
                            rec.end_date,
                            rec.request_status_id,
                            emp.employee,
                            this.formatDate(rec.created_at),
                            ((rec.request_status_id == this.oData.const.APPLICATION_APROBADO) ?
                                rec.approved_date_n :
                                ((rec.request_status_id == this.oData.const.APPLICATION_RECHAZADO) ?
                                this.formatDate(rec.updated_at) :
                                    '')),
                            rec.start_date + ' a ' + rec.end_date,
                            rec.total_days,
                            rec.request_status_id == 2 ? 'NUEVO' : rec.applications_st_name,
                            rec.sup_comments_n
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
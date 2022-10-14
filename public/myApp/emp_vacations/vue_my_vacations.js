var app = new Vue({
    el: '#myVacations',
    data: {
        oData: oServerData,
        oUser: oServerData.oUser,
        lHolidays: oServerData.lHolidays,
        startDate: null,
        endDate: null,
        returnDate: null,
        comments: null,
        idRequest: null,
        takedDays: 0,
        lDays: [],
        lRec: [],
        year: oServerData.year,
        actual_vac_days: oServerData.oUser.actual_vac_days,
        prop_vac_days: oServerData.oUser.prop_vac_days,
        prox_vac_days: oServerData.oUser.prox_vac_days
    },
    mounted(){
        
    },
    methods: {
        showModal(data = null){
            if(data != null){
                this.comments = data[8];
                this.idRequest = data[0];
                $('#date-range200').val(data[1]).trigger('change');
			    $('#date-range201').val(data[2]).trigger('change');
            }else{
                this.startDate = null;
                this.endDate = null;
                this.returnDate = null;
                this.comments = null;
                this.idRequest = null;
                this.takedDays = 0;
                this.lDays = [];
                $('#clear').trigger('click');
            }
            $('#modal_solicitud').modal('show');
        },

        getDataDays(){
            this.getTakedDays();
        },

        getTakedDays(){
            this.takedDays = 0;
            this.lDays = [];
            var diffDays = moment(this.endDate).diff(moment(this.startDate), 'days');
            var oDate = moment(this.startDate);
            if(this.oUser.payment_frec_id == 1){
                if(this.startDate != null && this.startDate != ''){
                    for(var i = 0; i < 31;  i++){
                        switch (moment(this.startDate).weekday()) {
                            case 0:
                                this.startDate = moment(this.startDate).add('1', 'days').format('YYYY-MM-DD');
                                break;
                            default:
                                break;
                        }

                        if(!this.lHolidays.includes(this.startDate)){
                            break;
                        }else{
                            this.startDate = moment(this.startDate).add('1', 'days').format('YYYY-MM-DD');
                        }
                    }
                }

                if(this.endDate != null && this.endDate != ''){
                    this.returnDate = moment(this.endDate).add('1', 'days').format('YYYY-MM-DD');
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

                    for(var i = 0; i < 31; i++){
                        switch (moment(this.endDate).weekday()) {
                            case 0:
                                this.endDate = moment(this.endDate).subtract('1', 'days').format('YYYY-MM-DD');
                                break;
                            default:
                                break;
                        }

                        if(!this.lHolidays.includes(this.endDate)){
                            break;
                        }else{
                            this.endDate = moment(this.endDate).subtract('1', 'days').format('YYYY-MM-DD');
                        }
                    }
                }

                for (let i = 0; i <= diffDays; i++) {
                    if(oDate.weekday() != 0 && !this.lHolidays.includes(oDate.format('YYYY-MM-DD'))){
                        this.takedDays = this.takedDays + 1;
                        this.lDays.push(oDate.format('YYYY-MM-DD'));
                    }
                    oDate.add('1', 'days');
                }
            }else{
                if(this.startDate != null && this.startDate != ''){
                    for(var i = 0; i < 31; i++){
                        switch (moment(this.startDate).weekday()) {
                            case 6:
                                this.startDate = moment(this.startDate).add('2', 'days').format('YYYY-MM-DD');
                                break;
                            case 0:
                                this.startDate = moment(this.startDate).add('1', 'days').format('YYYY-MM-DD');
                                break;
                            default:
                                break;
                        }

                        if(!this.lHolidays.includes(this.startDate)){
                            break;
                        }else{
                            this.startDate = moment(this.startDate).add('1', 'days').format('YYYY-MM-DD');
                        }
                    }
                }

                if(this.endDate != null && this.endDate != ''){
                    this.returnDate = moment(this.endDate).add('1', 'days').format('YYYY-MM-DD');
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

                    for(var i = 0; i < 31; i++){
                        switch (moment(this.endDate).weekday()) {
                            case 6:
                                this.endDate = moment(this.endDate).subtract('1', 'days').format('YYYY-MM-DD');
                                break;
                            case 0:
                                this.endDate = moment(this.endDate).subtract('2', 'days').format('YYYY-MM-DD');
                                break;
                            default:
                                break;
                        }
                        if(!this.lHolidays.includes(this.endDate)){
                            break;
                        }else{
                            this.endDate = moment(this.endDate).subtract('1', 'days').format('YYYY-MM-DD');
                        }
                    }
                }

                for (let i = 0; i <= diffDays; i++) {
                    if(oDate.weekday() != 0 && oDate.weekday() != 6 && !this.lHolidays.includes(oDate.format('YYYY-MM-DD'))){
                        this.takedDays = this.takedDays + 1;
                        this.lDays.push(oDate.format('YYYY-MM-DD'));
                    }
                    oDate.add('1', 'days');
                }
            }
        },

        formatDate(sDate){
            return moment(sDate).format('YYYY-MM-DD');
        },

        requestVac(){
            SGui.showWaiting(5000);
            if(this.idRequest == null){
                var route = this.oData.requestVacRoute;
            }else{
                var route = this.oData.updateRequestVacRoute;
            }
            axios.post(route, {
                'id_application': this.idRequest,
                'startDate': this.startDate,
                'endDate': this.endDate,
                'comments': this.comments,
                'takedDays': this.takedDays,
                'lDays': this.lDays,
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    this.actual_vac_days = data.oUser.actual_vac_days,
                    this.prop_vac_days = data.oUser.prop_vac_days,
                    this.prox_vac_days = data.oUser.prox_vac_days
                    $('#modal_solicitud').modal('hide');
                    SGui.showOk();
                    // this.oUser = data.oUser;
                    
                    this.reDrawVacationsTable(data);
                    this.reDrawRequestTable(data.oUser);
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function(error) {
                console.log(error);
                SGui.showMessage('', data.message, data.icon);
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
                    this.reDrawRequestTable(data);
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function(error) {
                console.log(error);
                SGui.showMessage('', data.message, data.icon);
            });
        },

        deleteRequest(request_id){
            SGui.showWaiting(5000);
            axios.post(this.oData.deleteRequestRoute, {
                'id_application': request_id,
                'year': this.year,
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    this.actual_vac_days = data.oUser.actual_vac_days,
                    this.prop_vac_days = data.oUser.prop_vac_days,
                    this.prox_vac_days = data.oUser.prox_vac_days
                    SGui.showOk();
                    this.reDrawVacationsTable(data);
                    this.reDrawRequestTable(data.oUser);
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function(error) {
                console.log(error);
                SGui.showMessage('', data.message, data.icon);
            });
        },

        reDrawRequestTable(data){
            var dataReq = [];
            for(let rec of data.applications){
                dataReq.push(
                    [
                        rec.id_application,
                        rec.start_date,
                        rec.end_date,
                        this.formatDate(rec.created_at),
                        ((rec.request_status_id == 3) ?
                            rec.approved_date_n :
                                ((rec.request_status_id == 4) ?
                                    rec.approved_date_n :
                                        '')),
                        rec.start_date + ' a ' + rec.end_date,
                        rec.total_days,
                        rec.applications_st_name,
                        rec.sup_comments_n
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
            Swal.fire({
                title: '¿Desea eliminar la solicitud para las fechas?',
                text: data[5],
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deleteRequest(data[0]);
                }
            })
        }
    },
})
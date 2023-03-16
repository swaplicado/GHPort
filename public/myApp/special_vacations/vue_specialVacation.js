var app = new Vue({
    el: '#specialVacations',
    data: {
        oData: oServerData,
        oUser: oServerData.oUser,
        oCopyUser: oServerData.oUser,
        initialCalendarDate: oServerData.initialCalendarDate,
        lHolidays: oServerData.lHolidays,
        lTemp: oServerData.lTemp,
        year: oServerData.year,
        startDate: null,
        endDate: null,
        returnDate: null,
        comments: null,
        idRequest: null,
        status: null,
        takedDays: 0,
        lDays: [],
        applicationsEA: [],
        oDateUtils: new SDateUtils(),
        vacationUtils: new vacationUtils(),
        actual_vac_days: null,
        prop_vac_days: null,
        prox_vac_days: null,
        take_rest_days: false,
        take_holidays: false,
        valid: true,
        totCalendarDays: 0,
        isNewApplication: false,
        actual_vac_days: oServerData.oUser.actual_vac_days,
        prop_vac_days: oServerData.oUser.prop_vac_days,
        prox_vac_days: oServerData.oUser.prox_vac_days,
        indexes: oServerData.indexesSpecialRequestTable,
    },
    mounted(){

    },
    methods: {
        async showModal(data = null){
            await this.getEmpApplicationsEA(this.oUser.id);
            if(data != null){
                this.isNewApplication = false;
                this.valid = (data[this.indexes.request_status_id] == this.oData.const.APPLICATION_ENVIADO || 
                                data[this.indexes.request_status_id] == this.oData.const.APPLICATION_APROBADO ||
                                    data[this.indexes.request_status_id] == this.oData.const.APPLICATION_RECHAZADO) ?
                                        false :
                                            true;
                dateRangePickerValid = this.valid;
                if(!this.valid){
                    SGui.showMessage('', 'No se puede editar una solicitud con estatus: '+data[this.indexes.status], 'warning');
                }
                $('#clear').trigger('click');
                $('#two-inputs-myRequest').data('dateRangePicker').redraw();
                this.isNewApplication = false;
                this.comments = data[this.indexes.comments];
                this.idRequest = data[this.indexes.id];
                this.status = data[this.indexes.status];
                this.take_holidays = parseInt(data[this.indexes.take_holidays]);
                this.take_rest_days = parseInt(data[this.indexes.take_rest_days]);
                $('#date-range200-myRequest').val(moment(data[this.indexes.start_date], 'ddd DD-MMM-YYYY').format('YYYY-MM-DD')).trigger('change');
                $('#date-range201-myRequest').val(moment(data[this.indexes.end_date], 'ddd DD-MMM-YYYY').format('YYYY-MM-DD')).trigger('change');
                this.isNewApplication = true;
            }else{
                this.isNewApplication = true;
                if(this.HasRequestCreated()){
                    SGui.showMessage('', 'No puede crear otra solicitud de vacaciones si tiene solicitudes creadas pendientes de enviar', 'warning');
                    return;
                }
                this.isNewApplication = true;
                this.startDate = null;
                this.endDate = null;
                this.returnDate = null;
                this.comments = null;
                this.idRequest = null;
                this.takedDays = 0;
                this.totCalendarDays = 0;
                this.lDays = [];
                this.status = null;
                this.take_rest_days = false;
                this.take_holidays = false;
                this.valid = true;
                dateRangePickerValid = this.valid;
                $('#clear').trigger('click');
                $('#two-inputs-myRequest').data('dateRangePicker').redraw();
            }
            $('#modal_specialVac').modal('show');
        },

        setTakedDay(index, checkbox_id){
            let checked = $('#' + checkbox_id).is(":checked");
            this.lDays[index].taked = checked;
            checked ? this.takedDays++ : this.takedDays--;
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
            this.totCalendarDays = result[3];
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
                'take_holidays': this.take_holidays,
                'take_rest_days': this.take_rest_days,
                'returnDate': moment(this.returnDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                'tot_calendar_days': this.totCalendarDays,
                'employee_id': this.oUser.id,
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    this.actual_vac_days = data.oUser.actual_vac_days;
                    this.prox_vac_days = data.oUser.prox_vac_days;
                    $('#modal_specialVac').modal('hide');
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
                        rec.user_apr_rej_id,
                        rec.folio_n,
                        this.oDateUtils.formatDate(rec.created_at, 'ddd DD-MMM-YYYY'),
                        rec.user_apr_rej_name,
                        ((rec.request_status_id == this.oData.const.APPLICATION_APROBADO) ?
                            this.oDateUtils.formatDate(rec.approved_date_n, 'ddd DD-MMM-YYYY') :
                                ((rec.request_status_id == this.oData.const.APPLICATION_RECHAZADO) ?
                                    this.oDateUtils.formatDate(rec.rejected_date_n, 'ddd DD-MMM-YYYY') :
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
            var footer = [];
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
            footer =
                [
                    '',
                    'Total',
                    data.oUser.tot_vacation_days,
                    data.oUser.tot_vacation_taken,
                    data.oUser.tot_vacation_expired,
                    data.oUser.tot_vacation_request,
                    data.oUser.tot_vacation_remaining
                ];

            table['vacationsTable'].clear().draw();
            document.getElementById('vacationsTable').deleteTFoot();
            table['vacationsTable'].rows.add(dataVac).draw();
            ofoot = document.getElementById('vacationsTable').createTFoot();
            var row = ofoot.insertRow(0);
            var count = 0;
            for(var fo of footer){
                let cell = row.insertCell(count);
                if(fo == 'Total'){
                    cell.classList.add('myTdHead');
                }
                cell.innerHTML = fo;
                count++;
            }
        },

        checkSelectDates(){
            if(this.isNewApplication){
                for(let appEA of this.applicationsEA){
                    if(moment(appEA, 'YYYY-MM-DD').isBetween(moment(this.startDate, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD'), moment(this.endDate, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD'), undefined, '[]')){
                        $('#clear').trigger('click');
                        SGui.showMessage('', 'Ya existe una solicitud de vacaciones para el dia: \n' + this.oDateUtils.formatDate(appEA, 'ddd DD-MMM-YYYY'), 'warning');
                        break;
                    }
                }
            }
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
                    dateRangePickerArraySpecialSeasons = data.arrSpecialSeasons;
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
        },

        HasRequestCreated(){
            for(let rec of this.oCopyUser.applications){
                if(rec.request_status_id == 1){
                    return true;
                }
            }

            return false;
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

        deleteRequest(request_id){
            SGui.showWaiting(15000);
            axios.post(this.oData.deleteRequestRoute, {
                'id_application': request_id,
                'year': this.year,
                'employee_id': this.oUser.id,
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    this.actual_vac_days = data.oUser.actual_vac_days;
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

        filterYear(){
            SGui.showWaiting(5000);
            axios.post(this.oData.myVacations_filterYearRoute, {
                'year': this.year,
                'employee_id': this.oUser.id,
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
                SGui.showError(error);
            });
        },

        sendRegistry(data){
            if(data[this.indexes.status] != 'CREADO'){
                SGui.showMessage('','Solo se pueden enviar solicitudes con el estatus CREADO', 'warning');
                return
            }
            Swal.fire({
                title: '¿Desea enviar la solicitud para las fechas?',
                html: '<b>Inicio:</b> ' +
                        data[this.indexes.start_date] +
                        '<br>' +
                        '<b>Fin:</b> ' +
                        data[this.indexes.end_date] +
                        '<br>' +
                        '<br>' +
                        '<h4><b>La solicitud se aprobará automáticamente</b></h4>',
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
                'returnDate': this.returnDate,
                'employee_id': this.oUser.id,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.actual_vac_days = data.oUser.actual_vac_days;
                    this.prox_vac_days = data.oUser.prox_vac_days;
                    SGui.showMessage('', data.message, data.icon);
                    this.oCopyUser = data.oUser;
                    this.reDrawVacationsTable(data);
                    this.reDrawRequestTable(data.oUser);
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(error){
                consol.log(error);
                SGui.showError(error);
            })
        }
    }
});
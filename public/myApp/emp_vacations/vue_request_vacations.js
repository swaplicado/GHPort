var appRequestVacation = new Vue({
    el: '#requestVacations',
    data: {
        oData: oServerData,
        oDateUtils: new SDateUtils(),
        oUsersUtils: new SUsersUtils(),
        oUser: null,
        indexes: oServerData.indexesRequest,
        myManagers: oServerData.myManagers,
        selectedmanager: null,
        lEmployees: oServerData.lEmployees,
        year: oServerData.year,
        lHolidays: oServerData.lHolidays,
        idRequest: null,
        idUser: null,
        comments: null,
        status: null,
        takedDays: 0,
        lDays: [],
        lDaysConventionalFormat: [],
        startDate: null,
        endDate: null,
        returnDate: null,
        isApprove: false,
        idApplication: oServerData.idApplication,
        take_rest_days: false,
        take_holidays: false,
        vacationUtils: new vacationUtils(),
        applicationsEA: [],
        totCalendarDays: 0,
        originalDaysTaked: 0,
        lNoBussinesDay: [],
        noBussinesDayIndex: 0,
        lTemp: [],
        emp_comments: null,
        rqStatus: 0,
        oApplication: oServerData.oApplication,
        isFromMail: false,
        MyReturnDate: null,
        showDatePickerSimple: false,
        lTypes: [],
    },
    computed: {
        propertyAAndPropertyB() {
            return `${this.endDate}|${this.takedDays}`;
        },
    },
    watch: {
        propertyAAndPropertyB(newVal, oldVal) {
            // this.newData = true;
            let oldlTypes = structuredClone(this.lTypes);
            this.lTypes = [];
            if(this.endDate != null && this.endDate != undefined && this.endDate != ""){
                let res = this.checkSpecial(this.oApplication);
                if(res[0] && !this.arraysEqual(this.lTypes, oldlTypes)){
                    SGui.showMessage('', res[1], 'warning');
                }
            }
        },
    },
    mounted(){
        var self = this;
        var dataMyManagers = [{id: '', text: ''}];
        for (let i = 0; i < this.myManagers.length; i++) {
            dataMyManagers.push({id: this.myManagers[i].id, text: this.myManagers[i].full_name_ui });
        }

        $('#selManager')
            .select2({
                placeholder: 'selecciona',
                data: dataMyManagers,
            });

            // table['table_requestVac'].rows('.noSelectableRow').deselect();
    },
    methods: {
        arraysEqual(a, b) {
            if (a.length !== b.length) {
                return false;
            }
            return a.every(element => b.includes(element));
        },

        initView(){
            
        },

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

            this.returnDate = this.oDateUtils.formatDate(result[0], 'ddd DD-MMM-YYYY');
            this.takedDays = result[1];
            this.lDays = result[2];
            this.totCalendarDays = result[3];
            this.lNoBussinesDay = result[4];
            this.noBussinesDayIndex = 0;
            this.originalDaysTaked = result[1];
        },

        cancelRegistry(data){
            Swal.fire({
                title: '¿Desea cancelar la solicitud?',
                html:   '<b>Colaborador: </b>' +
                        data[this.indexes.employee] +
                        '<br>' +
                        '<b>Inicio:</b> ' +
                        data[this.indexes.start_date] +
                        '<br>' +
                        '<b>Fin:</b> ' +
                        data[this.indexes.end_date],
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'No',
                confirmButtonText: 'Si'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.cancelRequest(data[this.indexes.id]);
                }
            })
        },

        cancelRequest(application_id){
            SGui.showWaiting(15000);

            let route = this.oData.cancelRequestRoute;
            axios.post(route, {
                'application_id': application_id,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.reDrawRequestTable(data.lEmployees);
                    SGui.showOk();
                    this.checkMail(data.mail_log_id, this.oData.checkMailRoute);
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function(error){
                console.log(error);
                SGui.showError(error);
            });
        },

        async showAcceptRegistry(data){
            SGui.showWaiting(15000);
            this.oApplication = null;
            $('#two-inputs').data('dateRangePicker').clear();
            if(parseInt(data[this.indexes.request_status_id]) != this.oData.const.APPLICATION_ENVIADO){
                SGui.showMessage('', 'Solo se pueden aprobar solicitudes nuevas', 'warning');
                return;
            }
            this.oUser = await this.oUsersUtils.getUserData(this.oData.getUserDataRoute, data[this.indexes.user_id]);
            await this.getEmpApplicationsEA(data[this.indexes.user_id]);
            await this.getlDays(data[this.indexes.id]);
            this.vacationUtils.createClass(this.lTemp);
            this.comments = data[this.indexes.sup_comments];
            this.idRequest = data[this.indexes.id];
            this.idUser = data[this.indexes.user_id];
            birthday = data[this.indexes.birthday];
            aniversaryDay = data[this.indexes.benefits_date];
            this.status = data[this.indexes.applications_st_name];
            this.startDate = data[this.indexes.start_date];
            this.endDate = data[this.indexes.end_date];
            this.takedDays = data[this.indexes.total_days];
            this.emp_comments = data[this.indexes.comments];
            this.isApprove = true;
            this.take_holidays = parseInt(data[this.indexes.take_holidays]);
            this.take_rest_days = parseInt(data[this.indexes.take_rest_days]);
            $('#date-range200').val(moment(data[this.indexes.start_date], 'ddd DD-MMM-YYYY').format('YYYY-MM-DD')).trigger('change');
            $('#date-range201').val(moment(data[this.indexes.end_date], 'ddd DD-MMM-YYYY').format('YYYY-MM-DD')).trigger('change');
            // this.getReturnDate(data);
            this.returnDate = data[this.indexes.return_date];
            this.takedDays = data[this.indexes.total_days];
            this.noBussinesDayIndex = 0;
            this.totCalendarDays = (moment(moment(this.endDate, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD'), 'YYYY-MM-DD').diff(moment(moment(this.startDate, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD'), 'YYYY-MM-DD'), 'days') + 1);
            // this.reMaplDays();
            $('#modal_solicitud').modal('show');
            Swal.close();
        },

        async showRejectRegistry(data){
            SGui.showWaiting(15000);
            this.oApplication = null;
            $('#two-inputs').data('dateRangePicker').clear();
            if(parseInt(data[this.indexes.request_status_id]) != this.oData.const.APPLICATION_ENVIADO){
                SGui.showMessage('', 'Solo se pueden rechazar solicitudes nuevas', 'warning');
                return;
            }
            this.oUser = await this.oUsersUtils.getUserData(this.oData.getUserDataRoute, data[this.indexes.user_id]);
            await this.getEmpApplicationsEA(data[this.indexes.user_id]);
            await this.getlDays(data[this.indexes.id]);
            this.vacationUtils.createClass(this.lTemp);
            this.comments = data[this.indexes.sup_comments];
            this.idRequest = data[this.indexes.id];
            this.idUser = data[this.indexes.user_id];
            birthday = data[this.indexes.birthday];
            aniversaryDay = data[this.indexes.benefits_date];
            this.status = data[this.indexes.applications_st_name];
            this.startDate = data[this.indexes.start_date];
            this.endDate = data[this.indexes.end_date];
            this.takedDays = data[this.indexes.total_days];
            this.emp_comments = data[this.indexes.comments];
            this.isApprove = false;
            this.take_holidays = parseInt(data[this.indexes.take_holidays]);
            this.take_rest_days = parseInt(data[this.indexes.take_rest_days]);
            $('#date-range200').val(moment(data[this.indexes.start_date], 'ddd DD-MMM-YYYY').format('YYYY-MM-DD')).trigger('change');
            $('#date-range201').val(moment(data[this.indexes.end_date], 'ddd DD-MMM-YYYY').format('YYYY-MM-DD')).trigger('change');
            // this.getReturnDate(data);
            this.returnDate = data[this.indexes.return_date];
            this.takedDays = data[this.indexes.total_days];
            this.noBussinesDayIndex = 0;
            this.totCalendarDays = (moment(moment(this.endDate, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD'), 'YYYY-MM-DD').diff(moment(moment(this.startDate, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD'), 'YYYY-MM-DD'), 'days') + 1);
            // this.reMaplDays();
            $('#modal_solicitud').modal('show');
            Swal.close();
        },

        getApplication(){
            if (table['table_requestVac'].row('.selected').data() == undefined) {
                SGui.showError("Debe seleccionar un renglón");
                return;
            }
            this.endDate = null;
            let data = table['table_requestVac'].row('.selected').data();

            axios.post(this.oData.getApplicationRoute, {
                'application_id': data[this.indexes.id],
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.oApplication = data.oApplication;
                    this.showModal();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(error){
                console.log(error);
                SGui.showError(error);
            })
        },

        setMyReturnDate(){
            this.MyReturnDate = ReqDatepicker.getDate('dd-mm-yyyy');
            this.returnDate = this.oDateUtils.formatDate(this.MyReturnDate, 'ddd DD-MMM-YYYY');
            this.showDatePickerSimple  = false;
        },

        editMyReturnDate(){
            ReqDatepicker.setDate({ clear: !0 });
            if(this.endDate != null || this.endDate != undefined || this.endDate != ''){
                ReqDatepicker.setOptions({minDate: moment(this.endDate, 'ddd DD-MMM-YYYY').add(1, 'days').format("DD-MM-YYYY")});
            }else{
                ReqDatepicker.setOptions({minDate: null});
            }
            this.showDatePickerSimple  = true;
        },

        async showModal(){
            SGui.showWaitingBlock(15000);
            $('#two-inputs').data('dateRangePicker').clear();
            this.oUser = await this.oUsersUtils.getUserData(this.oData.getUserDataRoute, this.oApplication.user_id);
            await this.getEmpApplicationsEA(this.oApplication.user_id);
            await this.getlDays(this.oApplication.id_application);
            this.vacationUtils.createClass(this.lTemp);
            this.comments = this.oApplication.sup_comments_n;
            this.idRequest = this.oApplication.id_application;
            this.idUser = this.oApplication.user_id;
            birthday = this.oApplication.birthday_n;
            aniversaryDay = this.oDateUtils.formatDate(this.oApplication.benefits_date, 'ddd DD-MMM-YYYY');
            this.status = this.oApplication.applications_st_name;
            this.startDate = this.oDateUtils.formatDate(this.oApplication.start_date, 'ddd DD-MMM-YYYY');
            this.endDate = this.oDateUtils.formatDate(this.oApplication.end_date, 'ddd DD-MMM-YYYY');
            this.takedDays = this.oApplication.total_days;
            this.emp_comments = this.oApplication.emp_comments_n;
            this.isApprove = false;
            $('#date-range200').val(this.oApplication.start_date).trigger('change');
            $('#date-range201').val(this.oApplication.end_date).trigger('change');
            this.returnDate = this.oDateUtils.formatDate(this.oApplication.return_date, 'ddd DD-MMM-YYYY');
            this.showDatePickerSimple = false;
            ReqDatepicker.setDate(moment(this.returnDate, 'ddd DD-MMM-YYYY').format("DD-MM-YYYY"));
            this.takedDays = this.oApplication.total_days;
            this.noBussinesDayIndex = 0;
            this.totCalendarDays = (moment(this.oApplication.end_date).diff(moment(this.oApplication.start_date), 'days') + 1);
            this.isFromMail = true;
            $('#modal_solicitud').modal('show');
            Swal.close();
            if(this.oApplication.request_status_id == this.oData.const.APPLICATION_CONSUMIDO ||
                this.oApplication.request_status_id == this.oData.const.APPLICATION_APROBADO
                ){
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
            }else if(this.oApplication.request_status_id == this.oData.const.APPLICATION_RECHAZADO){
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
        },

        getlDays(id){
            return new Promise((resolve) => 
            axios.post(this.oData.getRequestlDaysRoute, {
                'id_application': id,
            })
            .then(response => {
                let data = response.data;
                if(data.success){
                    let oDates = JSON.parse(data.lDays);
                    for(let i = 0; i < oDates.length; i++){
                        oDates[i].date = this.oDateUtils.formatDate(oDates[i].date, 'ddd DD-MMM-YYYY');
                    }
                    this.lDays = oDates;
                    resolve(data.lDays);
                }else{
                    SGui.showMessage('', data.message, data.icon);
                    resolve(null);
                }
            })
            .catch( function (error){
                console.log(error);
                resolve(error);
            }));
        },

        reMaplDays(){
            for(let i = 0; i < (parseInt(this.takedDays) - this.originalDaysTaked); i++){
                this.lDays.push(this.lNoBussinesDay[this.noBussinesDayIndex]);
                this.noBussinesDayIndex++;
                this.lDays.sort(function(a, b) {
                    const dateA = new Date(a);
                    const dateB = new Date(b);
                    return dateA - dateB;
                });
            }
        },

        reMaplDaysConventionalFormat(){
            for (let i = 0; i < this.lDays.length; i++) {
                this.lDaysConventionalFormat.push(moment(this.lDays[i], 'ddd DD-MMM-YYYY').format('YYYY-MM-DD'));
            }
        },

        acceptRequest(){
            SGui.showWaiting(10000);
            this.reMaplDaysConventionalFormat();
            var route = this.oData.acceptRequestRoute;
            axios.post(route, {
                'id_application': this.idRequest,
                'id_user': this.idUser,
                'comments': this.comments,
                'year': this.year,
                'returnDate': moment(this.returnDate, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD'),
                'manager_id': this.selectedmanager,
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
                'returnDate': moment(this.returnDate, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD'),
                'manager_id': this.selectedmanager,
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
            let dataIsnormal = [];
            for(var emp of data){
                for(rec of emp.applications){
                    dataReq.push(
                        [
                            rec.id_application,
                            rec.user_id,
                            emp.birthday_n,
                            emp.benefits_date,
                            emp.payment_frec_id,
                            rec.request_status_id,
                            rec.take_holidays,
                            rec.take_rest_days,
                            rec.sup_comments_n,
                            rec.user_apr_rej_id,
                            emp.employee,
                            rec.folio_n,
                            this.oDateUtils.formatDate(rec.created_at, 'ddd DD-MMM-YYYY'),
                            rec.user_apr_rej_name,
                            ((
                                rec.request_status_id == this.oData.const.APPLICATION_APROBADO ||
                                rec.request_status_id == this.oData.const.APPLICATION_CONSUMIDO
                                ) ?
                                this.oDateUtils.formatDate(rec.approved_date_n, 'ddd DD-MMM-YYYY') :
                                ((rec.request_status_id == this.oData.const.APPLICATION_RECHAZADO) ?
                                    this.oDateUtils.formatDate(rec.updated_at, 'ddd DD-MMM-YYYY') :
                                    '')),
                            this.oDateUtils.formatDate(rec.start_date, 'ddd DD-MMM-YYYY'),
                            this.oDateUtils.formatDate(rec.end_date, 'ddd DD-MMM-YYYY'),
                            this.oDateUtils.formatDate(rec.return_date, 'ddd DD-MMM-YYYY'),
                            rec.total_days,
                            this.specialType(rec),
                            rec.request_status_id == 2 ? 'NUEVO' : (rec.applications_st_name == 'CONSUMIDO' ? 'APROBADO' : rec.applications_st_name),
                            rec.emp_comments_n,
                            rec.start_date,
                        ]
                    );

                    dataIsnormal.push(rec.is_normal);
                }
            }
            table['table_requestVac'].clear().draw();
            table['table_requestVac'].rows.add(dataReq).draw();


            // for (let i = 0; i < dataIsnormal.length; i++) {
            //     let row = table['table_requestVac'].row(i).nodes().to$();
            //     if(!dataIsnormal[i]){
            //         $(row).addClass('noSelectableRow');
            //     }
            // }
        },

        filterYear(){
            SGui.showWaiting(5000);
            axios.post(this.oData.filterYearRoute, {
                'year': this.year,
                'manager_id': this.selectedmanager,
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
            return new Promise((resolve) => 
                axios.post(this.oData.applicationsEARoute, {
                    'user_id':  user_id
                })
                .then(response => {
                    let data = response.data;
                    if(data.success){
                        this.applicationsEA = data.arrAplications;
                        dateRangePickerArrayApplications = data.arrAplications;
                        dateRangePickerArraySpecialSeasons = data.arrSpecialSeasons;
                        this.lTemp = data.lTemp;
                        resolve(dateRangePickerArrayApplications);
                    }else{
                        SGui.showMessage('', data.message, data.icon);
                        resolve(null);
                    }
                })
                .catch( function (error){
                    console.log(error);
                    resolve(error);
                })
            );
        },

        seeLikeManager(){
            SGui.showWaiting(15000);
            this.selectedmanager = parseInt($('#selManager').val());
            if(this.selectedmanager == null || this.selectedmanager == '' || isNaN(this.selectedmanager)){
                swal.close();
                SGui.showMessage('', 'Debe seleccionar un supervisor', 'info');
                return;
            }
            let manager_id = $('#selManager').val();
            let manager_name = $('#selManager').find(':selected').text();
            axios.post( this.oData.getDataManagerRoute,{
                'manager_id': parseInt(manager_id),
                'manager_name': manager_name,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    SGui.showOk();
                    this.year = data.year;
                    this.reDrawRequestTable(data.lEmployees);
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(error) {
                console.log(error);
                SGui.showError(error);
            })
        },

        cleanManager(){
            SGui.showWaiting(15000);
            axios.post( this.oData.getDataManagerRoute,{
                'manager_id': null,
                'manager_name': null,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    SGui.showOk();
                    this.year = data.year;
                    this.reDrawRequestTable(data.lEmployees);
                    $('#selManager').val('').trigger('change');
                    this.selectedmanager = null;
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(error) {
                console.log(error);
                SGui.showError(error);
            })
        },

        checkIsSelectable(row){
            if(!row.is_normal){
                // return "noSelectableRow";
                return "";
            }else{
                return "";
            }
        },

        specialType(data){
            let type = "";
            if(data.is_normal && !data.is_past && !data.is_advanced && !data.is_proportional && !data.is_season_special){
                type = type + "Normal\n";
            }

            if(data.is_past){
                type = type + "Días pasados\n";
            }

            if(data.is_advanced){
                type = type + "Días adelantados\n";
            }

            if(data.is_proportional){
                type = type + "Días proporcionales\n";
            }

            if(data.is_season_special){
                type = type + "Temporada especial\n";
            }

            if(data.is_recover_vacation){
                type = type + "Con días vencidos\n";
            }

            return type;
        },

        checkSpecial(data){
            let message = "";
            let is_special = false;

            if(data.is_proportional){
                message = message + "Se utilizarán días proporcionales para la solicitud.\n";
                is_special = true;
                this.lTypes.push('Con días proporcionales');
            }

            if(data.is_advanced){
                message = message + "Se utilizarán más días de los proporcionales para la solicitud.\n";
                is_special = true;
                this.lTypes.push('Con más días de los proporcionales');
            }

            if(data.is_past){
                message = message + "Se tomarán días pasados.\n";
                is_special = true;
                this.lTypes.push('Con días pasados');
            }

            if(data.is_season_special){
                message = message + 'Estas tomando días en temporada especial ' + oSeason.name + "\n";
                is_special = true;
                this.lTypes.push('Con días en temporada especial');
            }

            if(data.is_normal){
                this.lTypes.push('Normal');
            }

            return [is_special, message];
        },
    },
})
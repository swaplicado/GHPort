var app = new Vue({
    el: '#incidencesApp',
    data: {
        oData: oServerData,
        lIncidences: oServerData.lIncidences,
        oCopylIncidences: structuredClone(oServerData.lIncidences),
        oDateUtils: new SDateUtils(),
        vacationUtils: new vacationUtils(),
        indexes_incidences: oServerData.indexes_incidences,
        oUser: oServerData.oUser,
        lClass: oServerData.lClass,
        lTypes: oServerData.lTypes,
        lTypesToFilter: [],
        filter_class_id: oServerData.lClass[0].id_incidence_cl,
        filter_type_id: null,
        select_changed: false,
        class_id: null,
        type_id: null,
        lTemp: oServerData.lTemp,
        showCalendar: false,
        is_singleDate: false,
        startDate: null,
        endDate: null,
        returnDate: null,
        totCalendarDays: null,
        takedDays: null,
        showDatePickerSimple: false,
        lDays: [],
        comments: null,
        valid: true,
        idApplication: null,
        oApplication: null,
        isEdit: false,
        class_name: null,
        type_name: null,
        is_normal: true,
        is_past: false,
        is_season_special: false,
        lSpecialTypes: [],
    },
    computed: {
        propertyAAndPropertyB() {
            return `${this.endDate}|${this.takedDays}`;
        },
    },
    watch: {
        filter_class_id:function(val){
            this.setTypesToSelect(val);            

            $('#incident_tp_filter').empty().trigger("change");
            
            let dataTypes = [];
            for (let i = 0; i < this.lTypesToFilter.length; i++) {
                dataTypes.push({id: this.lTypesToFilter[i].id_incidence_tp, text: this.lTypesToFilter[i].incidence_tp_name });
            }
            
            $('#incident_tp_filter').select2({
                data: dataTypes,
            });

            if(!!table['table_MyIncidences'] && this.select_changed){
                table['table_MyIncidences'].draw();
                this.select_changed = false;
            }
        },
        filter_type_id:function(val){
            
        },
        class_id:function(val){
            if(!this.isEdit){
                $('#clear').trigger('click');
                this.showCalendar = false;
                this.setClass();
            }
        },
        type_id:function(val){
            if(!this.isEdit){
                this.createCalendar(val);
            }
        },
        propertyAAndPropertyB(newVal, oldVal) {
            let oldlTypes = structuredClone(this.lSpecialTypes);
            this.lSpecialTypes = [];
            if(this.endDate != null && this.endDate != undefined && this.endDate != ""){
                let res = this.checkSpecial();
                if(res[0] && !this.arraysEqual(this.lSpecialTypes, oldlTypes)){
                    SGui.showMessage('', res[1], 'warning');
                }
            }
        },
    },
    updated() {
        
    },
    mounted(){
        var self = this;
        this.lTypesToFilter = this.lTypes.filter(function(item) {
            return item.incidence_cl_id == self.lClass[0].id_incidence_cl;
        });

        let dataClass = [];
        for (let i = 0; i < this.lClass.length; i++) {
            dataClass.push({id: this.lClass[i].id_incidence_cl, text: this.lClass[i].incidence_cl_name });
        }

        let dataTypes = [];
        for (let i = 0; i < this.lTypesToFilter.length; i++) {
            dataTypes.push({id: this.lTypesToFilter[i].id_incidence_tp, text: this.lTypesToFilter[i].incidence_tp_name });
        }
        
        $('.select2-class-modal').select2({
            dropdownParent: $('#incidencesModal')
        });

        $('.select2-class').select2({});

        $('#incident_cl_filter').select2({
            data: dataClass,
        }).on('select2:select', function(e) {
            self.filter_class_id = e.params.data.id;
        });

        $('#incident_tp_filter').select2({
            data: dataTypes,
        }).on('select2:select', function(e) {
            self.filter_type_id = e.params.data.id;
        });

        $('#incident_class').select2({
            placeholder: 'Selecciona clase de incidencia',
            data: dataClass,
        }).on('select2:select', function(e) {
            self.class_id = e.params.data.id;
        });

        $('#incident_class').val('').trigger('change');

        $('#incident_type').select2({
            placeholder: 'Selecciona tipo de incidencia',
            data: [],
        }).on('select2:select', function(e) {
            self.type_id = e.params.data.id;
        });

        $('#incident_type').val('').trigger('change');

    },
    methods: {
        arraysEqual(a, b) {
            if (a.length !== b.length) {
                return false;
            }
            return a.every(element => b.includes(element));
        },

        createCalendar(val){
            $('#clear').trigger('click');
            if(!!val){
                this.showCalendar = true;
                switch (parseInt(val)) {
                    case 2:
                    case 3:
                        this.is_singleDate = false;
                        initCalendar(
                            null,
                            false,
                            false,
                            oServerData.oUser.payment_frec_id,
                            oServerData.lTemp,
                            oServerData.lHolidays,
                            oServerData.oUser.birthday_n,
                            oServerData.oUser.benefits_date,
                        );
                        break;
                    case 4:
                        this.is_singleDate = true;
                        initCalendar(
                            null,
                            true,
                            true,
                            oServerData.oUser.payment_frec_id,
                            oServerData.lTemp,
                            oServerData.lHolidays,
                            oServerData.oUser.birthday_n,
                            oServerData.oUser.benefits_date,
                        );
                        break;
                    default:
                        break;
                }
            }
        },

        setClass(){
            let dataTypes = [];
            for (let i = 0; i < this.lTypesToFilter.length; i++) {
                dataTypes.push({id: this.lTypesToFilter[i].id_incidence_tp, text: this.lTypesToFilter[i].incidence_tp_name });
            }

            $('#incident_type').empty().trigger("change");
            
            $('#incident_type').select2({
                placeholder: 'Selecciona tipo de incidencia',
                data: dataTypes,
            });

            $('#incident_type').val('').trigger('change');
            this.type_id = null;
        },

        setTypesToSelect(val){
            this.lTypesToFilter = [];
            this.lTypesToFilter = this.lTypes.filter(function(item) {
                return item.incidence_cl_id == val;
            });

            if(this.lTypesToFilter.length > 0){
                this.filter_type_id = this.lTypesToFilter[0].id_incidence_tp;
            }else{
                this.filter_type_id = null;
            }
        },

        getDataDays(){
            var result = this.vacationUtils.getTakedDays(
                            this.oData.lHolidays,
                            this.oUser.payment_frec_id,
                            moment(this.startDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                            moment(this.endDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                            this.oData.constants,
                            false,
                            false
                        );

            this.returnDate = this.oDateUtils.formatDate(result[0], 'ddd DD-MMM-YYYY');
            this.takedDays = result[1];
            this.lDays = result[2];
            this.totCalendarDays = result[3];
        },

        getApplication(){
            SGui.showWaiting(15000);
            return new Promise((resolve) =>
                axios.post(this.oData.routeGetIncidence, {
                    'application_id': this.idApplication,
                })
                .then( result => {
                    let data = result.data;
                    if(data.success){
                        this.oApplication = data.oApplication;
                        swal.close();
                        resolve(data.oApplication);
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

        formatlDays(slDays){
            let olDays = JSON.parse(slDays);
            for(let i = 0; i < olDays.length; i++){
                olDays[i].date = this.oDateUtils.formatDate(olDays[i].date, 'ddd DD-MMM-YYYY');
            }

            return olDays;
        },

        async showModal(data = null){
            $('#clear').trigger('click');
            $('#incident_class').val('').trigger('change');
            $('#incident_type').empty().trigger("change");
            this.cleanData();
            if(data != null){
                this.isEdit = true;
                this.idApplication = data[this.indexes_incidences.id_application];
                await this.getApplication();
                this.class_id = data[this.indexes_incidences.id_incidence_cl];
                this.type_id = data[this.indexes_incidences.id_incidence_tp];
                this.class_name = this.oApplication.incidence_cl_name;
                this.type_name = this.oApplication.incidence_tp_name;
                this.createCalendar(this.type_id);
                this.showCalendar = true;
                $('#date-range-001').val(moment(data[this.indexes_incidences.start_date], 'ddd DD-MMM-YYYY').format('YYYY-MM-DD')).trigger('change');
			    $('#date-range-002').val(moment(data[this.indexes_incidences.end_date], 'ddd DD-MMM-YYYY').format('YYYY-MM-DD')).trigger('change');
                this.startDate = this.oDateUtils.formatDate(this.oApplication.start_date, 'ddd DD-MMM-YYYY');
                this.endDate = this.oDateUtils.formatDate(this.oApplication.end_date, 'ddd DD-MMM-YYYY');
                this.returnDate = this.oDateUtils.formatDate(this.oApplication.return_date, 'ddd DD-MMM-YYYY');
                this.totCalendarDays = this.oApplication.tot_calendar_days;
                this.takedDays = this.oApplication.total_days;
                this.lDays = this.formatlDays(this.oApplication.ldays);
                this.comments = this.oApplication.emp_comments_n;
                this.is_normal = this.oApplication.is_normal;
                this.is_past = this.oApplication.is_past;
                this.is_season_special = this.oApplication.is_season_special;
            }else{
                if(this.HasIncidencesCreated()){
                    SGui.showMessage('', 'No puede crear otra incidencia si tiene incidencias creadas pendientes de enviar', 'warning');
                    return;
                }
            }
            $('#modal_incidences').modal('show');
        },

        setMyReturnDate(){
            if(this.endDate != null && this.endDate != undefined && this.endDate != ''){
                this.MyReturnDate = datepicker.getDate('dd-mm-yyyy');
                this.returnDate = this.oDateUtils.formatDate(this.MyReturnDate, 'ddd DD-MMM-YYYY');
            }
            this.showDatePickerSimple  = false;
        },

        editMyReturnDate(){
            datepicker.setDate({ clear: !0 });
            if(this.endDate != null && this.endDate != undefined && this.endDate != ''){
                datepicker.setOptions({minDate: moment(this.endDate, 'ddd DD-MMM-YYYY').add(1, 'days').format("DD-MM-YYYY")});
            }else{
                datepicker.setOptions({minDate: null});
            }
            this.showDatePickerSimple  = true;
        },

        setTakedDay(index, checkbox_id){
            let checked = $('#' + checkbox_id).is(":checked");
            this.lDays[index].taked = checked;
            checked ? this.takedDays++ : this.takedDays--;
        },

        setApplication(route){
            let copylDays = structuredClone(this.lDays);
            for (let index = 0; index < copylDays.length; index++) {
                copylDays[index].date = moment(copylDays[index].date, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD');
            }

            axios.post(route, {
                'id_application': this.idApplication,
                'startDate': moment(this.startDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                'endDate': moment(this.endDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                'comments': this.comments,
                'takedDays': this.takedDays,
                'returnDate': moment(this.returnDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                'tot_calendar_days': this.totCalendarDays,
                'employee_id': this.oUser.id,
                'lDays': copylDays,
                'incident_type_id': this.type_id,
                'incident_class_id': this.class_id,
                'is_normal': this.is_normal,
                'is_past': this.is_past,
                'is_season_special': this.is_season_special,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.oCopylIncidences = data.lIncidences;
                    this.reDrawTableMyIncidences(data);
                    SGui.showOk();
                    $('#modal_incidences').modal('hide');
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(error){
                console.log(error);
                SGui.showError(error);
            });
        },

        save(){
            SGui.showWaiting(15000);

            if(this.startDate == null || this.startDate == ""){
                SGui.showMessage('', 'Debe ingresar una fecha de inicio');
                return;
            }

            if(this.endDate == null || this.endDate == ""){
                SGui.showMessage('', 'Debe ingresar una fecha de fin');
                return;
            }

            if(this.isEdit){
                this.setApplication(this.oData.routeUpdate);
            }else{
                this.setApplication(this.oData.routeCreate);
            }
        },

        cleanData(){
            // this.lTemp = oServerData.lTemp;
            this.class_id = null;
            this.type_id = null;
            this.showCalendar = false;
            this.is_singleDate = false;
            this.startDate = null;
            this.endDate = null;
            this.returnDate = null;
            this.totCalendarDays = null;
            this.takedDays = null;
            this.showDatePickerSimple = false;
            this.lDays = [];
            this.comments = null;
            this.valid = true;
            this.idApplication = null;
            this.isEdit = false;
        },

        reDrawTableMyIncidences(data){
            var dataIncidences = [];
            for(let incident of data.lIncidences){
                dataIncidences.push(
                    [
                        incident.id_application,
                        incident.request_status_id,
                        incident.emp_comments_n,
                        incident.sup_comments_n,
                        incident.user_apr_rej_id,
                        incident.id_incidence_cl,
                        incident.id_incidence_tp,
                        incident.incidence_tp_name,
                        incident.folio_n,
                        (incident.date_send_n != null ? 
                            this.oDateUtils.formatDate(incident.date_send_n, 'ddd DD-MMM-YYYY') :
                                this.oDateUtils.formatDate(incident.updated_at, 'ddd DD-MMM-YYYY')
                            ),
                        incident.user_apr_rej_name,
                        (incident.request_status_id == this.oData.constants.APPLICATION_APROBADO) ?
                            this.oDateUtils.formatDate(incident.approved_date_n, 'ddd DD-MMM-YYYY') :
                                ((incident.request_status_id == this.oData.constants.APPLICATION_RECHAZADO) ?
                                    this.oDateUtils.formatDate(incident.rejected_date_n, 'ddd DD-MMM-YYYY') :
                                        ''),
                        this.oDateUtils.formatDate(incident.start_date, 'ddd DD-MMM-YYYY'),
                        this.oDateUtils.formatDate(incident.end_date, 'ddd DD-MMM-YYYY'),
                        this.oDateUtils.formatDate(incident.return_date, 'ddd DD-MMM-YYYY'),
                        incident.total_days,
                        'SUBTIPO',
                        incident.applications_st_name,
                    ]
                );
            }
            table['table_MyIncidences'].clear().draw();
            table['table_MyIncidences'].rows.add(dataIncidences).draw();
        },

        HasIncidencesCreated(){
            for(let rec of this.oCopylIncidences){
                if(rec.request_status_id == 1){
                    return true;
                }
            }

            return false;
        },

        deleteRegistry(data){
            if(data[this.indexes_incidences.applications_st_name] != 'CREADO'){
                SGui.showMessage('','Solo se pueden eliminar incidencias con el estatus CREADO', 'warning');
                return;
            }

            Swal.fire({
                title: '¿Desea eliminar la incidencia ' + data[this.indexes_incidences.folio_n] + ' ?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deleteIncidence(data[this.indexes_incidences.id_application]);
                }
            })
        },

        deleteIncidence(application_id){
            SGui.showWaiting(15000);
            axios.post(this.oData.routeDelete, {
                'application_id': application_id,
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    this.oCopylIncidences = data.lIncidences;
                    this.reDrawTableMyIncidences(data);
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

        checkSpecial(){
            let message = "";
            let is_special = false;
            this.is_normal = true;
            this.is_past = false;
            this.is_season_special = false;

            if(moment(this.endDate, 'ddd DD-MMM-YYYY').isBefore(moment(this.today)) || moment(this.endDate, 'ddd DD-MMM-YYYY').isSame(moment(this.today)) || moment(this.startDate, 'ddd DD-MMM-YYYY').isBefore(moment(this.today)) || moment(this.startDate, 'ddd DD-MMM-YYYY').isSame(moment(this.today))){
                message = message + "Se tomarán días pasados.\n";
                is_special = true;
                this.is_normal = false;
                this.is_past = true;
                this.lSpecialTypes.push('Con días pasados');
            }

            for(let oSeason of this.lTemp){
                for(let day of oSeason.lDates){
                    if(moment(day, 'YYYY-MM-DD').isBetween(moment(this.startDate, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD'), moment(this.endDate, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD'), undefined, '[]')){
                        message = message + 'Estas tomando días en temporada especial ' + oSeason.name + "\n";
                        is_special = true;
                        this.is_normal = false;
                        this.is_season_special = true;
                        this.lSpecialTypes.push('Con días en temporada especial');
                        break;
                    }
                }
            }

            if(this.is_normal){
                this.lSpecialTypes.push('Normal');
            }

            return [is_special, message];
        },
    },
})
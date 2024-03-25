var app = new Vue({
    el: '#incidencesApp',
    data: {
        oData: oServerData,
        initialCalendarDate: oServerData.initialCalendarDate,
        lIncidences: oServerData.lIncidences,
        oCopylIncidences: structuredClone(oServerData.lIncidences),
        oDateUtils: new SDateUtils(),
        vacationUtils: new vacationUtils(),
        indexes_incidences: oServerData.indexes_incidences,
        oUser: oServerData.oUser,
        lSuperviser: oServerData.lSuperviser,
        myManagers: oServerData.myManagers,
        selectedmanager: null,
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
        oApplication: oServerData.oApplication,
        isEdit: false,
        class_name: null,
        type_name: null,
        is_normal: true,
        is_past: false,
        is_season_special: false,
        lSpecialTypes: [],
        isRevision: false,
        table_name: oServerData.table_name,
        lEmployees: oServerData.lEmployees,
        needRenderTableIncidences: false,
        renderTabletaReqIncidences: false,
        lBirthDay: [],
        birthDayYear: null,
        minYear: null,
        emp_comments: null,
        status_incidence: 0,
        limit_days: null,
        lEvents: oServerData.lEvents,
        is_event: false,
        lIncidencesEA: [],
    },
    computed: {
        propertyAAndPropertyB() {
            return `${this.endDate}|${this.takedDays}`;
        },
    },
    watch: {
        filter_class_id:function(val){
            this.setTypesToSelect(val);            
            $('#incident_tp_filter').empty();
            
            let dataTypes = [];
            val == 0 ? dataTypes = [{id: '0', text: 'Todos'}] : '';
            for (let i = 0; i < this.lTypesToFilter.length; i++) {
                dataTypes.push({id: this.lTypesToFilter[i].id_incidence_tp, text: this.lTypesToFilter[i].incidence_tp_name });
            }
            
            $('#incident_tp_filter').select2({
                data: dataTypes,
            });

            $('#incident_tp_filter').trigger("change");

            if(!!table['table_Incidences'] && this.select_changed){
                table['table_Incidences'].draw();
                this.select_changed = false;
            }
        },
        filter_type_id:function(val){
            
        },
        class_id:function(val){
            if(!this.isEdit && !this.isRevision){
                $('#clear').trigger('click');
                this.showCalendar = false;
                this.setClass(val);
            }
        },
        type_id:function(val){
            if(!this.isEdit && !this.isRevision){
                let oIncidence = this.lTypesToFilter.find(({ id_incidence_tp }) => id_incidence_tp == val);
                this.limit_days = oIncidence.limit_days_n != undefined ? oIncidence.limit_days_n : null;
                this.createCalendar(val);
                $('#two-inputs-calendar').on('datepicker-first-date-selected', function(event, data) {
                    if(self.limit_days != null && self.limit_days > 0){
                        if (data.date1 !== undefined) {
                            let oDate = self.checklDaysToIncidencesWithLimitDays(
                                    data.date1,
                                    (self.oUser != null ? self.oUser.payment_frec_id : 1),
                                    oServerData.lHolidays
                                );
                            // const endDate = new Date(moment(data.date1).add(self.limit_days, 'days').format("YYYY-MM-DD"));
                            const endDate = new Date(oDate.format("YYYY-MM-DD"));
                            $('#two-inputs-calendar').data('dateRangePicker').setDateRange(data.date1, endDate);
                        }
                    }
                });
            }

            if(val == this.oData.constants.TYPE_CUMPLEAÑOS){
                this.getBirdthDayIncidences();
            }
        },
        propertyAAndPropertyB(newVal, oldVal) {
            let oldlTypes = structuredClone(this.lSpecialTypes);
            this.lSpecialTypes = [];
            if(this.endDate != null && this.endDate != undefined && this.endDate != "" && this.valid){
                let res = this.checkSpecial();
                if(res[0] && !this.arraysEqual(this.lSpecialTypes, oldlTypes)){
                    // SGui.showMessage('', res[1], 'warning');
                    Swal.fire({
                        title: "<b>Hay " + res[2].length + (res[2].length > 1 ? " cuestiones" : " cuestión")
                            + " con esta solicitud que" + (res[2].length > 1 ? " pueden " : " puede ") + "afectar su procesamiento, favor de revisarla</b>",
                        icon: "info",
                        html: res[1],
                        allowOutsideClick: false,
                        showCloseButton: true,
                        focusConfirm: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: `
                          Aceptar
                        `,
                    });
                }
            }
        },
    },
    updated() {
        this.$nextTick(function () {
            if(typeof self.$refs.table_Incidences != 'undefined' && self.needRenderTableIncidences){
                this.createTable('table_Incidences', [0,2,3,4,7,16,17,19], [1,5,6]);
                let dataClassFilter = [{id: '0', text: 'Todos'}];
                for (let i = 0; i < this.lClass.length; i++) {
                    dataClassFilter.push({id: this.lClass[i].id_incidence_cl, text: this.lClass[i].incidence_cl_name });
                }

                $('#myIncident_cl_filter').select2({
                    data: dataClassFilter,
                });

                $('#myIncident_tp_filter').empty().trigger("change");
            
                let dataTypes = [];
                dataTypes = [{id: '0', text: 'Todos'}];
                for (let i = 0; i < this.lTypesToFilter.length; i++) {
                    dataTypes.push({id: this.lTypesToFilter[i].id_incidence_tp, text: this.lTypesToFilter[i].incidence_tp_name });
                }
                
                $('#myIncident_tp_filter').select2({
                    data: dataTypes,
                });

                $('#myIncident_cl_filter').change( function() {
                    $('#myIncident_tp_filter').empty();
                    let val = $('#myIncident_cl_filter').val();
                    let lDataTypes = [];
                    let dataTypes = [];
                    if(val != 0){
                        lDataTypes = self.lTypes.filter(function(item) {
                            return item.incidence_cl_id == val;
                        });
                    }else{
                        lDataTypes = self.lTypes;
                    }
            
                    val == 0 ? dataTypes = [{id: '0', text: 'Todos'}] : '';
                    for (let i = 0; i < lDataTypes.length; i++) {
                        dataTypes.push({id: lDataTypes[i].id_incidence_tp, text: lDataTypes[i].incidence_tp_name });
                    }
                    
                    $('#myIncident_tp_filter').select2({
                        data: dataTypes,
                    });
                    self.filterIncidenceTable();
                });
                
                $('#myIncident_tp_filter').change( function() {
                    self.filterIncidenceTable();
                });

                self.filterIncidenceTable();
            }
        })
    },
    mounted(){
        self = this;
        this.lTypesToFilter = this.lTypes.filter(function(item) {
            return item.incidence_cl_id == self.lClass[0].id_incidence_cl;
        });

        let dataClassFilter = [{id: '0', text: 'Todos'}];
        let dataClass = [];
        for (let i = 0; i < this.lClass.length; i++) {
            dataClass.push({id: this.lClass[i].id_incidence_cl, text: this.lClass[i].incidence_cl_name });
            dataClassFilter.push({id: this.lClass[i].id_incidence_cl, text: this.lClass[i].incidence_cl_name });
        }

        let dataTypesFilter = [{id: '0', text: 'Todos'}];
        let dataTypes = [];
        for (let i = 0; i < this.lTypesToFilter.length; i++) {
            dataTypes.push({id: this.lTypesToFilter[i].id_incidence_tp, text: this.lTypesToFilter[i].incidence_tp_name });
            dataTypesFilter.push({id: this.lTypesToFilter[i].id_incidence_tp, text: this.lTypesToFilter[i].incidence_tp_name });
        }
        
        $('.select2-class-modal').select2({
            dropdownParent: $('#incidencesModal')
        });

        $('.select2-class').select2({});

        $('#incident_cl_filter').select2({
            data: dataClassFilter,
        }).on('select2:select', function(e) {
            self.filter_class_id = e.params.data.id;
        });

        $('#incident_tp_filter').select2({
            data: dataTypesFilter,
        }).on('select2:select', function(e) {
            self.filter_type_id = e.params.data.id;
        });

        $('#incident_class').select2({
            placeholder: 'Selecciona clase de incidencia',
            data: dataClass,
        }).on('select2:select', function(e) {
            self.class_id = e.params.data.id;
        });
        this.class_id = this.lClass[0].id_incidence_cl;
        // this.setClass(this.class_id);
        // $('#incident_class').val('').trigger('change');

        $('#incident_type').select2({
            placeholder: 'Selecciona tipo de incidencia',
            data: [],
        }).on('select2:select', function(e) {
            self.type_id = e.params.data.id;
        });

        $('#incident_type').val('').trigger('change');

        if(!!this.oApplication && !!this.oUser){
            let data = [this.oApplication.id_application];
            this.showDataModal(data);
        }

        if(!!this.myManagers){
            var dataMyManagers = [];
            for (let i = 0; i < this.myManagers.length; i++) {
                dataMyManagers.push({id: this.myManagers[i].id, text: this.myManagers[i].full_name_ui });
            }
    
            $('#selManager')
                .select2({
                    placeholder: 'selecciona',
                    data: dataMyManagers,
                });
    
            $('#selManager').val('').trigger('change');
        }

        $('#status_incidence').on('change', function() {
            self.status_incidence = this.value;
        });

        $('#status_incidence').trigger('change');
    },
    methods: {
        initRequestincidences(){
            this.isRevision = true;
            this.oUser = null;
            table['table_ReqIncidences'].draw();
        },

        initGestionIncidences(){
            this.needRenderTableIncidences = true;
            this.isRevision = false;
            this.oUser = null;
            this.setSelectEmployees();
        },

        createTable(table_name, colTargets = [], colTargetsSercheable = []){
            table[table_name] = $('#'+table_name).DataTable({
                "language": {
                    "sProcessing":     "Procesando...",
                    "sLengthMenu":     "Mostrar _MENU_ registros",
                    "sZeroRecords":    "No se encontraron resultados",
                    "sEmptyTable":     "Ningún dato disponible en esta tabla",
                    "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                    "sInfoPostFix":    "",
                    "sSearch":         "Buscar:",
                    "sUrl":            "",
                    "sInfoThousands":  ",",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": {
                        "sFirst":    "Primero",
                        "sLast":     "Último",
                        "sNext":     "Siguiente",
                        "sPrevious": "Anterior"
                    },
                    "oAria": {
                        "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    }
                },
                "responsive": false,
                "dom": 'Bfrtip',
                "columnDefs": [
                    {
                        "targets": colTargets,
                        "visible": false,
                        "searchable": false,
                        "orderable": false,
                    },
                    {
                        "targets": colTargetsSercheable,
                        "visible": false,
                        "searchable": true,
                        "orderable": false,
                    },
                    {
                        "orderable": false,
                        "targets": "no-sort",
                    }
                ],
                "buttons": [
                    
                ],
                "paging": false,
                "dom": 'Bfrtip',
                "initComplete": function(){ 
                    $("#"+table_name).wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
                    $('#'+table_name+' tbody').on('click', 'tr', function () {
                        if ($(this).hasClass('selected')) {
                            $(this).removeClass('selected');
                        }
                        else {
                            table[table_name].$('tr.selected').removeClass('selected');
                            $(this).addClass('selected');
                        }
                    });

                    /**
                     * Editar un registro con vue modal
                     */
                    $('#btn_edit').click(function () {
                        if (table[table_name].row('.selected').data() == undefined) {
                            SGui.showError("Debe seleccionar un renglón");
                            return;
                        }
                
                        app.showModal(table[table_name].row('.selected').data());
                    });

                    /**
                     * Crear un registro con vue modal
                     */
                    $('#btn_crear').click(function () {        
                        app.showModal();
                    });

                    /**
                     * Borrar un registro con vue
                     */
                    $('#btn_delete').click(function  () {
                        if (table[table_name].row('.selected').data() == undefined) {
                            SGui.showError("Debe seleccionar un renglón");
                            return;
                        }
                        app.deleteRegistry(table[table_name].row('.selected').data());
                    });

                    /**
                    * Enviar un registro con vue
                    */
                    $('#btn_send').click(function  () {
                        if (table[table_name].row('.selected').data() == undefined) {
                            SGui.showError("Debe seleccionar un renglón");
                            return;
                        }
                        app.sendRegistry(table[table_name].row('.selected').data());
                    });
                },
            });

            
            this.reDrawTableIncidences('table_Incidences', this.lIncidences);
            this.needRenderTableIncidences = false;
        },

        arraysEqual(a, b) {
            if (a.length !== b.length) {
                return false;
            }
            return a.every(element => b.includes(element));
        },

        createCalendar(val, enable = true){
            $('#clear').trigger('click');
            if(!!val){
                this.showCalendar = true;
                switch (parseInt(val)) {
                    case this.oData.constants.TYPE_CUMPLEAÑOS:
                        this.is_singleDate = true;
                        initCalendar(
                            this.initialCalendarDate,
                            true,
                            true,
                            this.oUser.payment_frec_id,
                            this.lTemp,
                            oServerData.lHolidays,
                            this.oUser.birthday_n,
                            this.oUser.benefits_date,
                            enable,
                        );
                        break;
                    default:
                        this.is_singleDate = false;
                        initCalendar(
                            this.initialCalendarDate,
                            false,
                            false,
                            this.oUser.payment_frec_id,
                            this.lTemp,
                            oServerData.lHolidays,
                            this.oUser.birthday_n,
                            this.oUser.benefits_date,
                            enable,
                        );
                        break;
                }
            }
        },

        setClass(val){
            let lDataTypes = [];
            lDataTypes = this.lTypes.filter(function(item) {
                return item.incidence_cl_id == val;
            });

            let dataTypes = [];
            for (let i = 0; i < lDataTypes.length; i++) {
                dataTypes.push({id: lDataTypes[i].id_incidence_tp, text: lDataTypes[i].incidence_tp_name });
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
            if(val == 0){
                this.lTypesToFilter = this.lTypes;
            }else{
                this.lTypesToFilter = this.lTypes.filter(function(item) {
                    return item.incidence_cl_id == val;
                });
            }
            if(this.lTypesToFilter.length > 0){
                this.filter_type_id = this.lTypesToFilter[0].id_incidence_tp;
            }else{
                this.filter_type_id = null;
            }
        },

        getDataDays(){
            var result = this.vacationUtils.getTakedDays(
                            this.oData.lHolidays,
                            this.oUser != null ? this.oUser.payment_frec_id : 1,
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

        getEmpApplicationsEA(user_id){
            // SGui.showWaiting(3000);
            return new Promise((resolve) => 
            axios.post(this.oData.routeGetEmpIncidencesEA, {
                'user_id':  user_id
            })
            .then(response => {
                let data = response.data;
                if(data.success){
                    dateRangePickerArrayApplications = data.lVacations;
                    dateRangePickerArrayIncidences = data.lIncidences;
                    this.applicationsEA = data.arrAplications;
                    // swal.close();
                    resolve(dateRangePickerArrayIncidences);
                }else{
                    SGui.showMessage('', data.message, data.icon);
                    swal.close();
                    resolve(null);
                }
            })
            .catch( function (error){
                console.log(error);
                swal.close()
                resolve(error);
            }));
        },

        getApplication(){
            // SGui.showWaiting(15000);
            return new Promise((resolve) =>
                axios.post(this.oData.routeGetIncidence, {
                    'application_id': this.idApplication,
                })
                .then( result => {
                    let data = result.data;
                    if(data.success){
                        this.oApplication = data.oApplication;
                        this.lEvents = data.lEvents;
                        // swal.close();
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
            SGui.showWaiting();
            $('#clear').trigger('click');
            // $('#incident_class').val('').trigger('change');
            $('#incident_type').empty().trigger("change");
            this.cleanData();
            await this.getEmpApplicationsEA(this.oUser.id);
            this.class_id = this.lClass[0].id_incidence_cl;
            this.setClass(this.class_id);
            if(data != null){
                this.isEdit = true;
                this.idApplication = data[this.indexes_incidences.id_application];
                await this.getApplication();
                this.class_id = this.oApplication.id_incidence_cl;
                this.type_id = this.oApplication.id_incidence_tp;
                this.class_name = this.oApplication.incidence_cl_name;
                this.type_name = this.oApplication.incidence_tp_name;
                this.valid = this.oApplication.request_status_id == this.oData.constants.APPLICATION_CREADO;
                $('#date-range-001').val(this.oApplication.start_date);
			    $('#date-range-002').val(this.oApplication.end_date);
                this.createCalendar(this.type_id, this.valid);
                this.showCalendar = true;
                this.startDate = this.oDateUtils.formatDate(this.oApplication.start_date, 'ddd DD-MMM-YYYY');
                this.endDate = this.oDateUtils.formatDate(this.oApplication.end_date, 'ddd DD-MMM-YYYY');
                $('#date-range-001').val(this.oApplication.start_date).trigger('change');
			    $('#date-range-002').val(this.oApplication.end_date).trigger('change');
                this.returnDate = this.oDateUtils.formatDate(this.oApplication.return_date, 'ddd DD-MMM-YYYY');
                this.totCalendarDays = this.oApplication.tot_calendar_days;
                this.takedDays = this.oApplication.total_days;
                this.lDays = this.formatlDays(this.oApplication.ldays);
                this.comments = this.oApplication.emp_comments_n;
                this.is_normal = this.oApplication.is_normal;
                this.is_past = this.oApplication.is_past;
                this.is_season_special = this.oApplication.is_season_special;
                Swal.close();
            }else{
                if(this.HasIncidencesCreated()){
                    SGui.showMessage('', 'No puede crear otra incidencia si tiene incidencias creadas pendientes de enviar', 'warning');
                    return;
                }
            }
            Swal.close();
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
                'is_event': this.is_event,
                'birthDayYear': this.birthDayYear,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.oCopylIncidences = data.lIncidences;
                    this.reDrawTableIncidences('table_Incidences', data.lIncidences);
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

        async save(){
            if(this.startDate == null || this.startDate == ""){
                SGui.showMessage('', 'Debe ingresar una fecha de inicio');
                return;
            }

            if(this.endDate == null || this.endDate == ""){
                SGui.showMessage('', 'Debe ingresar una fecha de fin');
                return;
            }

            if(this.comments == null || this.comments == ""){
                SGui.showMessage('', 'Para proseguir, se requiere incluir un comentario en la solicitud');
                return;
            }

            if(this.takedDays < 1){
                SGui.showMessage('', 'No es posible generar una solicitud sin días efectivos de incidencia. Por favor, elija un rango de fechas que esté dentro de los días hábiles, o si es necesario, seleccione el día inhábil en la sección "Desglose de los días de calendario"', 'warning');
                return;
            }

            if(this.type_id == this.oData.constants.TYPE_CUMPLEAÑOS){
                let aux = await this.checkBirthDaysTaked();
                if(!aux){
                    return;
                }
            }

            SGui.showWaiting(15000);

            if(this.isEdit){
                this.setApplication(this.oData.routeUpdate);
            }else{
                this.setApplication(this.oData.routeCreate);
            }
        },

        cleanData(){
            // this.lTemp = oServerData.lTemp;
            this.oApplication = null;
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
            // this.isRevision = false;
        },

        reDrawTableIncidences(table_name, lIncidences){
            var dataIncidences = [];
            for(let incident of lIncidences){
                dataIncidences.push(
                    [
                        incident.id_application,
                        incident.request_status_id,
                        incident.emp_comments_n,
                        incident.sup_comments_n,
                        incident.user_apr_rej_id,
                        incident.id_incidence_cl,
                        incident.id_incidence_tp,
                        incident.employee,
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
                        incident.date_send_n,
                    ]
                );
            }
            table[table_name].clear().draw();
            table[table_name].rows.add(dataIncidences).draw();
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
            if(data[this.indexes_incidences.applications_st_name] != 'Nuevas'){
                SGui.showMessage('','La solicitud que deseas eliminar no tiene el estatus de "nuevas". Solo se pueden eliminar solicitudes con dicho estatus', 'warning');
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
                    this.reDrawTableIncidences('table_Incidences', data.lIncidences);
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
            this.is_event = false;
            let lMessages = [];

            if(moment(this.endDate, 'ddd DD-MMM-YYYY').isBefore(moment(this.today)) || moment(this.endDate, 'ddd DD-MMM-YYYY').isSame(moment(this.today)) || moment(this.startDate, 'ddd DD-MMM-YYYY').isBefore(moment(this.today)) || moment(this.startDate, 'ddd DD-MMM-YYYY').isSame(moment(this.today))){
                is_special = true;
                this.is_normal = false;
                this.is_past = true;
                this.lSpecialTypes.push('Con días pasados');
                lMessages.push("Se tomarán días pasados.");
            }

            for(let oSeason of this.lTemp){
                for(let day of oSeason.lDates){
                    if(moment(day, 'YYYY-MM-DD').isBetween(moment(this.startDate, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD'), moment(this.endDate, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD'), undefined, '[]')){
                        is_special = true;
                        this.is_normal = false;
                        this.is_season_special = true;
                        this.lSpecialTypes.push('Con días en temporada especial');
                        lMessages.push('Estas tomando días en temporada especial ' + oSeason.name);
                        break;
                    }
                }
            }

            for(let oEvent of this.lEvents) {
                for (let day of oEvent.lDates) {
                    if (moment(day, 'YYYY-MM-DD').isBetween(moment(this.startDate, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD'), moment(this.endDate, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD'), undefined, '[]')) {
                        is_special = true;
                        this.is_normal = false;
                        this.is_event = true;
                        this.lSpecialTypes.push('Con días en evento');
                        lMessages.push('Estas tomando días en evento, ' + oEvent.name);
                        break;
                    }
                }
            }

            if(this.lDays[0].bussinesDay == false && this.lDays[0].taked == false){
                is_special = true;
                this.is_normal = false;
                this.lSpecialTypes.push('inicio de vacaciones');

                lMessages.push("La fecha inicial del periodo de la incidencia es inhábil, si requieres la fecha puedes marcarla en el desglose de los días de calendario, si no, presiona limpiar");
            }

            if(this.lDays[this.lDays.length - 1].bussinesDay == false && this.lDays[this.lDays.length - 1].taked == false){
                is_special = true;
                this.is_normal = false;
                this.lSpecialTypes.push('fin de vacaciones');

                lMessages.push("La fecha final del periodo de la incidencia es inhábil, si requieres la fecha puedes marcarla en el desglose de los días de calendario, si no, presiona limpiar");
            }

            if(this.is_normal){
                this.lSpecialTypes.push('Normal');
            }

            message = "<ol>";
            for (let i = 0; i < lMessages.length; i++) {
                message = message + "<li>" + lMessages[i] + "</li>";
            }
            message = message + "</ol>";

            return [is_special, message, lMessages];
        },

        sendRegistry(data){
            if(data[this.indexes_incidences.applications_st_name] != 'Nuevas'){
                SGui.showMessage('','Solo se pueden enviar incidencias con el estatus CREADO', 'warning');
                return
            }

            let message = '<b>Se enviará a:</b>' +
                            '<br>' +
                            '<ul>';

            for (const user of this.lSuperviser) {
                message = message + '<li>' + user.full_name_ui + '</li>';
            }
                message = message + '</ul>';

            Swal.fire({
                title: '¿Desea enviar la incidencia ' + data[this.indexes_incidences.folio_n] + ' ?',
                html: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.sendIncident(data[this.indexes_incidences.id_application]);
                }
            })
        },

        sendIncident(application_id){
            SGui.showWaiting(15000);
            axios.post(this.oData.routeGestionSendIncidence, {
                'application_id': application_id,
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    this.oCopylIncidences = data.lIncidences;
                    this.reDrawTableIncidences('table_Incidences', data.lIncidences);
                    SGui.showOk();
                    this.checkMail(data.mailLog_id, this.oData.routeCheckMail);
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function(error) {
                console.log(error);
                SGui.showError(error);
            });
        },

        /**
         * Metodo utilizado en la vista de revisor para obtener el usuario de la incidencia
         */
        getEmployee(user_id){
            return new Promise((resolve) =>
                axios.post(this.oData.routeGetEmployee, {
                    'user_id': user_id,
                })
                .then( result => {
                    let data = result.data;
                    if(data.success){
                        this.oUser = data.oUser;
                        this.lTemp = data.lTemp;
                        this.lEvents = data.lEvents;
                        resolve(data.oUser);
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

        async approbeIncidence(){
            if(this.type_id == this.oData.constants.TYPE_CUMPLEAÑOS){
                let aux = await this.checkBirthDaysTaked();
                if(!aux){
                    return;
                }
            }
            SGui.showWaiting(15000);
            axios.post(this.oData.routeApprobe, {
                'application_id': this.oApplication.id_application,
                'comments': this.comments,
                'returnDate': moment(this.returnDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                'manager_id': this.selectedmanager,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.oCopylIncidences = data.lIncidences;
                    this.reDrawTableIncidences('table_ReqIncidences', data.lIncidences);
                    $('#modal_incidences').modal('hide');
                    SGui.showOk();
                    this.checkMail(data.mailLog_id, this.oData.routeCheckMail);
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(error){
                console.log(error);
                SGui.showError(error);
            });
        },

        rejectIncidence(){
            SGui.showWaiting(15000)
            axios.post(this.oData.routeReject, {
                'application_id': this.oApplication.id_application,
                'comments': this.comments,
                'returnDate': moment(this.returnDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                'manager_id': this.selectedmanager,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.oCopylIncidences = data.lIncidences;
                    this.reDrawTableIncidences('table_ReqIncidences', data.lIncidences);
                    $('#modal_incidences').modal('hide');
                    SGui.showOk();
                    this.checkMail(data.mailLog_id, this.oData.routeCheckMail);
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(error){
                console.log(error);
                SGui.showError(error);
            })
        },

        /**
         * Show modal para la vista de revision de incidencias
         * @param {*} data 
         */
        async showDataModal(data){
            SGui.showWaiting();
            this.cleanData();
            $('#clear').trigger('click');
            this.isRevision = true;
            this.idApplication = data[this.indexes_incidences.id_application];
            await this.getApplication();
            await this.getEmpApplicationsEA(this.oApplication.user_id);
            await this.getEmployee(this.oApplication.user_id);
            this.class_id = this.oApplication.id_incidence_cl;
            this.type_id = this.oApplication.id_incidence_tp;
            this.class_name = this.oApplication.incidence_cl_name;
            this.type_name = this.oApplication.incidence_tp_name;
            this.showCalendar = true;
            this.startDate = this.oDateUtils.formatDate(this.oApplication.start_date, 'ddd DD-MMM-YYYY');
            this.endDate = this.oDateUtils.formatDate(this.oApplication.end_date, 'ddd DD-MMM-YYYY');
            // $('#date-range-001').val(this.oApplication.start_date).trigger('change');
            // $('#date-range-002').val(this.oApplication.end_date).trigger('change');
            $('#date-range-001').val(this.oApplication.start_date);
            $('#date-range-002').val(this.oApplication.end_date);
            this.createCalendar(this.type_id, false);
            this.returnDate = this.oDateUtils.formatDate(this.oApplication.return_date, 'ddd DD-MMM-YYYY');
            this.totCalendarDays = this.oApplication.tot_calendar_days;
            this.takedDays = this.oApplication.total_days;
            this.lDays = this.formatlDays(this.oApplication.ldays); 
            this.comments = this.oApplication.sup_comments_n;
            this.emp_comments = this.oApplication.emp_comments_n;
            this.is_normal = this.oApplication.is_normal;
            this.is_past = this.oApplication.is_past;
            this.is_season_special = this.oApplication.is_season_special;
            this.valid = this.oApplication.request_status_id == this.oData.constants.APPLICATION_ENVIADO;
            Swal.close();
            if(this.oApplication.request_status_id == this.oData.constants.APPLICATION_APROBADO){
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
            }else if(this.oApplication.request_status_id == this.oData.constants.APPLICATION_RECHAZADO){
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
            $('#modal_incidences').modal('show');
        },

        getAllEmployees(){
            SGui.showWaiting(15000);
            axios.get(this.oData.routeGetAllEmployees, {

            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.lEmployees = data.lEmployees;
                    this.setSelectEmployees();
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(error){
                console.log(error);
                SGui.showError(error);
            });
        },

        setGestionIncidences(){
            SGui.showWaiting(15000);
            this.getEmployeeData();
            this.needRenderTableIncidences = true;
        },
    
        getEmployeeData(){
            this.cleanData();
            this.oUser = null;
            let user_id = null;
            if(!!$('#selectEmp').val()){
                user_id = $('#selectEmp').val();
            }
            axios.post(this.oData.routeGetEmployee, {
                'user_id': user_id,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.oUser = data.oUser;
                    this.lIncidences = data.lIncidences;
                    this.oCopylIncidences = data.lIncidences;
                    this.lTemp = data.lTemp;
                    this.lEvents = data.lEvents;
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(error){
                console.log(error);
                SGui.showError(error);
            });
        },

        setSelectEmployees(){
            if(!!$('#selectEmp')){
                let dataEmp = []
                for (let i = 0; i < this.lEmployees.length; i++) {
                    dataEmp.push({id: this.lEmployees[i].id, text: this.lEmployees[i].employee});
                }
                $('#selectEmp').empty().trigger('change');
                $('#selectEmp').select2({
                    data: dataEmp
                })

                $('#selectEmp').val('').trigger('change');
            }
        },

        filterIncidenceTable(){
            table['table_Incidences'].draw();
        },

        sendAuthorize(){
            if (table['table_Incidences'].row('.selected').data() == undefined) {
                SGui.showError("Debe seleccionar un renglón");
                return;
            }

            let data = table['table_Incidences'].row('.selected').data();
    
            SGui.showWaiting(15000);

            axios.post(this.oData.routeSendAuthorize, {
                'application_id': data[this.indexes_incidences.id_application],
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.oCopylIncidences = data.lIncidences;
                    this.reDrawTableIncidences('table_Incidences', data.lIncidences);
                    SGui.showOk();
                    this.checkMail(data.mailLog_id, this.oData.routeCheckMail);
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(error){
                console.log(error);
                SGui.showError(error);
            })
        },

        checkBirthDaysTaked(){
            return new Promise((resolve) => {
                    if(this.lBirthDay.includes(parseInt(this.birthDayYear))){
                        Swal.fire({
                            title: 'Ya existe una incidencia de cumpleaños para el año:',
                            html: this.birthDayYear,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Aceptar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                resolve(true);
                            }else{
                                resolve(false);
                            }
                        })
                    }else{
                        resolve(true);
                    }
                }
            );
        },

        updateBirthDayYear(){
            if(this.birthDayYear < this.minYear){
                this.birthDayYear = moment().format('Y');
                SGui.showMessage('', 'El año de aplicación no puede ser menor a tu fecha de ingreso', 'warning');
                return;
            }
        },

        getBirdthDayIncidences(){
            axios.post(this.oData.routeGetBirdthDayIncidences, {
                'user_id': this.oUser.id,
                'application_id': this.idApplication,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.lBirthDay = data.lBirthDay;
                    this.birthDayYear = data.birthDayYear;
                    this.minYear = data.minYear;
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(error){
                console.log(error);
                SGui.showError(error);
            });
        },

        sleep(milliseconds) {
            return new Promise((resolve) => setTimeout(resolve, milliseconds));
        },

        async checkMail(mail_log_id, route){
            var checked = false;
            for(var i = 0; i<10; i++){
                console.log(i);
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
                            SGui.showMessage('', data.message, 'warning');
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

        seeLikeManager(){
            this.selectedmanager = parseInt($('#selManager').val());
            if(!(!!this.selectedmanager)){
                SGui.showMessage('', 'Debe seleccionar un supervisor', 'info');
                return;
            }
            SGui.showWaiting(15000);
            let manager_id = $('#selManager').val();
            let manager_name = $('#selManager').find(':selected').text();
            axios.post( this.oData.routeSeeLikeManager,{
                'manager_id': parseInt(manager_id),
                'manager_name': manager_name,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    SGui.showOk();
                    this.reDrawTableIncidences('table_ReqIncidences', data.lIncidences);
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
            axios.post( this.oData.routeSeeLikeManager,{
                'manager_id': null,
                'manager_name': null,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    SGui.showOk();
                    this.reDrawTableIncidences('table_ReqIncidences', data.lIncidences);
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

        cancelRegistry(data){
            Swal.fire({
                title: '¿Desea cancelar la incidencia?',
                html:   '<b>Colaborador: </b>' +
                        data[this.indexes_incidences.employee] +
                        '<br>' +
                        '<b>incidencia:</b> ' +
                        data[this.indexes_incidences.incidence_tp_name] +
                        '<br>' +
                        '<b>Inicio:</b> ' +
                        data[this.indexes_incidences.start_date] +
                        '<br>' +
                        '<b>Fin:</b> ' +
                        data[this.indexes_incidences.end_date],
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'No',
                confirmButtonText: 'Si'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.cancelRequest(data[this.indexes_incidences.id_application]);
                }
            })
        },

        cancelRequest(application_id){
            SGui.showWaiting(15000);

            let route = this.oData.cancelIncidenceRoute;
            axios.post(route, {
                'application_id': application_id,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.oCopylIncidences = data.lIncidences;
                    this.reDrawTableIncidences('table_ReqIncidences', data.lIncidences);
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function(error){
                console.log(error);
                SGui.showError(error);
            });
        },

        checklDaysToIncidencesWithLimitDays(startDate, payment, lHolidays){
            let oDate = moment(startDate);
            if(payment == this.oData.constants.QUINCENA){
                for (let i = 0; i < this.limit_days; i++) {
                    if(oDate.weekday() == 5 || oDate.weekday() == 6 || lHolidays.includes(oDate.format('YYYY-MM-DD'))){
                        i--;
                    }
                    oDate.add(1, 'days');
                }
            }else{
                for (let i = 0; i < this.limit_days; i++) {
                    if(oDate.weekday() == 6 || lHolidays.includes(oDate.format('YYYY-MM-DD'))){
                        i--;
                    }
                    oDate.add(1, 'days');
                }
            }

            return oDate;
        },

        deleteSendRegistry(){
            let data = table['table_ReqIncidences'].row('.selected').data()
            if(data[this.oData.indexes_incidences.request_status_id] != 2){
                SGui.showMessage('','La solicitud que deseas eliminar no tiene el estatus de "Por aprobar". Solo se pueden eliminar solicitudes con dicho estatus', 'warning');
                return;
            }

            Swal.fire({
                title: '¿Desea eliminar la solicitud de?',
                html: '<b>' + data[this.oData.indexes_incidences.employee] + '</b><br>Con fechas:<br>'  + '<b>Inicio:</b> ' + data[this.oData.indexes_incidences.start_date] + '<br>' + '<b>Fin:</b> ' +  data[this.oData.indexes_incidences.end_date],
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deleteSendRequest(data[this.oData.indexes_incidences.id_application], data[this.oData.indexes_incidences.user_id]);
                }
            })
        },

        deleteSendRequest(request_id, user_id){
            SGui.showWaiting();
            let route = this.oData.deleteSendIncidenceRoute;
            axios.post(route,{
                'id_application': request_id,
                'manager_id': this.selectedmanager,
            }).then(result => {
                let data = result.data;
                if(data.success){
                    this.oCopylIncidences = data.lIncidences;
                    this.reDrawTableIncidences('table_ReqIncidences', data.lIncidences);
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            }).catch(function(error){
                console.log(error);
                SGui.showError(error);
            });
        }
    }
});
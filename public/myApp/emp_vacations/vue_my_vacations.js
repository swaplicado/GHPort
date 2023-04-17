var appMyVacations = new Vue({
    el: '#myVacations',
    data: {
        oData: oServerData,
        oDateUtils: new SDateUtils(),
        vacationUtils: new vacationUtils(),
        indexes: oServerData.indexesMyRequestTable,
        oUser: null,  //No modificar, mejor modificar oCopyUser
        oCopyUser: null,
        lEmployees: [],
        lHolidays: oServerData.lHolidays,
        lTemp: oServerData.lTemp,
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
        actual_vac_days: null,
        prop_vac_days: null,
        prox_vac_days: null,
        take_rest_days: false,
        take_holidays: false,
        applicationsEA: [],
        isNewApplication: false,
        valid: true,
        oReDrawTables: new SReDrawTables(),
        totCalendarDays: 0,
        tempData: null,
        renderTableMyVacations: false,
        renderTableMyRequest: false,
        initialCalendarDate: null,
        takedNoBussinesDay: false,
        originalDaysTaked: 0,
        lNoBussinesDay: [],
        noBussinesDayIndex: 0,
        today: oServerData.today,
        is_normal: true,
        is_past: false,
        is_advanced: false,
        is_proportional: false,
        is_season_special: false,
        newData: false,
        MyReturnDate: null,
        showDatePickerSimple: false,
        isGestionVac: false,
        lTypes: [],
    },
    computed: {
        propertyAAndPropertyB() {
            return `${this.endDate}|${this.takedDays}`;
        },
    },
    watch: {
        startDate:function(val) {
            this.newData = true;
        },
        // endDate:function(val) {
        //     this.newData = true;
        //     this.lTypes = [];
        //     if(this.endDate != null && this.endDate != undefined && this.endDate != ""){
        //         let res = this.checkSpecial();
        //         if(res[0]){
        //             SGui.showMessage('', res[1], 'warning');
        //         }
        //     }
        // },
        propertyAAndPropertyB(newVal, oldVal) {
            this.newData = true;
            let oldlTypes = structuredClone(this.lTypes);
            this.lTypes = [];
            if(this.endDate != null && this.endDate != undefined && this.endDate != ""){
                let res = this.checkSpecial();
                if(res[0] && !this.arraysEqual(this.lTypes, oldlTypes)){
                    SGui.showMessage('', res[1], 'warning');
                }
            }
        },
    },
    mounted(){
        
    },
    updated: function(){
        var self = this
        this.$nextTick(function () {
            if(typeof self.$refs.vacationsTable != 'undefined' && !self.renderTableMyVacations){
                this.createVacationsTable(this.tempData);
            }
            if(typeof self.$refs.table_myRequest != 'undefined' && !self.renderTableMyRequest){
                this.createMyRequestTable(this.tempData);
                this.initDatePicker();
            }
            if(typeof self.$refs.datepicker != 'undefined' && self.isGestionVac){
                elem = document.querySelector('input[name="datepicker"]');
                datepicker = new Datepicker(elem, {
                    language: 'es',
                    format: 'dd/mm/yyyy',
                    // minDate: null,
                });

                elem.addEventListener('changeDate', function (e, details) { 
                    self.setMyReturnDate();
                });
                self.isGestionVac = false;
            }
        })
    },
    methods: {
        arraysEqual(a, b) {
            if (a.length !== b.length) {
                return false;
            }
            return a.every(element => b.includes(element));
        },
          
        /**
         * Metodo para inicialisar la vista cuando se usa en gestión de vacaciones
         */
        initView(lEmployees){
            this.lEmployees = lEmployees;
            var datalEmp = [{id: '', text: ''}];
            for(var i = 0; i<this.lEmployees.length; i++){
                datalEmp.push({id: this.lEmployees[i].id, text: this.lEmployees[i].employee});
            }

            $('#selectEmp')
                .select2({
                    placeholder: 'selecciona',
                    data: datalEmp,
                });
        },

        /**
         * Metodo para filtrar la tabla myRequest en la vista gestion de vacaciones
         */
        filterMyVacationTable(){
            table['table_myRequest'].draw();
        },

        /**
         * Metodo para obtener los datos de vacaciones del empleado en la vista gestion de vacaciones
         */
        getEmployeeData(){
            SGui.showWaiting(15000);
            this.cleanData();
            let employee_id = $('#selectEmp').select2().val();
            if(employee_id == null || employee_id == undefined || employee_id == ''){
                SGui.showMessage('', 'Debe seleccionar un colaborador', 'info');
                return;
            }

            axios.post(this.oData.getEmployeeDataRoute, {
                'employee_id': employee_id,
            })
            .then(result => {
                let data = result.data;
                if(data.success){
                    this.lTemp = data.lTemp;
                    this.tempData = data;
                    this.year = data.year;
                    this.initialCalendarDate = data.initialCalendarDate;
                    this.isGestionVac = true;
                    this.initValuesForUser(data.oUser);
                    // this.initDatePicker();
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

        /**
         * Metodo para obtener la lista de los empleados directos o todos los empleados
         */
        getAllEmployees(){
            this.cleanData();
            let is_checked = document.getElementById('checkBoxAllEmployees').checked;
            if(is_checked){
                SGui.showWaiting(15000);
                axios.get(this.oData.getAllEmployeesRoute,{

                })
                .then( response => {
                    let data = response.data;
                    if(data.success){
                        $('#selectEmp').empty().trigger("change");
                        let lAlllEmployees = data.lEmployees;
                        let datalEmp = [{id: '', text: ''}];
                        for(var i = 0; i<lAlllEmployees.length; i++){
                            datalEmp.push({id: lAlllEmployees[i].id, text: lAlllEmployees[i].employee});
                        }
                        $('#selectEmp')
                            .select2({
                                placeholder: 'selecciona',
                                data: datalEmp,
                            });

                        SGui.showOk();
                    }else{
                        SGui.showMessage('', data.message, data.icon);
                    }
                })
                .catch( function(error){
                    SGui.showError(error);
                    console.log(error);
                })
            }else{
                SGui.showWaiting(15000);
                axios.get(this.oData.getDirectEmployeesRoute,{

                })
                .then( response => {
                    let  data = response.data;
                    if(data.success){
                        $('#selectEmp').empty().trigger("change");
                        let lDirectlEmployees = data.lEmployees;
                        let datalEmp = [{id: '', text: ''}];
                        for(var i = 0; i<lDirectlEmployees.length; i++){
                            datalEmp.push({id: lDirectlEmployees[i].id, text: lDirectlEmployees[i].employee});
                        }
                        $('#selectEmp')
                            .select2({
                                placeholder: 'selecciona',
                                data: datalEmp,
                            });

                        SGui.showOk();
                    }else{
                        SGui.showMessage('', data.message, data.icon);
                    }
                })
            }
        },

        /**
         * inicializar valores para la vista gestion de vacaciones
         */
        initValuesForUser(oUser){
            this.oUser = oUser;
            this.oCopyUser = oUser;
            this.actual_vac_days = oUser.actual_vac_days;
            this.prop_vac_days = oUser.prop_vac_days;
            this.prox_vac_days = oUser.prox_vac_days;
            aniversaryDay = oUser.benefits_date;
            birthday = oUser.birthday_n;
        },

        /**
         * inicializar datePicker para la vista gestion de vacaciones
         */
        initDatePicker(){
            oDateRangePickerForMyRequest = new SDateRangePicker();

            oDateRangePickerForMyRequest.setDateRangePicker(
                'two-inputs-myRequest',
                this.initialCalendarDate,
                this.oUser.payment_frec_id,
                oServerData.const.QUINCENA,
                'date-range200-myRequest',
                'date-range201-myRequest',
                'clear',
                oServerData.lHolidays
            );
        },

        /**
         * Limpiar valores para la vista gestion de vacaciones
         */
        cleanData(){
            this.tempData = [];
            this.oUser = null;
            this.oCopyUser = null;
            this.actual_vac_days = null;
            this.prop_vac_days = null;
            this.prox_vac_days = null;
            this.renderTableMyVacations = false;
            this.renderTableMyRequest = false;
            this.takedNoBussinesDay = false;
            this.originalDaysTaked = 0;
            this.lNoBussinesDay = [];
            this.noBussinesDayIndex = 0;
        },

        /**
         * Crear datatable vacationsTable en la vista gestion de vacaciones
         */
        createVacationsTable(data){
            table['vacationsTable'] = $('#vacationsTable').DataTable({
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
                "ordering": true,
                "bSort": false,
                "colReorder": false,
                "order": [1, 'desc'],
                "columnDefs": [
                    {
                        "targets": [],
                        "visible": false,
                        "searchable": false,
                        "orderable": false,
                    },
                    {
                        "targets": [],
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
                "searching": false,
                "paging": false,
                "dom": 'Bfrtip',
                "initComplete": function(){ 
                    $("#vacationsTable").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
                },
            });
            this.reDrawVacationsTable(data);
            this.renderTableMyVacations = true;
        },

        /**
         * crear datatable table_myRequest en la vista gestion de vacaciones 
         */
        createMyRequestTable(data){
            table['table_myRequest'] = $('#table_myRequest').DataTable({
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
                "ordering": true,
                "bSort": false,
                "colReorder": false,
                "order": [1, 'desc'],
                "columnDefs": [
                    {
                        "targets": [0,2,3,4,5,6,17],
                        "visible": false,
                        "searchable": false,
                        "orderable": false,
                    },
                    {
                        "targets": [1],
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
                "initComplete": function() {
                    $("#table_myRequest").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");

                    $('#table_myRequest tbody').on('click', 'tr', function () {
                        if ($(this).hasClass('selected')) {
                            $(this).removeClass('selected');
                        }
                        else {
                            table['table_myRequest'].$('tr.selected').removeClass('selected');
                            $(this).addClass('selected');
                        }
                    });

                    /**
                     * Editar un registro con vue modal
                     */
                    $('#btn_edit').click(function () {
                        if (table['table_myRequest'].row('.selected').data() == undefined) {
                            SGui.showError("Debe seleccionar un renglón");
                            return;
                        }
                
                        app.showModal(table['table_myRequest'].row('.selected').data());
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
                        if (table['table_myRequest'].row('.selected').data() == undefined) {
                            SGui.showError("Debe seleccionar un renglón");
                            return;
                        }
                        app.deleteRegistry(table['table_myRequest'].row('.selected').data());
                    });

                    /**
                    * Enviar un registro con vue
                    */
                    $('#btn_send').click(function  () {
                        if (table['table_myRequest'].row('.selected').data() == undefined) {
                            SGui.showError("Debe seleccionar un renglón");
                            return;
                        }
                        app.sendRegistry(table['table_myRequest'].row('.selected').data());
                    });
                },
            });
            this.reDrawRequestTable(data.oUser);
            this.renderTableMyRequest = true;
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

        async showModal(data = null){
            $('#clear').trigger('click');
            await this.getEmpApplicationsEA(this.oUser.id);
            this.vacationUtils.createClass(this.lTemp);
            if(data != null){
                this.newData = false;
                await this.getlDays(data[this.indexes.id]);
                this.valid = (data[this.indexes.request_status_id] == this.oData.const.APPLICATION_ENVIADO || 
                                data[this.indexes.request_status_id] == this.oData.const.APPLICATION_APROBADO ||
                                    data[this.indexes.request_status_id] == this.oData.const.APPLICATION_RECHAZADO) ?
                                        false :
                                            true;
                dateRangePickerValid = this.valid;
                if(!this.valid){
                    SGui.showMessage('', 'No se puede editar una solicitud con estatus: '+data[this.indexes.status], 'warning');
                }
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
                this.returnDate = data[this.indexes.return_date];
                this.takedDays = data[this.indexes.taked_days];
                this.noBussinesDayIndex = 0;
                this.totCalendarDays = this.lDays.length;
                this.showDatePickerSimple = false;
                datepicker.setDate(moment(this.returnDate, 'ddd DD-MMM-YYYY').format("DD-MM-YYYY"));
                // this.reMaplDays();
                // this.lDays = result[2];
            }else{
                if(this.HasRequestCreated()){
                    SGui.showMessage('', 'No puede crear otra solicitud de vacaciones si tiene solicitudes creadas pendientes de enviar', 'warning');
                    return;
                }
                this.newData = true;
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
                this.noBussinesDayIndex = 0;
                dateRangePickerValid = this.valid;
                this.showDatePickerSimple = false;
                datepicker.setDate({ clear: !0 });
                // $('#clear').trigger('click');
                $('#two-inputs-myRequest').data('dateRangePicker').redraw();
            }
            $('#modal_Mysolicitud').modal('show');
        },

        getDataDays(){
            if(this.newData){
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
                this.originalDaysTaked = result[1];
                this.lDays = result[2];
                this.totCalendarDays = result[3];
                this.lNoBussinesDay = result[4];
                this.noBussinesDayIndex = 0;
            }
        },

        setTakedDay(index, checkbox_id){
            let checked = $('#' + checkbox_id).is(":checked");
            this.lDays[index].taked = checked;
            checked ? this.takedDays++ : this.takedDays--;
        },

        formatDate(sDate){
            return moment(sDate).format('ddd DD-MM-YYYY');
        },

        checkSpecial(){
            let message = "";
            let is_special = false;
            this.is_normal = true;
            this.is_past = false;
            this.is_advanced = false;
            this.is_proportional = false;
            this.is_season_special = false;

            if(this.takedDays > this.oUser.tot_vacation_remaining && this.takedDays <= (this.oUser.tot_vacation_remaining + Math.floor(parseInt(this.oUser.prop_vac_days)))){
                message = message + "Se utilizarán días proporcionales para la solicitud.\n";
                is_special = true;
                this.is_normal = false;
                this.is_proportional = true;
                this.lTypes.push('Con días proporcionales');
            }

            if(this.takedDays > (this.oUser.tot_vacation_remaining + Math.floor(parseInt(this.oUser.prop_vac_days)))){
                message = message + "Se utilizarán más días de los proporcionales para la solicitud.\n";
                is_special = true;
                this.is_normal = false;
                this.is_advanced = true;
                this.lTypes.push('Con más días de los proporcionales');
            }

            if(moment(this.endDate, 'ddd DD-MMM-YYYY').isBefore(moment(this.today)) || moment(this.endDate, 'ddd DD-MMM-YYYY').isSame(moment(this.today)) || moment(this.startDate, 'ddd DD-MMM-YYYY').isBefore(moment(this.today)) || moment(this.startDate, 'ddd DD-MMM-YYYY').isSame(moment(this.today))){
                message = message + "Se tomarán días pasados.\n";
                is_special = true;
                this.is_normal = false;
                this.is_past = true;
                this.lTypes.push('Con días pasados');
            }

            for(let oSeason of this.lTemp){
                for(let day of oSeason.lDates){
                    if(moment(day, 'YYYY-MM-DD').isBetween(moment(this.startDate, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD'), moment(this.endDate, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD'), undefined, '[]')){
                        message = message + 'Estas tomando días en temporada especial ' + oSeason.name + "\n";
                        is_special = true;
                        this.is_normal = false;
                        this.is_season_special = true;
                        this.lTypes.push('Con días en temporada especial');
                        break;
                    }
                }
            }

            if(this.is_normal){
                this.lTypes.push('Normal');
            }

            return [is_special, message];
        },

        specialType(data){
            let type = "";
            if(data.is_normal){
                type = type + "Normal\n";
            }

            if(data.is_past){
                type = type + "Días pasados\n";
            }

            if(data.is_advanced){
                type = type + "Días adelantados.\n";
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

        async requestVac(){
            if(this.startDate == null || this.startDate == '' || this.endDate == null || this.endDate == ''){
                SGui.showMessage('', 'Debe ingresar las fecha de inicio y fin de vacaciones', 'warning');
                return;
            }

            if(this.takedDays < 1){
                SGui.showMessage('', 'No se puede crear una solicitud con cero días efectivos de vacaciones', 'warning');
                return;
            }

            // let check = this.checkSpecial();
            // if(check[0]){
            //     if(!(await SGui.showConfirm(check[1], '¿Desea continua?', 'warning'))){
            //         return;
            //     }
            // }

            if(this.idRequest == null){
                var route = this.oData.requestVacRoute;
            }else{
                if(this.status != 'CREADO'){
                    SGui.showMessage('','Solo se pueden editar solicitudes con el estatus CREADO', 'warning');
                    return;
                }
                var route = this.oData.updateRequestVacRoute;
            }

            let copylDays = structuredClone(this.lDays);
            for (let index = 0; index < copylDays.length; index++) {
                copylDays[index].date = moment(copylDays[index].date, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD');
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
                'is_normal': this.is_normal,
                'is_past': this.is_past,
                'is_advanced': this.is_advanced,
                'is_proportional': this.is_proportional,
                'is_season_special': this.is_season_special,
                'lDays': copylDays,
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    this.actual_vac_days = data.oUser.actual_vac_days;
                    this.prox_vac_days = data.oUser.prox_vac_days;
                    this.prop_vac_days = data.oUser.prop_vac_days;
                    $('#modal_Mysolicitud').modal('hide');
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
                        rec.id_application_vs_type,
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
                        this.specialType(rec),
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
                    this.startDate = data[this.indexes.start_date];
                    this.endDate = data[this.indexes.end_date];
                    this.comments = data[this.indexes.comments];
                    this.idRequest = data[this.indexes.id];
                    this.status = data[this.indexes.status];
                    this.noBussinesDayIndex = 0;
                    this.getDataDays();
                    this.takedDays = data[this.indexes.taked_days];
                    // this.reMaplDays();
                    this.sendRequest(data[this.indexes.id]);
                }
            })
        },

        sendRequest(request_id){
            SGui.showWaiting(15000);
            axios.post(this.oData.sendRequestRoute, {
                'id_application': request_id,
                'year': this.year,
                'returnDate': moment(this.returnDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                'employee_id': this.oUser.id,
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    this.actual_vac_days = data.oUser.actual_vac_days;
                    this.prox_vac_days = data.oUser.prox_vac_days;
                    SGui.showMessage('', data.message, data.icon);
                    this.oCopyUser = data.oUser;
                    this.reDrawVacationsTable(data);
                    this.reDrawRequestTable(data.oUser);
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
                    // dateRangePickerArraySpecialSeasons = data.arrSpecialSeasons;
                    this.applicationsEA = data.arrAplications;
                    swal.close();
                    resolve(dateRangePickerArrayApplications);
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

        getlDays(id){
            SGui.showWaiting(3000);
            return new Promise((resolve) => 
            axios.post(this.oData.getlDaysRoute, {
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
                    swal.close();
                    resolve(data.lDays);
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

        getHistoryVac(table_id){
            SGui.showWaiting(10000);
            axios.post(this.oData.getMyVacationHistoryRoute, {
                'user_id':  this.oUser.id
            })
            .then(response => {
                let data = response.data;
                if(data.success){
                    this.oReDrawTables.reDrawVacationsTable(table_id, data);
                    swal.close();
                }else{
                    swal.close();
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function (error){
                console.log(error);
                swal.close();   
            });
        },

        hiddeHistory(table_id){
            SGui.showWaiting(10000);
            axios.post(this.oData.hiddeHistoryRoute, {
                'user_id':  this.oUser.id
            })
            .then(response => {
                let data = response.data;
                if(data.success){
                    this.oReDrawTables.reDrawVacationsTable(table_id, data);
                    swal.close();
                }else{
                    swal.close();
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function (error){
                console.log(error);
                swal.close();   
            });
        },

        sendAprove(data){
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
                    SGui.showWaiting(15000);
                    axios.post(this.oData.quickSendRoute, {
                        'id_application': data[this.indexes.id],
                    })
                    .then( result => {
                        let res = result.data;
                        if(res.success){
                            axios.post(this.oData.acceptRequestRoute, {
                                'id_application': data[this.indexes.id],
                                'id_user': this.oUser.id,
                                'comments': this.comments,
                                'year': this.year,
                                'returnDate': moment(this.returnDate, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD'),
                                'manager_id': this.selectedmanager,
                            })
                            .then(response => {
                                let data = response.data;
                                if(data.success){
                                    this.checkMail(data.mail_log_id, this.oData.checkMailRoute);
                                    axios.post(this.oData.quickDataRoute, {
                                        'user_id': this.oUser.id,
                                        'year': this.year,
                                    })
                                    .then( result => {
                                        data = result.data;
                                        if(data.success){
                                            this.reDrawVacationsTable(data);
                                            this.reDrawRequestTable(data.oUser);
                                            SGui.showOk();
                                        }else{
                                            SGui.showMessage('', data.message, data.icon);
                                        }
                                    })
                                    .catch( function(error){
                                        console.log(error);
                                        SGui.showError(error);
                                    });
                                }else{
                                    SGui.showMessage('', data.message, data.icon);
                                }
                            })
                            .catch(function(error) {
                                console.log(error);
                                SGui.showError(error);
                            });
                        }else{
                            SGui.showMessage('', res.message, res.icon);
                        }
                    })
                    .catch( function(error){

                    });
                }
            })
        }
    },
})
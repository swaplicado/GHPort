var app = new Vue({
    el: '#directVacation',
    data: {
        oData: oServerData,
        oDateUtils: new SDateUtils(),
        vacationUtils: new vacationUtils(),
        indexes: oServerData.indexesMyRequestTable,
        oUser: null,
        lEmployees: oServerData.lEmployees,
        employee_id: null,
        lTemp: [],
        tempData: null,
        year: null,
        initialCalendarDate: null,
        oUser: null,
        renderTableMyRequest: false,
        actual_vac_days: null,
        prop_vac_days: null,
        prox_vac_days: null,
        aniversaryDay: null,
        birthday: null,
        startDate: null,
        endDate: null,
        returnDate: null,
        comments: null,
        idRequest: null,
        status: null,
        takedDays: 0,
        lDays: [],
        lRec: [],
        valid: true,
        totCalendarDays: 0,
        showDatePickerSimple: false,
        loadReturnDate: false,
        showDatePickerSimple: false,
        lTypes: [],
        oApplication: null,
        lHolidays: oServerData.lHolidays,
        MyReturnDate: null,
        newData: false,
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
        propertyAAndPropertyB(newVal, oldVal) {
            this.newData = true;
            let oldlTypes = structuredClone(this.lTypes);
            this.lTypes = [];
            if(this.endDate != null && this.endDate != undefined && this.endDate != ""){
                let res = this.checkSpecial();
                if(res[0] && !this.arraysEqual(this.lTypes, oldlTypes)){
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
                this.getReturnDate();
            }
        },
    },
    updated: function(){
        this.$nextTick(function (){
            if(typeof self.$refs.table_myRequest != 'undefined' && !self.renderTableMyRequest){
                this.createVacationsTable(this.tempData);
                this.createMyRequestTable(this.tempData);
                createDatePicker();
                createDateRangePicker();
            }
        });
    },
    mounted(){
        self = this;
        $('.select2-class').select2({});

        $('#selectEmp').select2({
            placeholder: 'selecciona colaborador',
            data: self.lEmployees,
        }).on('select2:select', function(e) {
            self.employee_id = e.params.data.id;
        });

        $('#selectEmp').val('').trigger('change');
    },
    methods:{
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
                    $('#btn_sendAprov').click(function  () {
                        if (table['table_myRequest'].row('.selected').data() == undefined) {
                            SGui.showError("Debe seleccionar un renglón");
                            return;
                        }
                        app.sendAndAuthorize(table['table_myRequest'].row('.selected').data());
                    });
                },
            });
            this.reDrawRequestTable(data.oUser);
            this.renderTableMyRequest = true;
        },

        arraysEqual(a, b) {
            if (a.length !== b.length) {
                return false;
            }
            return a.every(element => b.includes(element));
        },

        checkSpecial(){
            let message = "";
            let is_special = false;
            this.is_normal = true;
            this.is_past = false;
            this.is_advanced = false;
            this.is_proportional = false;
            this.is_season_special = false;
            let lMessages = [];

            if(this.takedDays > this.oUser.tot_vacation_remaining && this.takedDays <= (this.oUser.tot_vacation_remaining + Math.floor(parseInt(this.oUser.prop_vac_days)))){
                is_special = true;
                this.is_normal = false;
                this.is_proportional = true;
                this.lTypes.push('Con días proporcionales');

                lMessages.push("Se utilizarán días proporcionales para la solicitud.");
            }

            if(this.takedDays > (this.oUser.tot_vacation_remaining + Math.floor(parseInt(this.oUser.prop_vac_days)))){
                is_special = true;
                this.is_normal = false;
                this.is_advanced = true;
                this.lTypes.push('Con más días de los proporcionales');

                lMessages.push("Se utilizarán más días de los proporcionales para la solicitud.");
            }

            if(moment(this.endDate, 'ddd DD-MMM-YYYY').isBefore(moment(this.today)) || moment(this.endDate, 'ddd DD-MMM-YYYY').isSame(moment(this.today)) || moment(this.startDate, 'ddd DD-MMM-YYYY').isBefore(moment(this.today)) || moment(this.startDate, 'ddd DD-MMM-YYYY').isSame(moment(this.today))){
                is_special = true;
                this.is_normal = false;
                this.is_past = true;
                this.lTypes.push('Con días pasados');

                lMessages.push("Se tomarán días pasados.");
            }

            for(let oSeason of this.lTemp){
                for(let day of oSeason.lDates){
                    if(moment(day, 'YYYY-MM-DD').isBetween(moment(this.startDate, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD'), moment(this.endDate, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD'), undefined, '[]')){
                        is_special = true;
                        this.is_normal = false;
                        this.is_season_special = true;
                        this.lTypes.push('Con días en temporada especial');

                        lMessages.push('Estas tomando días en temporada especial ' + oSeason.name);
                        break;
                    }
                }
            }

            if(this.lDays[0].bussinesDay == false && this.lDays[0].taked == false){
                is_special = true;
                this.is_normal = false;
                this.lTypes.push('inicio de vacaciones');

                lMessages.push("La fecha inicial del periodo de la incidencia es inhábil, si requieres la fecha puedes marcarla en el desglose de los días de calendario, si no, presiona limpiar");
            }

            if(this.lDays[this.lDays.length - 1].bussinesDay == false && this.lDays[this.lDays.length - 1].taked == false){
                is_special = true;
                this.is_normal = false;
                this.lTypes.push('fin de vacaciones');

                lMessages.push("La fecha final del periodo de la incidencia es inhábil, si requieres la fecha puedes marcarla en el desglose de los días de calendario, si no, presiona limpiar");
            }

            if(this.is_normal){
                this.lTypes.push('Normal');
            }

            message = "<ol>";
            for (let i = 0; i < lMessages.length; i++) {
                message = message + "<li>" + lMessages[i] + "</li>";
            }
            message = message + "</ol>";

            return [is_special, message, lMessages];
        },

        async getReturnDate(){
            this.loadReturnDate = true;
            let route = this.oData.calcReturnDate;
            axios.post(route,{
                'user_id': this.oUser.id,
                'start_date': moment(this.startDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                'end_date': moment(this.endDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                'application_id': this.idRequest,
            })
            .then(result => {
                let data = result.data;
                if(data.success){
                    this.returnDate = this.oDateUtils.formatDate(data.returnDate, 'ddd DD-MMM-YYYY');
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
                this.loadReturnDate = false;
            })
            .catch(function(error){
                console.log(error);
                SGui.showError(error);
                this.loadReturnDate = false;
            });
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
                if(this.status != 'Nuevas'){
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
                'is_event': false,
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
                    this.oUser = data.oUser;
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

        editMyReturnDate(){
            datepicker.setDate({ clear: !0 });
            if(this.endDate != null && this.endDate != undefined && this.endDate != ''){
                datepicker.setOptions({minDate: moment(this.endDate, 'ddd DD-MMM-YYYY').add(1, 'days').format("DD-MM-YYYY")});
            }else{
                datepicker.setOptions({minDate: null});
            }
            this.showDatePickerSimple  = true;
        },

        setMyReturnDate(){
            if(this.endDate != null && this.endDate != undefined && this.endDate != ''){
                this.MyReturnDate = datepicker.getDate('dd-mm-yyyy');
                this.returnDate = this.oDateUtils.formatDate(this.MyReturnDate, 'ddd DD-MMM-YYYY');
            }
            this.showDatePickerSimple  = false;
        },

        getEmployeeData(){
            SGui.showWaiting(15000);
            this.cleanData();
            let route = this.oData.getEmployeeDataRoute;

            axios.post(route, {
                'employee_id': this.employee_id,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.initValuesForUser(data);
                    this.renderTableMyRequest = false;
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(e) {
                console.log(e);
                SGui.showError();
            })
        },

        initValuesForUser(data){
            this.lTemp = data.lTemp;
            this.tempData = data;
            this.year = data.year;
            this.initialCalendarDate = data.initialCalendarDate;
            this.oUser = data.oUser;

            this.actual_vac_days = data.oUser.actual_vac_days;
            this.prop_vac_days = data.oUser.prop_vac_days;
            this.prox_vac_days = data.oUser.prox_vac_days;
            aniversaryDay = data.oUser.benefits_date;
            birthday = data.oUser.birthday_n;
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
                        ((
                            rec.request_status_id == this.oData.const.APPLICATION_CONSUMIDO ||
                            rec.request_status_id == this.oData.const.APPLICATION_APROBADO
                                ) ?
                                this.oDateUtils.formatDate(rec.approved_date_n, 'ddd DD-MMM-YYYY') :
                                    ((rec.request_status_id == this.oData.const.APPLICATION_RECHAZADO) ?
                                        this.oDateUtils.formatDate(rec.rejected_date_n, 'ddd DD-MMM-YYYY') :
                                            '')),
                        this.oDateUtils.formatDate(rec.start_date, 'ddd DD-MMM-YYYY'),
                        this.oDateUtils.formatDate(rec.end_date, 'ddd DD-MMM-YYYY'),
                        this.oDateUtils.formatDate(rec.return_date, 'ddd DD-MMM-YYYY'),
                        rec.total_days,
                        this.specialType(rec),
                        (rec.applications_st_name == 'CONSUMIDO' ? 'APROBADO' : rec.applications_st_name),
                        rec.sup_comments_n,
                    ]
                );
            }
            table['table_myRequest'].clear().draw();
            table['table_myRequest'].rows.add(dataReq).draw();
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

        filterMyVacationTable(){
            table['table_myRequest'].draw();
        },

        cleanData(){
            this.tempData = [];
            this.oUser = null;
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
                    this.oUser = data.oUser;
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

        deleteRegistry(data){
            if(data[this.indexes.status] != 'Nuevas'){
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

        HasRequestCreated(){
            for(let rec of this.oUser.applications){
                if(rec.request_status_id == 1){
                    return true;
                }
            }

            return false;
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
                this.takedDays = result[1];
                this.originalDaysTaked = result[1];
                this.lDays = result[2];
                this.totCalendarDays = result[3];
                this.lNoBussinesDay = result[4];
                this.noBussinesDayIndex = 0;
            }
        },

        async showModal(data = null){
            $('#clear').trigger('click');
            await this.getEmpApplicationsEA(this.oUser.id);
            this.vacationUtils.createClass(this.lTemp);
            if(data != null){
                this.newData = false;
                await this.getlDays(data[this.indexes.id]);
                this.valid = (data[this.indexes.request_status_id] == this.oData.const.APPLICATION_ENVIADO || 
                                data[this.indexes.request_status_id] == this.oData.const.APPLICATION_CONSUMIDO ||
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

        sendAndAuthorize(data){
            if(data[this.indexes.status] != 'Nuevas'){
                SGui.showMessage('','Solo se pueden enviar y autorizar solicitudes con el estatus Nueva', 'warning');
                return
            }

            let message = '<b>Inicio:</b> ' + data[this.indexes.start_date] +
                            '<br>' +
                            '<b>Fin:</b> ' +  data[this.indexes.end_date] +
                            '<br>' +
                            '<b>Se enviará y autorizará</b>';

            Swal.fire({
                title: '¿Desea enviar y autorizar la solicitud para las fechas?',
                html: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.acceptRequest(data[this.indexes.id]);
                }
            })
        },

        acceptRequest(request_id){
            let route = this.oData.directVacationsApprobeRoute;

            axios.post(route, {
                'id_application': request_id,
                'id_user': this.oUser.id
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(error){
                console.log(error);
                SGui.showError(error);
            });
        }
    }
});
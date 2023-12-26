var app = new Vue({
    el: '#eventsApp',
    data: {
        oData: oServerData,
        oDateUtils: new SDateUtils(),
        vacationUtils: new vacationUtils(),
        indexesEventsTable: oServerData.indexesEventsTable,
        lHolidays: oServerData.lHolidays,
        initialCalendarDate: oServerData.initialCalendarDate,
        lEvents: oServerData.lEvents,
        lTemp: [],
        valid: true,
        cardType: 'events',
        isEdit: false,
        startDate: null,
        endDate: null,
        lDays: [],
        totCalendarDays: null,
        eventName: null,
        takedDays: null,
        priority: 1,

        idEvent: null,

        assignBy: 'employee',
        idEventToAssign: null,
        lGroupsNoAssigned: [],
        lGroupsAssigned: [],
        lEmployeesNoAssigned: [],
        lEmployeesAssigned: [],
        indexesEmpNoAssign: oServerData.indexesEmpNoAssign,
        indexesEmpAssign: oServerData.indexesEmpAssign,
        indexesGroupNoAssign: oServerData.indexesGroupNoAssign,
        indexesGroupAssign: oServerData.indexesGroupAssign,
    },
    mounted(){
        self = this;
    },
    methods: {
        /**Metodo para el cambio de pestaña entre eventos y asignacion de eventos */
        changeCard(card, idBtn){
            btnActive(idBtn); //funcion de js en la vista para poner activo el boton de pestaña seleccionado
            this.cardType = card;
        },

        /**
         * Metodos para la pestaña eventos
         */

        /**Desencadena el limpiar los datos en el calendario y en consecuencia los datos de vue */
        clearData(){
            $('#clear').trigger('click');
            this.eventName = null;
        },

        /**Dibuja la tabla de eventos */
        drawEventsTable(table_name, lEvents){
            var dataEvents = [];
            for(let event of lEvents){
                dataEvents.push(
                    [
                        event.idEvent,
                        event.nameEvent,
                        this.oDateUtils.formatDate(event.sDate, 'ddd DD-MMM-YYYY'),
                        this.oDateUtils.formatDate(event.eDate, 'ddd DD-MMM-YYYY'),
                        event.priority
                    ]
                );
            }
            table[table_name].clear().draw();
            table[table_name].rows.add(dataEvents).draw();
        },

        /**Muestra el modal de la pestaña eventos, crear y editar */
        showModal(data){
            this.clearData();
            initCalendar(
                this.initialCalendarDate,
                false,
                false,
                2,
                [],
                oServerData.lHolidays,
                null,
                null,
                true,
            );
            
            if(data != null){
                this.isEdit = true;
                this.idEvent = data[this.indexesEventsTable.id_event];
                this.eventName = data[this.indexesEventsTable.event];
                this.priority = data[this.indexesEventsTable.priority];
                $('#date-range-001').val(moment(data[this.indexesEventsTable.startDate], 'ddd DD-MMM-YYYY').format('YYYY-MM-DD')).trigger('change');
                $('#date-range-002').val(moment(data[this.indexesEventsTable.endDate], 'ddd DD-MMM-YYYY').format('YYYY-MM-DD')).trigger('change');
            }else{
                this.isEdit = false;
            }

            $('#modal_events').modal('show');
        },

        /**Obtiene los lDays, dias calendario, dias efectivos*/
        getDataDays(){
            var result = this.vacationUtils.getTakedDays(
                            this.lHolidays,
                            2,
                            moment(this.startDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                            moment(this.endDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                            this.oData.constants,
                            false,
                            false
                        );

            this.takedDays = result[1];
            this.lDays = result[2];
            this.totCalendarDays = result[3];
        },

        /**Guarda un evento nuevo o editado */
        saveEvent(){
            SGui.showWaiting(15000);
            let route;
            if(this.isEdit){
                route = this.oData.eventUpdateRoute;
            }else{
                route = this.oData.eventSaveRoute;
            }

            let copylDays = structuredClone(this.lDays);
            for (let index = 0; index < copylDays.length; index++) {
                copylDays[index].date = moment(copylDays[index].date, 'ddd DD-MMM-YYYY').format('YYYY-MM-DD');
            }

            axios.post(route, {
                'idEvent': this.idEvent,
                'name': this.eventName,
                'startDate': moment(this.startDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                'endDate': moment(this.endDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                'lDays': copylDays,
                'priority': this.priority,
                'takedDays': this.totCalendarDays,
                'returnDate': this.endDate,
                'tot_calendar_days': this.totCalendarDays,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.lEvents = data.lEvents;
                    this.drawEventsTable('events_table', this.lEvents);
                    SGui.showOk();
                    $('#modal_events').modal('hide');
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function(error){
                console.log(error);
                SGui.showError(error);
            });
        },

        /**Metodo para poner como seleccionado un dia inhabil de la seccion despliegue de dias del modal de eventos*/
        setTakedDay(index, checkbox_id){
            let checked = $('#' + checkbox_id).is(":checked");
            this.lDays[index].taked = checked;
            checked ? this.takedDays++ : this.takedDays--;
        },

        /**Metodo para confirmar eliminar grupo */
        deleteRegistry(data){
            Swal.fire({
                title: '¿Desea eliminar el evento?',
                html: '<b>' + data[this.indexesEventsTable.event] + '</b> ',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deleteEvent(data[this.indexesEventsTable.id_event]);
                }
            })
        },

        /**Metodo para eliminar un grupo */
        deleteEvent(idEvent){
            SGui.showWaiting(15000);

            let route = this.oData.eventDeleteRoute;
            axios.post(route, {
                'idEvent': idEvent,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.lEvents = data.lEvents;
                    this.drawEventsTable('events_table', this.lEvents);
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

        /******************************************************************************************************************* */
        /**
         * Metodos para la asignacion de eventos
         */

        clearDataToAssign(){
            this.idEventToAssign = null;
            this.lGroupsNoAssigned = [];
            this.lGroupsAssigned = [];
            this.lEmployeesNoAssigned = [];
            this.lEmployeesAssigned = [];
            table['groupsNoAssignTable'].clear().draw();
            table['groupsAssignTable'].clear().draw();
            table['employeesNoAssignTable'].clear().draw();
            table['employeesAssignTable'].clear().draw();
            this.assignBy = 'employee';
        },

        /**Muestra el modal de asignacion de eventos */
        showModalEventAssign(){
            if (table['events_table'].row('.selected').data() == undefined) {
                SGui.showError("Debe seleccionar un renglón");
                return;
            }
            this.clearDataToAssign();
            
            let data = table['events_table'].row('.selected').data();
            this.idEventToAssign = data[this.indexesEventsTable.id_event];
            this.getEventAssigned();
            this.btnAssignActive('bEmployee');
            $('#modal_events_assign').modal('show');
        },

        /**Dibuja la tabla de asignacion de eventos */
        drawEventsAssignTable(table_name, lEventsAssign){
            var dataEventsAssign = [];
            for(let eventAssign of lEventsAssign){
                dataEventsAssign.push(
                    [
                        eventAssign.id_event,
                        eventAssign.name,
                        eventAssign.assignedTo
                    ]
                );
            }
            table[table_name].clear().draw();
            table[table_name].rows.add(dataEventsAssign).draw();
        },

        /**Dibuja la tabla de empleados no asignados */
        drawNoAssignEmployees(lEmployeesNoAssigned){
            var dataEmployeesNoAssigned = [];
            for(let emp of lEmployeesNoAssigned){
                dataEmployeesNoAssigned.push(
                    [
                        emp.id_employee,
                        emp.employee,
                    ]
                );
            }
            table['employeesNoAssignTable'].clear().draw();
            table['employeesNoAssignTable'].rows.add(dataEmployeesNoAssigned).draw();
        },

        /**Dibuja la tabla de empleados asignados */
        drawAssignEmployees(lEmployeesAssigned){
            var dataEmployeesAssigned = [];
            for(let emp of lEmployeesAssigned){
                dataEmployeesAssigned.push(
                    [
                        emp.id_employee,
                        emp.employee,
                    ]
                );
            }
            table['employeesAssignTable'].clear().draw();
            table['employeesAssignTable'].rows.add(dataEmployeesAssigned).draw();
        },

        /**Dibuja la tabla de grupos no asignados */
        drawNoAssignGroups(lGroupsNoAssigned){
            var dataGroupsNoAssigned = [];
            for(let group of lGroupsNoAssigned){
                dataGroupsNoAssigned.push(
                    [
                        group.id_group,
                        group.group,
                    ]
                );
            }
            table['groupsNoAssignTable'].clear().draw();
            table['groupsNoAssignTable'].rows.add(dataGroupsNoAssigned).draw();
        },

        /**Dibuja la tabla de grupos asignados */
        drawAssignGroups(lGroupsAssigned){
            var dataGroupsAssigned = [];
            for(let group of lGroupsAssigned){
                dataGroupsAssigned.push(
                    [
                        group.id_group,
                        group.group,
                    ]
                );
            }
            table['groupsAssignTable'].clear().draw();
            table['groupsAssignTable'].rows.add(dataGroupsAssigned).draw();
        },

        /**Metodo que obtiene las asignaciones de un evento,
         * obtiene los grupos y los usuarios asignados al evento
         */
        getEventAssigned(){
            SGui.showWaiting(15000);
            let route = this.oData.getAssignedRoute;

            axios.post(route, {
                'idEvent': this.idEventToAssign,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.lGroupsNoAssigned = data.lGroupsNoAssigned;
                    this.lGroupsAssigned = data.lGroupsAssigned;
                    this.lEmployeesNoAssigned = data.lEmployeesNoAssigned;
                    this.lEmployeesAssigned = data.lEmployeesAssigned;

                    this.drawNoAssignEmployees(this.lEmployeesNoAssigned);
                    this.drawAssignEmployees(this.lEmployeesAssigned);
                    this.drawNoAssignGroups(this.lGroupsNoAssigned);
                    this.drawAssignGroups(this.lGroupsAssigned);

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

        /**Metodo para pasar los renglones seleccionados de la tabla employeesNoAssignTable a la tabla employeesAssignTable*/
        passToEmpAssign(isAll){
            let data;
            if(isAll){
                data = table['employeesNoAssignTable'].rows().data().toArray();
            }else{
                data = table['employeesNoAssignTable'].rows({ selected: true }).data().toArray();
            }

            data.forEach(item => {
                this.lEmployeesAssigned.push(
                    {
                        'id_employee': item[this.indexesEmpAssign.id_employee],
                        'employee': item[this.indexesEmpAssign.employee]
                    }
                );

                let index = this.lEmployeesNoAssigned.findIndex(emp => emp.id_employee == item[this.indexesEmpAssign.id_employee])
                this.lEmployeesNoAssigned.splice(index, 1); 
            });

            this.lEmployeesAssigned.sort((a, b) => {
                const nameA = a.employee.toUpperCase();
                const nameB = b.employee.toUpperCase();
                if(nameA < nameB){
                    return -1;
                }
                if(nameA > nameB){
                    return 1;
                }
                return 0
            });

            table['employeesNoAssignTable'].search('');
            table['employeesAssignTable'].search('');

            this.drawNoAssignEmployees(this.lEmployeesNoAssigned);
            this.drawAssignEmployees(this.lEmployeesAssigned);
        },

        /**Metodo para pasar los renglones seleccionados de la tabla employeesAssignTable a la tabla employeesNoAssignTable*/
        passToEmpNoAssign(isAll){
            let data;
            if(isAll){
                data = table['employeesAssignTable'].rows().data().toArray();
            }else{
                data = table['employeesAssignTable'].rows({ selected: true }).data().toArray();
            }

            data.forEach(item => {
                this.lEmployeesNoAssigned.push(
                    {
                        'id_employee': item[this.indexesEmpNoAssign.id_employee],
                        'employee': item[this.indexesEmpNoAssign.employee]
                    }
                );

                let index = this.lEmployeesAssigned.findIndex(emp => emp.id_employee == item[this.indexesEmpNoAssign.id_employee])
                this.lEmployeesAssigned.splice(index, 1); 
            });

            this.lEmployeesNoAssigned.sort((a, b) => {
                const nameA = a.employee.toUpperCase();
                const nameB = b.employee.toUpperCase();
                if(nameA < nameB){
                    return -1;
                }
                if(nameA > nameB){
                    return 1;
                }
                return 0
            });

            table['employeesNoAssignTable'].search('');
            table['employeesAssignTable'].search('');

            this.drawNoAssignEmployees(this.lEmployeesNoAssigned);
            this.drawAssignEmployees(this.lEmployeesAssigned);
        },

        /**Metodo para pasar los renglones seleccionados de la tabla groupsNoAssignTable a la tabla groupsAssignTable*/
        passToGroupAssign(isAll){
            let data;
            if(isAll){
                data = table['groupsNoAssignTable'].rows().data().toArray();
            }else{
                data = table['groupsNoAssignTable'].rows({ selected: true }).data().toArray();
            }

            data.forEach(item => {
                this.lGroupsAssigned.push(
                    {
                        'id_group': item[this.indexesGroupAssign.id_group],
                        'group': item[this.indexesGroupAssign.group]
                    }
                );

                let index = this.lGroupsNoAssigned.findIndex(group => group.id_group == item[this.indexesGroupAssign.id_group])
                this.lGroupsNoAssigned.splice(index, 1);
            });

            this.lGroupsAssigned.sort((a, b) => {
                const nameA = a.group.toUpperCase();
                const nameB = b.group.toUpperCase();
                if(nameA < nameB){
                    return -1;
                }
                if(nameA > nameB){
                    return 1;
                }
                return 0
            });

            table['groupsNoAssignTable'].search('');
            table['groupsAssignTable'].search('');

            this.drawNoAssignGroups(this.lGroupsNoAssigned);
            this.drawAssignGroups(this.lGroupsAssigned);
        },

        /**Metodo para pasar los renglones seleccionados de la tabla groupsAssignTable a la tabla groupsNoAssignTable*/
        passToGroupNoAssign(isAll){
            let data;
            if(isAll){
                data = table['groupsAssignTable'].rows().data().toArray();
            }else{
                data = table['groupsAssignTable'].rows({ selected: true }).data().toArray();
            }

            data.forEach(item => {
                this.lGroupsNoAssigned.push(
                    {
                        'id_group': item[this.indexesGroupNoAssign.id_group],
                        'group': item[this.indexesGroupNoAssign.group]
                    }
                );

                let index = this.lGroupsAssigned.findIndex(group => group.id_group == item[this.indexesGroupNoAssign.id_group])
                this.lGroupsAssigned.splice(index, 1);
            });

            this.lGroupsNoAssigned.sort((a, b) => {
                const nameA = a.group.toUpperCase();
                const nameB = b.group.toUpperCase();
                if(nameA < nameB){
                    return -1;
                }
                if(nameA > nameB){
                    return 1;
                }
                return 0
            });

            table['groupsNoAssignTable'].search('');
            table['groupsAssignTable'].search('');

            this.drawNoAssignGroups(this.lGroupsNoAssigned);
            this.drawAssignGroups(this.lGroupsAssigned);
        },

        /**metodo para cambiar de pestaña de asignar por empleados a asignar por grupos */
        changeAssign(assignBy, idBtn){
            this.assignBy = assignBy;
            this.btnAssignActive(idBtn);
        },

        /**metodo para activar el boton al cambiar de pestaña */
        btnAssignActive(id) {
            const btn_ids = ['bGroup', 'bEmployee'];

            let btn = document.getElementById(id);
            btn.style.backgroundColor = '#858796';
            btn.style.color = '#fff';
    
            for (const bt_id of btn_ids) {
                if (bt_id != id) {
                    let bt = document.getElementById(bt_id);
                    bt.style.backgroundColor = '#fff';
                    bt.style.color = '#858796';
                    bt.style.boxShadow = '0 0 0';
                }
            }
        },

        setAssignEmployee(){
            SGui.showWaiting(15000);
            let route = this.oData.saveAssignUserRoute;

            axios.post(route, { 
                'idEvent': this.idEventToAssign,
                'lEmployeesAssigned': this.lEmployeesAssigned,
                'lEmployeesNoAssigned': this.lEmployeesNoAssigned
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
                
        },

        setAssignGroup(){
            SGui.showWaiting(15000);
            let route = this.oData.saveAssignGroupRoute;
            axios.post(route, {
                'idEvent': this.idEventToAssign,
                'lGroupsAssigned': this.lGroupsAssigned,
                'lGroupsNoAssigned': this.lGroupsNoAssigned,
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
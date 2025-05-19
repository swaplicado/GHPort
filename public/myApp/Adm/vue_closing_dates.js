var app = new Vue({
    el: '#closingDatesApp',
    data: {
        oData: oServerData,
        initialCalendarDate: oServerData.initialCalendarDate,
        lDates: oServerData.lDates,
        indexes_closeDates: oServerData.indexes_closeDates,
        indexesUsers: oServerData.indexesUsers,
        indexesUsersAssign: oServerData.indexesUsersAssign,
        showCalendar: false,
        is_singleDate: false,
        oDateUtils: new SDateUtils(),
        vacationUtils: new vacationUtils(),
        startDate: null,
        endDate: null,
        lTemp:[],
        returnDate: null,
        totCalendarDays: null,
        takedDays: null,
        showDatePickerSimple: false,
        idCloseDate: 0,
        isEdit: 0,
        lTypes: oServerData.lTypes,
        type_id: oServerData.lTypes[0].id,
        lUsers: [],
        lUsersAssigned: [],
        showListUsers: false,
        is_global: true,
    },
    mounted(){
        self = this;
        let dataType = [];
        for (let i = 0; i < this.lTypes.length; i++) {
            dataType.push({ id: this.lTypes[i].id, text: this.lTypes[i].name });
        }
        
        $('#closing_date_type').select2({
            placeholder: 'Selecciona tipo',
            data: dataType,
        }).on('select2:select', function(e) {
            self.type_id = e.params.data.id;
        });
    },
    methods: {
        
        createCalendar(){
            $('#clear').trigger('click');
            this.is_singleDate = false;
            initCalendar(
                this.initialCalendarDate,
                false,
                false,
                2,
                this.lTemp,
                [],
                '1985-01-01',
                '1985-01-01',
                true,
            );
        },

        async showModal(data = null){
            SGui.showWaiting();
            this.is_global = true;
            $('#clear').trigger('click');
            this.cleanData();
            if(data != null){
                this.isEdit = true;
                this.idCloseDate = data[this.indexes_closeDates.id_closing_dates];
                this.startDate = data[this.indexes_closeDates.date_ini];
                this.endDate = data[this.indexes_closeDates.date_end];
                $('#date-range-001').val(this.startDate);
			    $('#date-range-002').val(this.endDate);
                this.createCalendar();
                this.showCalendar = true;
                this.startDate = this.oDateUtils.formatDate(data[this.indexes_closeDates.date_ini], 'ddd DD-MMM-YYYY');
                this.endDate = this.oDateUtils.formatDate(data[this.indexes_closeDates.date_end], 'ddd DD-MMM-YYYY');
                $('#date-range-001').val(this.startDate).trigger('change');
			    $('#date-range-002').val(this.endDate).trigger('change');
                Swal.close();

                if (! parseInt(data[this.indexes_closeDates.is_global])) {
                    this.is_global = false;
                    this.reDrawUsersTable(this.lUsers);
                    this.reDrawUsersAssignTable(this.lUsersAssigned);
                    this.getlUsers( data ? data[this.indexes_closeDates.id_closing_dates] : null );
                    table['table_users'].search('');
                    table['table_users_assigned'].search('');
                }
                $('#createModal').modal('show');
            }else{
                Swal.close();
                this.createCalendar()
                $('#createModal').modal('show');
            }
        },
        setApplication(route){

            axios.post(route, {
                'id_closedate': this.idCloseDate,
                'startDate': moment(this.startDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                'endDate': moment(this.endDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                'type_id': this.type_id,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.reDrawTableClosing('table_dates', data.lDates);
                    SGui.showOk();
                    $('#editModal').modal('hide');
                    $('#createModal').modal('hide');
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


            SGui.showWaiting(15000);

            if(this.isEdit && this.is_global){
                this.setApplication(this.oData.createRoute);
            }else if(this.is_global){
                this.setApplication(this.oData.createRoute);
            }

            if (!this.is_global) {
                this.createClosingDataUser(this.oData.createClosingDatesUsersRoute);
            }
        },

        cleanData(){
            // this.lTemp = oServerData.lTemp;
            this.startDate = null;
            this.endDate = null;
            this.idCloseDate = null;
            this.isEdit = false;
            this.is_global = true;
            // this.isRevision = false;
        },

        reDrawTableClosing(table_name, lDates){
            var dataDates = [];
            for(let date of lDates){
                dataDates.push(
                    [
                        date.id_closing_dates,
                        date.is_global,
                        this.oDateUtils.formatDate(date.start_date, 'ddd DD-MMM-YYYY'),
                        this.oDateUtils.formatDate(date.end_date, 'ddd DD-MMM-YYYY'),
                        date.name,
                        date.is_global ? 'Sí' : 'No'
                    ]
                );
            }
            table[table_name].clear().draw();
            table[table_name].rows.add(dataDates).draw();
        },

        deleteRegistry(data){

            Swal.fire({
                title: '¿Desea eliminar las fechas ' + data[this.indexes_closeDates.id_closing_dates] + ' ?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deleteIncidence(data[this.indexes_closeDates.id_closing_dates]);
                }
            })
        },

        deleteIncidence(application_id){
            SGui.showWaiting(15000);
            axios.post(this.oData.deleteRoute, {
                'application_id': application_id,
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    this.reDrawTableClosing('table_dates', data.lDates);
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

        getlUsers(closingDates_id){
            let route = this.oData.getlUsersRoute;
            SGui.showWaiting();
            axios.post(route, {
                'closingDates_id': closingDates_id
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    this.lUsers = data.lUsers;
                    this.lUsersAssigned = data.lUsersAssigned;
                    this.reDrawUsersTable(this.lUsers);
                    this.reDrawUsersAssignTable(this.lUsersAssigned);
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

        showAssignModal(data){
            $('#clear').trigger('click');
            this.cleanData();
            this.is_global = false;
            this.lUsers = [];
            this.lUsersAssigned = [];
            this.closingDate_id = data ? data[this.indexes_closeDates.id_closing_dates] : null;
            this.reDrawUsersTable(this.lUsers);
            this.reDrawUsersAssignTable(this.lUsersAssigned);
            this.getlUsers( data ? data[this.indexes_closeDates.id] : null );
            table['table_users'].search('');
            table['table_users_assigned'].search('');
            this.createCalendar()
            $('#createModal').modal('show');
            // $('#modal_assign').modal('show');
        },

        reDrawUsersTable(lUsers){
            var dataUsers = [];
            for(let user of lUsers){
                dataUsers.push(
                    [
                        user.id,
                        user.full_name_ui
                    ]
                );
            }
            table['table_users'].clear().draw();
            table['table_users'].rows.add(dataUsers).draw();
        },

        reDrawUsersAssignTable(lUsersAssigned){
            var dataUsersAssign = [];
            for(let user of lUsersAssigned){
                dataUsersAssign.push(
                    [
                        user.id,
                        user.full_name_ui
                    ]
                );
            }
            table['table_users_assigned'].clear().draw();
            table['table_users_assigned'].rows.add(dataUsersAssign).draw();
        },

        passTolUsers(){
            if (table['table_users_assigned'].row('.selected').data() == undefined) {
                SGui.showError("Debe seleccionar un renglón");
                return;
            }
            var data = table['table_users_assigned'].row('.selected').data();

            this.lUsers.push({'id': data[this.indexesUsersAssign.id], 'full_name_ui': data[this.indexesUsersAssign.full_name_ui]});
            const index = this.lUsersAssigned.findIndex(({ id }) => id == data[this.indexesUsersAssign.id]);
            this.lUsersAssigned.splice(index, 1);
            this.lUsers.sort((a, b) => {
                const nameA = a.full_name_ui.toUpperCase();
                const nameB = b.full_name_ui.toUpperCase();
                if (nameA < nameB) {
                    return -1;
                }
                if (nameA > nameB) {
                    return 1;
                }
                return 0;
            });
            table['table_users'].search('');
            table['table_users_assigned'].search('');
            this.reDrawUsersAssignTable(this.lUsersAssigned);
            this.reDrawUsersTable(this.lUsers);
        },

        passTolUsersAssign(){
            if (table['table_users'].row('.selected').data() == undefined) {
                SGui.showError("Debe seleccionar un renglón");
                return;
            }
            var data = table['table_users'].row('.selected').data();

            this.lUsersAssigned.push({'id': data[this.indexesUsers.id], 'full_name_ui': data[this.indexesUsers.full_name_ui]});
            const index = this.lUsers.findIndex(({ id }) => id == data[this.indexesUsers.id]);
            this.lUsers.splice(index, 1);
            this.lUsersAssigned.sort((a, b) => {
                const nameA = a.full_name_ui.toUpperCase();
                const nameB = b.full_name_ui.toUpperCase();
                if (nameA < nameB) {
                    return -1;
                }
                if (nameA > nameB) {
                    return 1;
                }
                return 0;
            });
            table['table_users'].search('');
            table['table_users_assigned'].search('');
            this.reDrawUsersAssignTable(this.lUsersAssigned);
            this.reDrawUsersTable(this.lUsers);
        },

        passAllTolUsersAssign(){
            var data = table['table_users'].rows().data().toArray();
            
            data.forEach(row => {
                this.lUsersAssigned.push({'id': row[this.indexesUsers.id], 'full_name_ui': row[this.indexesUsers.full_name_ui]});
                const index = this.lUsers.findIndex(({ id }) => id == row[this.indexesUsers.id]);
                this.lUsers.splice(index, 1);
                this.lUsersAssigned.sort((a, b) => {
                    const nameA = a.full_name_ui.toUpperCase();
                    const nameB = b.full_name_ui.toUpperCase();
                    if (nameA < nameB) {
                        return -1;
                    }
                    if (nameA > nameB) {
                        return 1;
                    }
                    return 0;
                });
            });

            table['table_users'].search('');
            table['table_users_assigned'].search('');
            this.reDrawUsersAssignTable(this.lUsersAssigned);
            this.reDrawUsersTable(this.lUsers);
        },
 
        passAllTolUsers(){
            var data = table['table_users_assigned'].rows().data().toArray();

            data.forEach(row => {
                this.lUsers.push({'id': row[this.indexesUsersAssign.id], 'full_name_ui': row[this.indexesUsersAssign.full_name_ui]});
                const index = this.lUsersAssigned.findIndex(({ id }) => id == row[this.indexesUsersAssign.id]);
                this.lUsersAssigned.splice(index, 1);
                this.lUsers.sort((a, b) => {
                    const nameA = a.full_name_ui.toUpperCase();
                    const nameB = b.full_name_ui.toUpperCase();
                    if (nameA < nameB) {
                        return -1;
                    }
                    if (nameA > nameB) {
                        return 1;
                    }
                    return 0;
                });
            })

            table['table_users'].search('');
            table['table_users_assigned'].search('');
            this.reDrawUsersAssignTable(this.lUsersAssigned);
            this.reDrawUsersTable(this.lUsers);
        },

        createClosingDataUser(route) {
            axios.post(route, {
                'closingDates_id': this.idCloseDate,
                'startDate': moment(this.startDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                'endDate': moment(this.endDate, 'ddd DD-MMM-YYYY').format("YYYY-MM-DD"),
                'type_id': this.type_id,
                'lUsersAssigned': this.lUsersAssigned
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    this.reDrawTableClosing('table_dates', data.lDates);
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function(error) {
                console.log(error);
                SGui.showError(error);
            });
        }
    }
});
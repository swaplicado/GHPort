var app = new Vue({
    el: '#closingDatesApp',
    data: {
        oData: oServerData,
        initialCalendarDate: oServerData.initialCalendarDate,
        lDates: oServerData.lDates,
        indexes_closeDates: oServerData.indexes_closeDates,
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
        isEdit: 0
    },
    mounted(){
        self = this;
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

            if(this.isEdit){
                this.setApplication(this.oData.createRoute);
            }else{
                this.setApplication(this.oData.createRoute);
            }
        },

        cleanData(){
            // this.lTemp = oServerData.lTemp;
            this.startDate = null;
            this.endDate = null;
            this.idCloseDate = null;
            this.isEdit = false;
            // this.isRevision = false;
        },

        reDrawTableClosing(table_name, lDates){
            var dataDates = [];
            for(let date of lDates){
                dataDates.push(
                    [
                        date.id_closing_dates,
                        this.oDateUtils.formatDate(date.start_date, 'ddd DD-MMM-YYYY'),
                        this.oDateUtils.formatDate(date.end_date, 'ddd DD-MMM-YYYY')
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
    }
});
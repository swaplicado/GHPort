var app = new Vue({
    el: '#vacationsPlans',
    data: {
        oData: oServerData,
        lVacationPlans: oServerData.lVacationPlans, //No modificar el valor de lVacationPlans
        indexes: oServerData.indexes,
        indexesUsers: oServerData.indexesUsers,
        indexesUsersAssign: oServerData.indexesUsersAssign,
        rowCount: 0,
        onlyShow: false,
        disabledSave: false,
        name: null,
        payment_frec: 0,
        unionized: false,
        start_date: null,
        years: [{'year': 1, 'days': ''}],
        copyYears: null,
        idVacPlan: null,
        lUsers: [],
        lUsersAssigned: [],
        vacation_plan_name: null,
        vacation_plan_id: null,
    },
    mounted(){
        
    },
    methods: {
        showModal(data = null){
            if(data == null){
                this.idVacPlan = null;
                this.onlyShow = false;
                this.rowCount = 0;
                this.years = [{'year': 1, 'days': ''}];
                this.name = null;
                this.payment_frec = 0;
                this.unionized = false;
                this.start_date = null;
                this.copyYears = JSON.parse(JSON.stringify(this.years));
            }else{
                SGui.showWaiting(15000);
                this.getDataVacPlan(data);
                this.onlyShow = false;
                this.rowCount = this.years.length;
                this.name = data[this.indexes.vacation_plan_name];
                this.payment_frec = data[this.indexes.payment_frec_id_n] != '' ? data[this.indexes.payment_frec_id_n] : 0;
                this.unionized = data[this.indexes.is_unionized_n] == 1 ? true : false;
                this.start_date = data[this.indexes.start_date_n];
                this.copyYears = JSON.parse(JSON.stringify(this.years));
                this.idVacPlan = data[this.indexes.id];
            }
            $('#modal_vacation_plan').modal('show');
        },

        getDataVacPlan(data){
            axios.post(this.oData.showVacationRoute, {
                'vacation_plan_id': data[this.indexes.id],
            })
            .then(response => {
                var oVacation = response.data;
                if(oVacation.success){
                    this.years = [];
                    for(let plan of oVacation.vacationPlanDays){
                        var value = {'year': plan.until_year, 'days': plan.vacation_days};
                        this.years.push(value);
                    }
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

        showDataModal(data){
            SGui.showWaiting(15000);
            axios.post(this.oData.showVacationRoute, {
                'vacation_plan_id': data[this.indexes.id],
            })
            .then(response => {
                var oVacation = response.data;
                if(oVacation.success){
                    this.onlyShow = true;
                    this.name = data[this.indexes.vacation_plan_name];
                    this.payment_frec = data[this.indexes.payment_frec_id_n] != '' ? data[this.indexes.payment_frec_id_n] : 0;
                    this.unionized = data[this.indexes.is_unionized_n] == 0 ? false : true;
                    this.start_date = data[this.indexes.start_date_n];
                    this.years = [];
                    
                    for(let plan of oVacation.vacationPlanDays){
                        var value = {'year': plan.until_year, 'days': plan.vacation_days};
                        this.years.push(value);
                    }
                    $('#modal_vacation_plan').modal('show');
                    SGui.showOk();
                }else{
                    this.onlyShow = false;
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function(error) {
                this.onlyShow = false;
                console.log(error);
                SGui.showError(error);
            });
        },

        addRow(){
            if(this.rowCount < 50){
                this.rowCount++;
                var value = {'year': parseInt(this.years[this.rowCount - 1].year) + 1, 'days': ''};
                this.years.push(value);
                this.copyYears = JSON.parse(JSON.stringify(this.years));
            }
        },

        removeRow(index){
            this.years.splice(index, 1);
            this.rowCount--;
            this.copyYears = JSON.parse(JSON.stringify(this.years));
        },

        recalcRows(index){
            this.years[index].year = Math.floor(Math.abs(this.years[index].year));
            if(parseInt(this.years[index].year) <= 50){
                if(parseInt(this.copyYears[index - 1].year) < parseInt(this.years[index].year)){
                    for(index; index < this.years.length; index++){
                        if(this.years[index + 1] != undefined){
                            this.years[index + 1].year = parseInt(this.years[index].year) + 1;
                        }
                    }
                    this.copyYears = JSON.parse(JSON.stringify(this.years));
                }else{
                    this.years = JSON.parse(JSON.stringify(this.copyYears));
                    SGui.showMessage('','No se puede insertar un año menor o igual al año anterior','info');
                }
            }else{
                this.years = JSON.parse(JSON.stringify(this.copyYears));
                SGui.showMessage('','No se puede insertar un año mayor a 50','info');
            }
            this.$mount();
        },

        checkDayBefore(index){
            this.years[index].days = Math.floor(Math.abs(this.years[index].days));
            if(this.years[index].days > 50){
                this.years[index].days = 50;
            }
            if(this.years[index - 1] != undefined){
                if(this.years[index - 1].days != ''){
                    if(parseInt(this.copyYears[index - 1].days) <= parseInt(this.years[index].days)){
                        return true;
                    }else{
                        SGui.showMessage('','No se puede insertar numero de dias menor al año anterior','info');
                        return false;
                    }
                }else{
                    SGui.showMessage('','Debe ingresar el número de días del renglón anterior','info');
                    return false;
                }
            }else{
                return true;
            }
        },

        checkDayAfter(index){
            this.years[index].days = Math.floor(Math.abs(this.years[index].days));
            if(this.years[index].days > 50){
                this.years[index].days = 50;
            }
            if(this.years[index + 1] != undefined){
                if(this.years[index + 1].days != ''){
                    if(parseInt(this.copyYears[index + 1].days) >= parseInt(this.years[index].days)){
                        return true;
                    }else{
                        SGui.showMessage('','No se puede insertar numero de dias mayor al año siguiente','info');
                        return false;
                    }
                }else{
                    return true;
                }
            }else{
                return true;
            }
        },

        checkDataBeforeSave(){
            for(var i = 0; i < this.years.length; i++){
                if(i > 0){
                    if(parseInt(this.years[i].year) <= parseInt(this.years[i - 1].year)){
                        return false;
                    }
                    if(parseInt(this.years[i].days) < parseInt(this.years[i - 1].days)){
                        return false;
                    }
                }
            }

            return true;
        },

        checkDays(index){
            if(this.checkDayBefore(index) && this.checkDayAfter(index)){
                this.copyYears = JSON.parse(JSON.stringify(this.years));
            }else{
                this.years = JSON.parse(JSON.stringify(this.copyYears));
            }
        },

        saveVacationPlan(){
            this.disabledSave = true;
            if(this.idVacPlan == null){
                var route = this.oData.saveRoute;
            }else{
                var route = this.oData.updateRoute;
            }
            if(this.name == null){
                SGui.showMessage('', 'Debe ingresar un nombre de plan de vacaciones', 'warning');
                this.disabledSave = false;
                return;
            }
            if(this.start_date == null){
                SGui.showMessage('', 'Debe ingresar una fecha de inicio de plan de vacaciones', 'warning');
                this.disabledSave = false;
                return;
            }
            for(let year of this.years){
                if(year.year == '' || year.year == null){
                    SGui.showMessage('', 'Debe llenar todos los campos de año', 'warning');
                    this.disabledSave = false;
                    return;
                }
                if(year.days == '' || year.days == null){
                    SGui.showMessage('', 'Debe llenar todos los campos de días', 'warning');
                    this.disabledSave = false;
                    return;
                }
            }
            SGui.showWaiting(15000);
            if(this.checkDataBeforeSave()){
                axios.post(route, {
                    'idVacPlan': this.idVacPlan,
                    'years': this.years,
                    'name': this.name,
                    'payment_frec': this.payment_frec,
                    'unionized': this.unionized,
                    'start_date': this.start_date
                })
                .then(response => {
                    this.disabledSave = false;
                    var data = response.data;
                    if(data.success){
                        $('#modal_vacation_plan').modal('hide');
                        SGui.showMessage('', data.message, data.icon);
                        this.reDrawVacPlanTable(data);
                        // this.lVacationPlans = data.lVacationPlans;
                    }else{
                        SGui.showMessage('', data.message, data.icon);
                    }
                })
                .catch(function(error) {
                    console.log(error);
                    SGui.showError(error);
                    this.disabledSave = false;
                });
            }
            this.disabledSave = false;
        },

        reDrawVacPlanTable(data){
            var dataPlan = [];
            for(let vac of data.lVacationPlans){
                dataPlan.push(
                    [
                        vac.id_vacation_plan,
                        vac.payment_frec_id_n,
                        vac.is_unionized_n,
                        vac.vacation_plan_name,
                        (vac.payment_frec_id_n == null || vac.payment_frec_id_n == '') ? 'AMBOS' : (vac.payment_frec_id_n == 1 ? 'SEMANA' : 'QUINCENA'),
                        vac.is_unionized_n == 1 ? 'SÍ' : 'NO',
                        vac.start_date_n
                    ]
                );
            }
            table['table_vacationsPlans'].clear().draw();
            table['table_vacationsPlans'].rows.add(dataPlan).draw();
        },

        showInfo(){
            Swal.fire(
                {
                    title: '',
                    text: 'Si años consecutivos tienen el mismo número de dias, ' + 
                            'no es necesario insertarlos todos, ' +
                            'solo debes insertar el año de inicio con el número de días ' +
                            'y el año final, por ejemplo: si el año 2 al 6 tienen 12 días de vacaciones ' +
                            'solo debes introducir el renglon del año 2 con sus 12 dias correspondientes ' +
                            'y el siguiente renglon con el año 6 con sus días correspondientes.',
                }
            );
        },

        deleteVacationPlan(id){
            axios.post(this.oData.deleteVacationRoute, {
                'vacation_plan_id': id,
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    SGui.showMessage('', data.message, data.icon);
                    this.reDrawVacPlanTable(data);
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
            Swal.fire({
                title: '¿Desea eliminar el registro?',
                html: data[this.indexes.vacation_plan_name],
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deleteVacationPlan(data[this.indexes.id]);
                }
            })
        },

        showAssignModal(data){
            this.lUsers = [];
            this.lUsersAssigned = [];
            this.vacation_plan_name = data[this.indexes.vacation_plan_name];
            this.vacation_plan_id = data[this.indexes.id];
            this.reDrawUsersTable(this.lUsers);
            this.reDrawUsersAssignTable(this.lUsersAssigned);
            this.getAssignedUsers(data[this.indexes.id]);
            table['table_users'].search('');
            table['table_users_assigned'].search('');
            $('#modal_assign').modal('show');
        },

        getAssignedUsers(vacation_plan_id){
            SGui.showWaiting(15000);
            axios.post(this.oData.getUsersAssignedRoute, {
                'vacation_plan_id': vacation_plan_id
            })
            .then( response => {
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
            })
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

        saveAssignVacationPlan(){
            SGui.showWaiting(15000);
            axios.post(this.oData.saveAssignVacationPlanRoute, {
                'lUsersAssigned': this.lUsersAssigned,
                'vacation_plan_id': this.vacation_plan_id
            })
            .then( response => {
                var data = response.data;
                if(data.success){
                    SGui.showOk();
                    $('#modal_assign').modal('hide');
                }else{
                    SGui.showMessage('',data.message,data.icon);
                }
            })
            .catch(function(error){
                console.log(error);
                SGui.showError(error);
            });
        }
    },
})
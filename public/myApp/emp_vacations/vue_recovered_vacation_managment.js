var app = new Vue({
    el: '#recoveredVacationsApp',
    data: {
        oData: oServerData,
        lUsers: oServerData.lUsers,
        indexes: oServerData.indexes,
        oUser: null,
        vacationsExpired: [],
        maxValue: 0,
        daysToRecover: 0,
    },
    watch: {
        daysToRecover:function(val) {
            this.daysToRecover  = Math.abs(this.daysToRecover);
        },
    },
    mounthed(){

    },
    methods:{
        showModal(data){
            this.oUser = this.lUsers.find(({ id }) => id == data[this.indexes.user_id]);
            if(this.oUser != null && this.oUser != undefined){
                this.vacationsExpired = this.oUser.vacationsExpired;
            }else{
                this.vacationsExpired = [];
            }
            this.setMax();
            this.reDrawRecoverTable(this.vacationsExpired);
            $('#modal_recover').modal('show');
        },

        setMax(){
            this.maxValue = 0;
            for(vac of this.oUser.vacationsExpired){
                this.maxValue = this.maxValue + vac.vacRemaining;
            }
        },

        checkIsSelectable(vac){
            if(vac.year != this.oUser.vacationsExpired[0].year){
                return 'noSelectableRow';
            }
        },

        saveRecovered(){
            if(this.daysToRecover > this.maxValue){
                SGui.showMessage('', 'No se pueden reactivar mas de ' + this.maxValue + ' días');
                return;
            }

            if(this.daysToRecover == 0){
                SGui.showMessage('', 'No se pueden reactivar menos de 1 día');
                return;
            }
            SGui.showWaiting(15000);
            axios.post(this.oData.saveRoute, {
                'user_id': this.oUser.id,
                'daysToRecover': this.daysToRecover,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.lUsers = data.lUsers;
                    this.oUser = this.lUsers.find(({ id }) => id == this.oUser.id);
                    if(this.oUser != null && this.oUser != undefined){
                        this.vacationsExpired = this.oUser.vacationsExpired;
                    }else{
                        this.vacationsExpired = [];
                    }
                    this.reDrawRecoverTable(this.vacationsExpired);
                    this.reDrawExpiredVacTable(this.lUsers);
                    this.daysToRecover = 0;
                    $('#modal_recover').modal('hide');
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(e){
                console.log(e);
                SGUi.showError(e);
            });
        },

        reDrawRecoverTable(data){
            var dataVac = [];
            for(vac of data){
                dataVac.push(
                    [
                        vac.id_vacation_user,
                        vac.year,
                        vac.vacation_days,
                        vac.consumedVac,
                        vac.vacRemaining,
                        vac.vacRecovered,
                    ]
                );
            }
            table['table_modal_expiredVac'].clear().draw();
            table['table_modal_expiredVac'].rows.add(dataVac).draw();
        },
        
        reDrawExpiredVacTable(data){
            var dataExp = [];
            for(exp of data){
                dataExp.push(
                    [
                        exp.id,
                        exp.full_name_ui,
                        exp.TotVacRemaining,
                        exp.TotVacRecovered
                    ]
                );
            }
            table['table_expiredVac'].clear().draw();
            table['table_expiredVac'].rows.add(dataExp).draw();
        },
    }
});
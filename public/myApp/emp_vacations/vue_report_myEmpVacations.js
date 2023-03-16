var app = new Vue({
    el: '#myEmpVacations',
    data: {
        oData: oServerData,
        lEmployees: oServerData.lEmployees,
        copylEmployees: structuredClone(oServerData.lEmployees),
        year: oServerData.year,
        period: 'Todo',
        seeLevel: 'Directos',
        lLevels: JSON.parse(oServerData.lLevels),
    },
    mounted(){
        
    },
    methods: {
        getLevelDown(){
            SGui.showWaiting(3000);
            axios.post(this.oData.getDownLevelRoute, {
                'lLevels': JSON.stringify(this.lLevels),
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.lLevels = data.lLevels;
                    this.seeLevel = this.lLevels[this.lLevels.length - 1].level + " nivel por debajo de tus directos";
                    for (const emp of data.lEmployees) {
                        this.copylEmployees.push(emp);
                    }
                    this.reDrawRequestTable(this.copylEmployees);
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function (error) {
                console.log(error);
                SGui.showError(error);
            })
        },

        getLevelUp(){
            SGui.showWaiting(7000);
            axios.post(this.oData.getUpLevelRoute, {
                'lLevels': JSON.stringify(this.lLevels),
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.lLevels = data.lLevels;
                    if(this.lLevels[this.lLevels.length - 1].level > 0){
                        this.seeLevel = this.lLevels[this.lLevels.length - 1].level + " nivel por debajo de tus directos";
                    }else{
                        this.seeLevel = "Directos";
                    }
                    this.copylEmployees = data.lEmployees;
                    this.reDrawRequestTable(this.copylEmployees);
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function (error) {
                console.log(error);
                SGui.showError(error);
            })
        },

        reDrawRequestTable(data){
            var dataEmp = [];
            for(let emp of data){
                dataEmp.push(
                    [
                        emp.id,
                        emp.employee,
                        emp.tot_vacation_days,
                        emp.tot_vacation_taken,
                        emp.tot_vacation_expired,
                        emp.tot_vacation_request,
                        emp.tot_vacation_remaining,
                    ]
                );
            }
            table['vacationsTable'].clear().draw();
            table['vacationsTable'].rows.add(dataEmp).draw();
        },

        filterYear(){
            SGui.showWaiting(15000);
            axios.post(this.oData.myEmpVacationsFilterYearRoute, {
                'lLevels': JSON.stringify(this.lLevels),
                'startYear': this.year,
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    this.period = this.year + ' - ' + data.nowYear;
                    this.copylEmployees = data.lEmployees;
                    this.reDrawRequestTable(this.copylEmployees);
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

        showCompletePeriod(){
            SGui.showWaiting(15000);
            axios.post(this.oData.myEmpVacationsFilterYearRoute, {
                'lLevels': JSON.stringify(this.lLevels),
                'startYear': null,
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    SGui.showOk();
                    this.period = 'Todo';
                    this.year = data.nowYear;
                    this.copylEmployees = data.lEmployees;
                    this.reDrawRequestTable(this.copylEmployees);
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
    },
})
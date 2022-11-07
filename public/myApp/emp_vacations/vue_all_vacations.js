var app = new Vue({
    el: '#allVacations',
    data: {
        oData: oServerData,
        lEmployees: oServerData.lEmployees,
        year: oServerData.year,
        period: 'Todo',
    },
    mounted(){
        
    },
    methods: {
        filterYear(){
            SGui.showWaiting(15000);
            axios.post(this.oData.getPeriodRoute, {
                'startYear': this.year,
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    SGui.showOk();
                    this.period = this.year;
                    this.lEmployees = data.lEmployees;
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
            axios.post(this.oData.getPeriodRoute, {
                'startYear': null,
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    SGui.showOk();
                    this.period = 'Todo';
                    this.year = moment().format('YYYY');
                    this.lEmployees = data.lEmployees;
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
var app = new Vue({
    el: '#allVacations',
    data: {
        oData: oServerData,
        lEmployees: oServerData.lEmployees,
        year: oServerData.year
    },
    mounted(){
        
    },
    methods: {
        filterYear(){
            SGui.showWaiting(5000);
            axios.post(this.oData.getPeriodRoute, {
                'startYear': this.year,
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    SGui.showOk();
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
            SGui.showWaiting(5000);
            axios.post(this.oData.getPeriodRoute, {
                'startYear': null,
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    SGui.showOk();
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
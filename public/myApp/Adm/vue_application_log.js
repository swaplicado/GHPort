var app = new Vue({
    el: '#logs',
    data: {
        oData: oServerData,
        logs: oServerData.logs,
        indexes: oServerData.indexes,
        lApplicationLogs: [],
    },
    mounted(){
        
    },
    methods: {
        showDataModal(data){
            this.lApplicationLogs = [];
            this.getApplicationLogsData(data);
            $('#modal_application_log').modal('show');
        },

        getApplicationLogsData(data){
            SGui.showWaiting(15000);
            axios.post(this.oData.getApplicationLogsDataRoute, {
                'application_id': data[this.indexes.id]
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    this.lApplicationLogs = data.applicationLogs;
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            }).
            catch(function(error){
                console.log(error);
                SGui.showError(error);
            });
        }
    },
})
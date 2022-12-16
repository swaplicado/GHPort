var app = new Vue({
    el: '#myEmplVac',
    data: {
        oData: oServerData,
        lEmployees: oServerData.lEmployees,
        oDateUtils: new SDateUtils(),
        oReDrawTables: new SReDrawTables()
    },
    mounted(){

    },
    methods: {
        getHistoryVac(table_id, user_id){
            SGui.showWaiting(5000);
            axios.post( this.oData.getVacationHistoryRoute, {
                'user_id': user_id
            })
            .then( result => {
                var data = result.data;
                if(data.success){
                    this.oReDrawTables.reDrawVacationsTable(table_id, data);
                    swal.close();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function(error){
                console.log(error);
                SGui.showError();
            })
        },

        hiddeHistory(table_id, user_id){
            SGui.showWaiting(10000);
            axios.post(this.oData.hiddeHistoryRoute, {
                'user_id':  user_id
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
        }
    }
})
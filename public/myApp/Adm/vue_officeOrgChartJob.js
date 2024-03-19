var app = new Vue({
    el: '#officeOrgChartJobApp',
    data: {
        oData: oServerData,
        lOrgChartJob: oServerData.lOrgChartJob,
    },
    mounted(){
        self = this;
    },
    methods: {
        updateOrgChartJob(area_id, name, checkBox){
            let checked = $('#'+checkBox).is(':checked');
            let route = this.oData.updateOrgChartJobRoute;
            
            axios.post(route, {
                'is_office': checked,
                'area_id': area_id,
            })
            .then(result => {
                let data = result.data;
                if(data.success){
                    createToast( name + ' ACTUALIZADO', data.toastType, 6000);
                }else{
                    createToast( name + ' NO ACTUALIZADO', data.toastType, 6000);
                    $('#'+checkBox).prop('checked', !checked);
                }
            })
            .catch(function (error) {
                console.log(error);
                createToast(name + ' NO ACTUALIZADO', 'error', 6000);
                $('#'+checkBox).prop('checked', !checked);
            })
        }
    }
});
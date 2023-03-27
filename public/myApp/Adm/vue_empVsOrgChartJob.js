var app = new Vue({
    el: '#empVsOrgChartJobApp',
    data: {
        oData: oServerData,
        indexes: oServerData.indexes,
        lUsers: oServerData.lUsers,
        lOrgChart: oServerData.lOrgChart,
        selOrgChart_id: null,
        FilterOrgChart: null,
        org_chart_job_id: null,
        user: null,
    },
    mounted(){
        let self = this;
        var datalAreas = [];
        datalAreas.push({id: '', text: ''});
        for(var i = 0; i<self.lOrgChart.length; i++){
            datalAreas.push({id: self.lOrgChart[i].id_org_chart_job, text: self.lOrgChart[i].job_name_ui});
        }

        var datalAreasFilter = structuredClone(datalAreas);
        datalAreasFilter[0] = {id: '', text: 'Todos'};
        datalAreasFilter.push({id: '0', text: 'DEF'});

        $('#selArea')
            .select2({
                placeholder: 'selecciona área',
                data: datalAreas,
            })
            .on('select2:select', function (e){
                self.selOrgChart_id = e.params.data.id;
            });

        $('#selAreaFilter')
            .select2({
                placeholder: 'selecciona área',
                data: datalAreasFilter,
            })
            .on('select2:select', function (e){
                self.FilterOrgChart = e.params.data.text;
                self.areaFilter();
            });
    },
    methods: {
        showModal(data){
            this.user = data[this.indexes.full_name_ui];
            this.org_chart_job_id = data[this.indexes.org_chart_job_id];

            $('#selArea').val(this.org_chart_job_id).trigger('change');

            $('#editModal').modal('show');
        },

        areaFilter(){
            if(this.FilterOrgChart == "Todos"){
                table['table_emp_vs_orgChart'].columns(this.indexes.job_name_ui).search("", true, true).draw();
            }else{
                table['table_emp_vs_orgChart'].columns(this.indexes.job_name_ui).search("(^" + this.FilterOrgChart + "$)", true, false).draw();
            }
        },

        update(){
            SGui.showWaiting(10000);
            let data = table['table_emp_vs_orgChart'].row('.selected').data();
            axios.post(this.oData.updateRoute, {
                'user_id': data[this.indexes.user_id],
                'selOrgChart_id': this.selOrgChart_id,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.lUsers = data.lUsers;
                    this.reDrawTable(data.lUsers);
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(error){
                console.log(error);
                SGui.showError(error);
            });
        },

        reDrawTable(data){
            let dataUser = [];
            for(let user of data){
                dataUser.push(
                    [
                        user.id,
                        user.org_chart_job_id,
                        user.top_org_chart_job_id_n,
                        user.full_name_ui,
                        user.job_name_ui,
                        user.job_name_ui_top,
                    ]
                );
            }
            table['table_emp_vs_orgChart'].clear().draw();
            table['table_emp_vs_orgChart'].rows.add(dataUser).draw();
        }
    }
});
var app = new Vue({
    el: '#jobVsArea',
    data: {
        oData: oServerData,
        lOrgChartJobs: oServerData.lOrgChartJobs,
        lJobs: oServerData.lJobs,
        lJobVsOrgChartJob: oServerData.lJobVsOrgChartJob,
        jobVsOrgChartJob_id: null,
        orgChart_id: null,
        positions: null,
        job_id: null,
        job: null,
        indexesTableAreas: oServerData.indexesTableAreas,
    },
    mounted(){
        let self = this;
        var datalAreas = [];
        datalAreas.push({id: '', text: ''});
        for(var i = 0; i<self.lOrgChartJobs.length; i++){
            datalAreas.push({id: self.lOrgChartJobs[i].id_org_chart_job, text: self.lOrgChartJobs[i].job_name});
        }

        $('#selArea')
            .select2({
                placeholder: 'selecciona nodo org.',
                data: datalAreas,
            })
            .on('select2:select', function (e){
                self.orgChart_id = e.params.data.id;
            });
    },
    methods: {
        showModal(data){
            if(data != null){
                this.jobVsOrgChartJob_id = data[this.indexesTableAreas.id];
                this.orgChart_id  = data[this.indexesTableAreas.org_chart_id];
                this.positions = data[this.indexesTableAreas.positions];
                this.job_id = data[this.indexesTableAreas.job_id];
                this.job = data[this.indexesTableAreas.job];
                $('#selArea').val(this.orgChart_id).trigger('change');
            }else{
                this.jobVsOrgChartJob_id = null;
                this.orgChart_id  = null;
                this.job = null;
            }
            $('#editModal').modal('show');
        },

        update(){
            if(this.positions < 1){
                SGui.showMessage('', 'El número de colaboradores no puede ser menor que 1', 'warning');
                return;
            }

            if(this.positions > 50){
                SGui.showMessage('', 'El número de colaboradores no puede ser mayor que 50', 'warning');
                return;
            }

            SGui.showWaiting(15000);
            axios.post(this.oData.updateRoute, {
                'jobVsOrgChartJob_id': this.jobVsOrgChartJob_id,
                'orgChart_id': this.orgChart_id,
                'positions': this.positions,
                'job_id': this.job_id,
                'job': this.job,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.reDrawAreasTable(data);
                    $('#editModal').modal('hide');
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

        reDrawAreasTable(data){
            var dataReq = [];
            for(let oJobOrg of data.lJobVsOrgChartJob){
                dataReq.push(
                    [
                        oJobOrg.id,
                        oJobOrg.id_org_chart_job,
                        oJobOrg.id_job,
                        oJobOrg.department,
                        oJobOrg.job,
                        oJobOrg.orgChart,
                        oJobOrg.positions,
                    ]
                );
            }
            table['table_areas'].clear().draw();
            table['table_areas'].rows.add(dataReq).draw();
        },
    }
});
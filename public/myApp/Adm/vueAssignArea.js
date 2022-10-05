var app = new Vue({
    el: '#assignArea',
    data: {
        oData: oServerData,
        lAreas: oServerData.lAreas,
        lUsers: oServerData.lUsers,
        area: null,
        area_id: null,
        superviser_id: null,
        top_org_chart_job_id: null,
    },
    mounted(){
        let self = this;
        var datalAreas = [];
        datalAreas.push({id: '', text: ''});
        for(var i = 0; i<self.lAreas.length; i++){
            datalAreas.push({id: self.lAreas[i].id_org_chart_job, text: self.lAreas[i].job_name});
        }
        $('#selUser')
            .select2({
                placeholder: 'selecciona usuario',
                data: self.lUsers,
            })
            .on('select2:select', function (e){
                self.superviser_id = e.params.data.id;
            });
            
        $('#selArea')
            .select2({
                placeholder: 'selecciona área',
                data: datalAreas,
            })
            .on('select2:select', function (e){
                self.top_org_chart_job_id = e.params.data.id;
            });
    },
    methods: {
        showModal(data){
            this.area_id = data[0];
            this.top_org_chart_job_id = data[1];
            this.superviser_id = data[2];
            this.area = data[3];

            $('#selUser').val(this.superviser_id).trigger('change');
            $('#selArea').val(this.top_org_chart_job_id).trigger('change');

            $('#editModal').modal('show');
        },

        save(){
            SGui.showWaiting(5000);
            axios.post(this.oData.updateRoute, {
                'org_chart_job': this.area_id,
                'top_org_chart_job_id': this.top_org_chart_job_id,
                'superviser_id': this.superviser_id,
            })
            .then(response => {
                let res = response.data;
                if(res.success){
                    $('#editModal').modal('hide');
                    SGui.showOk();
                    this.lAreas = res.lAreas;
                    var dataAreas = [];
                    for(let area of this.lAreas){
                        dataAreas.push(
                            [
                                area.id_org_chart_job,
                                area.top_org_chart_job_id_n,
                                area.head_user_id,
                                area.job_name,
                                area.head_user,
                                area.top_org_chart_job
                            ]
                        );
                    }
                    table.clear().draw();
                    table.rows.add(dataAreas).draw(); 
                    // table.rows.add(
                    //     [
                    //         [ 1, '', 1, 'area', 'res', 'sup' ],
                    //         [ 2, '', 2, 'area2', 'res2', 'sup2' ]
                    //     ]
                    //  ).draw(); 
                    // location.reload();s
                }else{
                    SGui.showError(res.message);
                }
            })
            .catch(function(error) {
                console.log(error);
                SGui.showError(error);
            });
        }
    },
})
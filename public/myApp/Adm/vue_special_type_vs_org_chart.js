var app = new Vue({
    el: '#specialTypeVsOrgChart',
    data: {
        oData: oServerData,
        indexes: oServerData.indexSpecialVsOrgChart,
        lSpecialTypeVsOrgChart: oServerData.lSpecialTypeVsOrgChart,
        lSpecialType: oServerData.lSpecialType,
        lUsers: oServerData.lUsers,
        lOrgChart: oServerData.lOrgChart,
        lCompanies: oServerData.lCompanies,
        specialName: null,
        id: null,
        cat_special_id: null,
        user_id: null,
        org_chart_job_id: null,
        company_id: null,
        assign_by: 0,
        specialName: null,
        option: null,
        lOptions: ['Área', 'Usuario', 'Empresa'],
        revisor_id: null,
    },
    mounted(){
        let self = this;
        self.revisor_id = 2;
        this.option = this.lOptions[0];
        var datalOptions = [];
        datalOptions.push({id: 0, text: 'Área'});
        datalOptions.push({id: 1, text: 'Usuario'});
        datalOptions.push({id: 2, text: 'Empresa'});

        $('#sol_type')
            .select2({
                placeholder: 'selecciona',
                data: self.lSpecialType,
            })
            .on('select2:select', function (e){
                self.cat_special_id = e.params.data.id;
            });

        $('#sel_option')
            .select2({
                placeholder: 'selecciona',
                data: self.lOrgChart,
            });

        $('#sel_revisor')
            .select2({
                placeholder: 'selecciona',
                data: self.lOrgChart,
            })
            .on('select2:select', function (e){
                self.revisor_id = e.params.data.id;
            });

        $('#assign_by')
            .select2({
                placeholder: 'selecciona',
                data: datalOptions,
            })
            .on('select2:select', function (e){
                self.assign_by = e.params.data.id;
                self.option = self.lOptions[self.assign_by];
                switch (parseInt(self.assign_by)) {
                    case 0:
                        $('#sel_option').empty().trigger("change");
                        self.user_id = null;
                        self.company_id = null;
                        $('#sel_option')
                            .select2({
                                placeholder: 'selecciona',
                                data: self.lOrgChart,
                            });
                        break;
                    case 1:
                        $('#sel_option').empty().trigger("change");
                        self.org_chart_job_id = null;
                        self.company_id = null;
                        $('#sel_option')
                            .select2({
                                placeholder: 'selecciona',
                                data: self.lUsers,
                            });
                        break;
                    case 2:
                        $('#sel_option').empty().trigger("change");
                        self.org_chart_job_id = null;
                        self.user_id = null;
                        $('#sel_option')
                            .select2({
                                placeholder: 'selecciona',
                                data: self.lCompanies,
                            });
                        break;
                    default:
                        break;
                }
            });
    },
    methods: {
        showModal(data){
            if(data == null || data == undefined){
                $('#sol_type').val('').trigger('change');
                this.id = null;
                this.user_id = null;
                this.org_chart_job_id = null;
                this.company_id = null;
                // this.revisor_id = null;
            }else{
                this.id = data[this.indexes.id];
                this.user_id = data[this.indexes.user_id];
                this.org_chart_job_id = data[this.indexes.org_chart_job_id];
                this.company_id = data[this.indexes.company_id];
                this.cat_special_id = data[this.indexes.cat_special_id];
                this.specialName = data[this.indexes.special_name];
                this.revisor_id = data[this.indexes.revisor_id];
                if(this.user_id != null && this.user_id != ""){
                    $('#assign_by').val(1).trigger('change');
                    $('#sel_option').val(this.user_id).trigger('change');
                }else if(this.org_chart_job_id != null && this.org_chart_job_id != ""){
                    $('#assign_by').val(0).trigger('change');
                    $('#sel_option').val(this.org_chart_job_id).trigger('change');
                }else if(this.company_id != null && this.company_id != ""){
                    $('#assign_by').val(3).trigger('change');
                    $('#sel_option').val(this.company_id).trigger('change');
                }
            }
            $('#modal_assign').modal('show');
        },

        save(){
            let route = null;
            if(this.id == null){
                route = this.oData.routeSave;
            }else{
                route = this.oData.routeUpdate;
            }

            let hasOption = false;
            switch (parseInt(this.assign_by)) {
                case 0:
                    this.org_chart_job_id = $('#sel_option').val();
                    if(this.org_chart_job_id != null && this.org_chart_job_id != ""){
                        hasOption = true;
                    }
                    break;
                case 1:
                    this.user_id = $('#sel_option').val();
                    if(this.user_id != null && this.user_id != ""){
                        hasOption = true;
                    }
                    break;
                case 2:
                    this.company_id = $('#sel_option').val();
                    if(this.company_id != null && this.company_id != ""){
                        hasOption = true;
                    }
                    break;
                default:
                    break;
            }

            if(this.cat_special_id == null || this.cat_special_id == ""){
                SGui.showMessage('', 'Debes seleccionar un tipo de solicitud', 'warning');
                return;
            }

            if(!hasOption){
                SGui.showMessage('', 'Debes seleccionar una opción para asignar', 'warning');
                return;
            }


            SGui.showWaiting(15000);

            axios.post(route, {
                'assign_by': this.assign_by,
                'id': this.id,
                'cat_special_id': this.cat_special_id,
                'user_id': this.user_id,
                'org_chart_job_id': this.org_chart_job_id,
                'company_id': this.company_id,
                'revisor_id': this.revisor_id,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.cat_special_id = null;
                    this.reDrawTableSpecialVsOrgChart(data.lSpecialTypeVsOrgChart);
                    $('#modal_assign').modal('hide');
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(error){
                this.cat_special_id = null;
                console.log(error);
                SGui.showError(error);
            })
        },

        reDrawTableSpecialVsOrgChart(data){
            var dataSpecial = [];
            for(let d of data){
                dataSpecial.push(
                    [
                        d.id,
                        d.cat_special_id,
                        d.user_id_n,
                        d.org_chart_job_id_n,
                        d.company_id_n,
                        d.revisor_id,
                        d.special_name,
                        d.user_name,
                        d.org_chart_name,
                        d.company_name,
                        d.revisor_name,
                    ]
                );
            }
            table['table_special_vs_org_chart'].clear().draw();
            table['table_special_vs_org_chart'].rows.add(dataSpecial).draw();
        },

        deleteRegistry(data){
            Swal.fire({
                title: '¿Desea eliminar la asignación?',
                html: '<b>Tipo solicitud:</b> ' +
                        data[this.indexes.special_name] +
                        '<br>' +
                        '<b>Asignado a:</b> ' +
                        data[this.indexes.user_name] +
                        " " +
                        data[this.indexes.org_chart_name] +
                        " " +
                        data[this.indexes.company_name],
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deleteSpecial(data[this.indexes.id]);
                }
            })
        },

        deleteSpecial(id){
            SGui.showWaiting(15000);

            axios.post( this.oData.routeDelete, {
                'id': id,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.reDrawTableSpecialVsOrgChart(data.lSpecialTypeVsOrgChart);
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(e){
                console.log(e);
                SGui.showError(e);
            })
        }
    }
})
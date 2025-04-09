var app = new Vue({
    el: '#assignArea',
    data: {
        oData: oServerData,
        lAreas: oServerData.lAreas,
        lUsers: oServerData.lUsers,
        lLevels: oServerData.lLevels,
        area: null,
        area_id: 0,
        superviser_id: null,
        top_org_chart_job_id: null,
        org_level_id: null,
        job_num: null,
        leader: 0,
        config_leader: 0,
    },
    mounted() {
        let self = this;
        var datalAreas = [];
        var datalLevels = [];
        datalAreas.push({ id: '', text: '' });
        datalLevels.push({ id: '', text: '' });
        for (var i = 0; i < self.lAreas.length; i++) {
            datalAreas.push({ id: self.lAreas[i].id_org_chart_job, text: self.lAreas[i].job_name });
        }

        for (var i = 0; i < self.lLevels.length; i++) {
            datalLevels.push({ id: self.lLevels[i].id_organization_level, text: self.lLevels[i].level + " - " + self.lLevels[i].name });
        }

        // $('#selUser')
        //     .select2({
        //         placeholder: 'selecciona usuario',
        //         data: self.lUsers,
        //     })
        //     .on('select2:select', function (e){
        //         self.superviser_id = e.params.data.id;
        //     });

        $('#selArea')
            .select2({
                placeholder: 'selecciona nodo org.',
                data: datalAreas,
            })
            .on('select2:select', function(e) {
                self.top_org_chart_job_id = e.params.data.id;
            });

        $('#selAreaC')
            .select2({
                placeholder: 'selecciona nodo org.',
                data: datalAreas,
            })
            .on('select2:select', function(e) {
                self.top_org_chart_job_id = e.params.data.id;
            });
        $('#selOrgC')
            .select2({
                placeholder: '',
                data: datalLevels,
            })
            .on('select2:select', function(e) {
                self.org_level_id = e.params.data.id;
            });

        $('#selOrgE')
            .select2({
                placeholder: '',
                data: datalLevels,

            })
            .on('select2:select', function(e) {
                self.org_level_id = e.params.data.id;
            });
    },
    methods: {
        showModal(data = null) {
            if (data == null) {
                this.area_id = 0;
                this.top_org_chart_job_id = null;
                this.org_level_id = null;
                this.superviser_id = null;
                this.area = null;
                this.job_num = 0;
                this.leader = 0;
                this.config_leader = 0;

                $('#createModal').modal('show');
            } else {
                this.area_id = data[0];
                this.top_org_chart_job_id = data[1];
                this.superviser_id = data[2];
                this.area = data[3];
                this.job_num = data[7];
                this.org_level_id = data[13];
                this.leader = parseInt(data[8]);
                this.config_leader = parseInt(data[9]);

                if (data[10] != 0) {
                    SGui.showMessage("Información", "Si modificas el nodo org. superior, se modificará toda la piramide inferior al nodo");
                }

                // $('#selUser').val(this.superviser_id).trigger('change');
                $('#selArea').val(this.top_org_chart_job_id).trigger('change');
                $('#nomArea').val(this.area).trigger('change');
                $('#numArea').val(this.job_num).trigger('change');
                $('#selOrgE').val(this.org_level_id).trigger('change');

                $('#editModal').modal('show');
            }
        },

        save() {
            if (this.area == null) {
                SGui.showError("El nombre del nodo org. no puede estar vacío");
                return false;
            }
            if (this.job_num == 0) {
                SGui.showError("No se pueden tener 0 puestos en un nodo org.");
                return false;
            }
            if (this.org_level_id == null) {
                SGui.showError("Tiene que tener un nivel jerárquico");
                return false;
            }
            SGui.showWaiting(5000);
            if (this.area_id != 0) {
                axios.post(this.oData.updateRoute, {
                        'area': this.area,
                        'org_chart_job': this.area_id,
                        'top_org_chart_job_id': this.top_org_chart_job_id,
                        'superviser_id': this.superviser_id,
                        'job_num': this.job_num,
                        'leader': this.leader,
                        'config_leader': this.config_leader,
                        'org_level_id': this.org_level_id,

                    })
                    .then(response => {
                        let res = response.data;
                        if (res.success) {
                            $('#editModal').modal('hide');
                            SGui.showOk();
                            this.lAreas = res.lAreas;
                            var dataAreas = [];
                            for (let area of this.lAreas) {
                                dataAreas.push(
                                    [
                                        area.id_org_chart_job,
                                        area.top_org_chart_job_id_n,
                                        area.head_user_id,
                                        area.job_name,
                                        area.head_user,
                                        area.top_org_chart_job,
                                        area.org_level_id,
                                        area.positions,
                                        area.is_boss,
                                        area.is_leader_config,
                                        area.childs,
                                        ((area.is_boss == 0) ? 'No' : 'Sí'),
                                        ((area.is_leader_config == 0) ? 'No' : 'Sí')
                                    ]
                                );
                            }
                            table['table_areas'].clear().draw();
                            table['table_areas'].rows.add(dataAreas).draw();
                            // table.rows.add(
                            //     [
                            //         [ 1, '', 1, 'area', 'res', 'sup' ],
                            //         [ 2, '', 2, 'area2', 'res2', 'sup2' ]
                            //     ]
                            //  ).draw(); 
                            // location.reload();s
                        } else {
                            SGui.showError(res.message);
                        }
                    })
                    .catch(function(error) {
                        console.log(error);
                        SGui.showError(error);
                    });
            } else {

                axios.post(this.oData.createRoute, {
                        'area': this.area,
                        'org_chart_job': this.area_id,
                        'top_org_chart_job_id': this.top_org_chart_job_id,
                        'superviser_id': this.superviser_id,
                        'job_num': this.job_num,
                        'leader': this.leader,
                        'config_leader': this.config_leader,
                        'org_level_id': this.org_level_id,
                    })
                    .then(response => {
                        let res = response.data;
                        if (res.success) {
                            $('#createModal').modal('hide');
                            SGui.showOk();
                            //this.lAreas = res.lAreas;
                            var dataAreas = [];
                            for (let area of res.lAreas) {
                                dataAreas.push(
                                    [
                                        area.id_org_chart_job,
                                        area.top_org_chart_job_id_n,
                                        area.head_user_id,
                                        area.job_name,
                                        area.head_user,
                                        area.top_org_chart_job,
                                        area.org_level,
                                        area.positions,
                                        area.is_boss,
                                        area.is_leader_config,
                                        area.childs,
                                        ((area.is_boss == 0) ? 'No' : 'Sí'),
                                        ((area.is_leader_config == 0) ? 'No' : 'Sí'),
                                        area.org_level_id,
                                    ]
                                );
                            }
                            table['table_areas'].clear().draw();
                            table['table_areas'].rows.add(dataAreas).draw();
                            // table.rows.add(
                            //     [
                            //         [ 1, '', 1, 'area', 'res', 'sup' ],
                            //         [ 2, '', 2, 'area2', 'res2', 'sup2' ]
                            //     ]
                            //  ).draw(); 
                            // location.reload();s
                        } else {
                            SGui.showError(res.message);
                        }
                    })
                    .catch(function(error) {
                        console.log(error);
                        SGui.showError(error);
                    });
            }
        },

        deleteRegistry(data) {
            if (data[10] != 0) {
                SGui.showError('No se puede eliminar un registro con nodos hijos, primero elimine todos los nodos hijos para poder proseguir');
            } else {
                axios.post(this.oData.deleteRoute, {
                        'org_chart_job': data[0],
                    })
                    .then(response => {
                        let res = response.data;
                        if (res.success) {
                            $('#createModal').modal('hide');
                            SGui.showOk();
                            //this.lAreas = res.lAreas;
                            var dataAreas = [];
                            for (let area of res.lAreas) {
                                dataAreas.push(
                                    [
                                        area.id_org_chart_job,
                                        area.top_org_chart_job_id_n,
                                        area.head_user_id,
                                        area.job_name,
                                        area.head_user,
                                        area.top_org_chart_job,
                                        area.org_level,
                                        area.positions,
                                        area.is_boss,
                                        area.is_leader_config,
                                        area.childs,
                                        ((area.is_boss == 0) ? 'No' : 'Sí'),
                                        ((area.is_leader_config == 0) ? 'No' : 'Sí'),
                                        area.org_level_id,
                                    ]
                                );
                            }
                            table['table_areas'].clear().draw();
                            table['table_areas'].rows.add(dataAreas).draw();
                            // table.rows.add(
                            //     [
                            //         [ 1, '', 1, 'area', 'res', 'sup' ],
                            //         [ 2, '', 2, 'area2', 'res2', 'sup2' ]
                            //     ]
                            //  ).draw(); 
                            // location.reload();s
                        } else {
                            SGui.showError(res.message);
                        }
                    })
                    .catch(function(error) {
                        console.log(error);
                        SGui.showError(error);
                    });
            }
        }
    },
})
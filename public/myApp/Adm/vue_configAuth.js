var app = new Vue({
    el: '#configAuth',
    data: {
        oData: oServerData,
        lconfigAuth: oServerData.lconfigAuth,   // consulta principal
        lAreas: oServerData.lAreas,             // conjunto áreas funcionales
        lUsers: oServerData.lUsers,             // conjunto usuarios
        lInci: oServerData.lInci,               // conjunto incidencias
        lComp: oServerData.lComp,               // conjunto empresas
        auth_id: 0,                             // primary key
        area_id: null,                          // id del area funcional - puede ser nulo
        user_id: null,                          // id del usuario - puede ser nulo
        tp_inci_id: 0,                          // id del tipo de incidencia
        comp_id: null,                          // id de la empresa - puede ser nulo
        auth: null,
        needauth: 0,                            // valor checkBox
    },
    mounted() {
        let self = this;
        
        // asignar areas funcionales a lAreas
        var datalAreas = [];
        datalAreas.push({ id: '', text: '' });
        for (var i = 0; i < self.lAreas.length; i++) {
            datalAreas.push({ id: self.lAreas[i].id_org_chart_job, text: self.lAreas[i].job_name });
        }

        var datalUsers = [];
        datalUsers.push({ id: '', text: '' });
        for (var i = 0; i < self.lUsers.length; i++) {
            datalUsers.push({ id: self.lUsers[i].id, text: self.lUsers[i].text });
        }

        var datalInci = [];
        datalInci.push({ id: '', text: '' });
        for (let i = 0; i < self.lInci.length; i++) {
            datalInci.push({ id: self.lInci[i].id_incidence_tp, text: self.lInci[i].incidence_tp_name });
        }

        var datalComp = [];
        datalComp.push({ id: '', text: '' });
        for (let i = 0; i < self.lComp.length; i++) {
            datalComp.push({ id: self.lComp[i].id_company, text: self.lComp[i].company_name_ui });
        }

        $('#insTp')
            .select2({
                placeholder: 'selecciona tipo de incidencia',
                data: datalInci,
            })
            .on('select2:select', function(e) {
                self.tp_inci_id = e.params.data.id;
            });

        $('#insTpU')
            .select2({
                placeholder: 'selecciona tipo de incidencia',
                data: datalInci,
            })
            .on('select2:select', function(e) {
                self.tp_inci_id = e.params.data.id;
            });

        $('#area')
            .select2({
                placeholder: 'selecciona área',
                data: datalAreas,
            })
            .on('select2:select', function(e) {
                self.area_id = e.params.data.id;
            });
        
        $('#areaU')
            .select2({
                placeholder: 'selecciona área',
                data: datalAreas,
            })
            .on('select2:select', function(e) {
                self.area_id = e.params.data.id;
            });

        $('#usr')
            .select2({
                placeholder: 'selecciona usuario',
                data: datalUsers,
            })
            .on('select2:select', function(e) {
                self.user_id = e.params.data.id;
            });
            
        $('#usrU')
            .select2({
                placeholder: 'selecciona usuario',
                data: datalUsers,
            })
            .on('select2:select', function(e) {
                self.user_id = e.params.data.id;
            });

        $('#comp')
            .select2({
                placeholder: 'selecciona empresa',
                data: datalComp,
            })
            .on('select2:select', function(e) {
                self.comp_id = e.params.data.id;
            });

        $('#compU')
            .select2({
                placeholder: 'selecciona empresa',
                data: datalComp,
            })
            .on('select2:select', function(e) {
                self.comp_id = e.params.data.id;
            });
    },
    methods: {
        showModal(data = null) {
            if (data == null) {
                this.auth_id = 0;
                this.tp_inci_id = 0;
                this.comp_id = null;
                this.area_id = null;
                this.user_id = null;
                this.needauth = 0;
                $('#createModal').modal('show');
            } else {
                this.auth_id = data[0];
                this.tp_inci_id = data[1];
                this.comp_id = data[2];
                this.area_id = data[3];
                this.user_id = data[4];
                this.needauth = data[5];

                $('#insTpU').val(this.tp_inci_id).trigger('change');
                $('#compU').val(this.comp_id).trigger('change');
                $('#areaU').val(this.area_id).trigger('change');
                $('#usrU').val(this.user_id).trigger('change');

                $('#editModal').modal('show');
            }
        },

        save() {
            var i = 0;
            if (this.tp_inci_id == 0) {
                SGui.showError("Seleccione tipo de incidencia");
                return null;
            }
            if (this.comp_id == null) {
                i++;
            }
            if (this.area_id == null) {
                i++;
            }
            if (this.user_id == null) {
                i++;
            }
            if (i >= 3) {
                SGui.showError("Debe de asignar valor a al menos uno: empresa, area, usuario");
                return null;
            } 
            // comprueba si ya existe un registro igual
            //for (let auth of this.lconfigAuth) {
            //    if (
            //        this.tp_inci_id == auth.tp_incidence_id &&
            //        this.comp_id == auth.company_id &&
            //        this.area_id == auth.org_chart_id &&
            //        this.user_id == auth.user_id         
            //    ){
            //        SGui.showError("Ya hay un registro con los mismos datos");
            //        return null;
            //    }                
            //}
            
            if (this.auth_id != 0) {
                for (let auth of this.lconfigAuth) {
                    if (this.auth_id == auth.id_config_auth && this.tp_inci_id == auth.tp_incidence_id) {
                        if ((this.comp_id != "" && this.comp_id != null && auth.company_id == null) ||
                            (this.area_id != "" && this.area_id != null && auth.org_chart_id == null) ||
                            (this.user_id != "" && this.user_id != null && auth.user_id == null)) {
                            SGui.showError("Sólo se pueden modificar los campos que ya tengan contenido asignado");
                            return null;
                        }         
                    }
                }
                SGui.showWaiting(5000);
                axios.post(this.oData.updateRoute, { // Datos en el modal
                    'auth_id': this.auth_id,
                    'tp_inci_id': this.tp_inci_id,
                    'comp_id': this.comp_id,
                    'area_id': this.area_id,
                    'user_id': this.user_id,
                    'needauth': this.needauth,
                })
                .then(response => {
                    let res = response.data;
                    if (res.success) {
                        $('#editModal').modal('hide');
                        SGui.showOk();
                        this.lconfigAuth = res.lconfigAuth; // nodo de la tabla para redibujarla
                        var dataAuth = [];
                        for (let auth of this.lconfigAuth) {
                            dataAuth.push(
                                [
                                    auth.id_config_auth,
                                    auth.tp_incidence_id,
                                    auth.company_id, 
                                    auth.org_chart_id,
                                    auth.user_id,
                                    auth.need_auth,
                                    auth.incidence,
                                    ((auth.need_auth == 0) ? 'No' : 'Sí'),
                                    auth.user,
                                    auth.job,
                                    auth.company
                                ]
                            );
                        }
                        table['table_auth'].clear().draw();
                        table['table_auth'].rows.add(dataAuth).draw();
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
                    'auth_id': this.auth_id,
                    'tp_inci_id': this.tp_inci_id,
                    'comp_id': this.comp_id,
                    'area_id': this.area_id,
                    'user_id': this.user_id,
                    'needauth': this.needauth,
                })
                .then(response => {
                    let res = response.data;
                    if (res.success) {
                        $('#createModal').modal('hide');
                        SGui.showOk();
                        var dataAuth = [];
                        for(let auth of res.lconfigAuth) {
                            dataAuth.push(
                                [
                                    auth.id_config_auth,
                                    auth.tp_incidence_id,
                                    auth.company_id, 
                                    auth.org_chart_id,
                                    auth.user_id,
                                    auth.need_auth,
                                    auth.incidence,
                                    ((auth.need_auth == 0) ? 'No' : 'Sí'),
                                    auth.user,
                                    auth.job,
                                    auth.company
                                ]
                            );
                        }
                        table['table_auth'].clear().draw();
                        table['table_auth'].rows.add(dataAuth).draw();
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
            axios.post(this.oData.deleteRoute, {
                'auth_id': data[0],
            })
            .then(response => {
                let res = response.data;
                if (res.success) {
                    $('#createModal').modal('hide');
                    SGui.showOk();
                    var dataAuth = [];
                    for (let auth of res.lconfigAuth) {
                        dataAuth.push(
                            [
                                auth.id_config_auth,
                                auth.tp_incidence_id,
                                auth.company_id, 
                                auth.org_chart_id,
                                auth.user_id,
                                auth.need_auth,
                                auth.incidence,
                                ((auth.need_auth == 0) ? 'No' : 'Sí'),
                                auth.user,
                                auth.job,
                                auth.company
                            ]
                        );
                    }
                    table['table_auth'].clear().draw();
                    table['table_auth'].rows.add(dataAuth).draw();
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
})
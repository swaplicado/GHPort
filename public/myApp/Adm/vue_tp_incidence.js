var app = new Vue({
    el: '#tp_incidence',
    data: {
        oData: oServerData,
        lTpIncidence: oServerData.lTpIncidence,
        lClIncidence: oServerData.lClIncidence,
        lInteractSystem: oServerData.lInteractSystem,
        indexesTpIncidenceTable: oServerData.indexesTpIncidenceTable,
        idTp: 0,
        nameTp: '',
        idCl: 0,
        nameCl: '',
        active: 0,
        auth: 0,
        idSys: 0,
        nameSys: '',
        deleted: 0
    },
    mounted() {
        let self = this;
        var datalClIncidence = [];
        var datalInteractSystem = [];
        datalClIncidence.push({ id: '', text: '' });
        datalInteractSystem.push({ id: '', text: '' });

        for (var i = 0; i < self.lClIncidence.length; i++) {
            datalClIncidence.push({ id: self.lClIncidence[i].id_incidence_cl, text: self.lClIncidence[i].incidence_cl_name });
        }

        for (var i = 0; i < self.lInteractSystem.length; i++) {
            datalInteractSystem.push({ id: self.lInteractSystem[i].id_int_sys, text: self.lInteractSystem[i].name });
        }

        $('#selClIncC')
            .select2({
                placeholder: 'selecciona clase',
                data: datalClIncidence,
            })
            .on('select2:select', function(e) {
                self.idCl = e.params.data.id;
            });

        $('#selClIncE')
            .select2({
                placeholder: 'selecciona clase',
                data: datalClIncidence,
            })
            .on('select2:select', function(e) {
                self.idCl = e.params.data.id;
            });
        $('#selIntSysC')
            .select2({
                placeholder: '',
                data: datalInteractSystem,
            })
            .on('select2:select', function(e) {
                self.idSys = e.params.data.id;
            });

        $('#selIntSysE')
            .select2({
                placeholder: '',
                data: datalInteractSystem,

            })
            .on('select2:select', function(e) {
                self.idSys = e.params.data.id;
            });
    },
    methods: {
        showModal(data = null) {
            if (data == null) {
                this.idTp = 0;
                this.nameTp = '';
                this.idCl = 0;
                this.nameCl = '';
                this.active = 0;
                this.auth = 0;
                this.idSys = 0;
                this.nameSys = '';
                this.deleted = 0;

                $('#createModal').modal('show');
            } else {
                this.idTp = data[this.indexesTpIncidenceTable.idTp];
                this.nameTp = data[this.indexesTpIncidenceTable.nameTp];
                this.idCl = data[this.indexesTpIncidenceTable.idCl];
                this.nameCl = data[this.indexesTpIncidenceTable.nameCl];
                this.active = parseInt(data[this.indexesTpIncidenceTable.active]);
                this.auth = parseInt(data[this.indexesTpIncidenceTable.auth]);
                this.idSys = data[this.indexesTpIncidenceTable.idSys];
                this.nameSys = data[this.indexesTpIncidenceTable.nameSys];
                this.deleted = data[this.indexesTpIncidenceTable.deleted];

                // $('#selUser').val(this.superviser_id).trigger('change');
                $('#selClIncE').val(this.idCl).trigger('change');
                $('#selIntSysE').val(this.idSys).trigger('change');

                $('#editModal').modal('show');
            }
        },

        save() {
            if (this.idTp == 0) {
                SGui.showError("El tipo de incidencia no puede ser vacio");
                return false;
            }
            if (this.idCl == 0) {
                SGui.showError("La clase de incidencia no puede ser vacia");
                return false;
            }
            if (this.idSys == 0) {
                SGui.showError("El sistema de interacción no puede ser vacio");
                return false;
            }
            SGui.showWaiting(5000);
            if (this.idTp != 0) {
                axios.post(this.oData.updateRoute, {
                        'idTp': this.idTp,
                        'nameTp': this.nameTp,
                        'idCl': this.idCl,
                        'nameCl': this.nameCl,
                        'active': this.active,
                        'auth': this.auth,
                        'idSys': this.idSys,
                        'nameSys': this.nameSys,

                    })
                    .then(response => {
                        let res = response.data;
                        if (res.success) {
                            $('#editModal').modal('hide');
                            SGui.showOk();
                            //this.lTpIncidence = res.lTpIncidence;
                            var dataTp = [];
                            for (let tp of res.lTpIncidence) {
                                dataTp.push(
                                    [
                                        tp.idTp,
                                        tp.nameTp,
                                        tp.idCl,
                                        tp.nameCl,
                                        tp.active,
                                        ((tp.active == 0) ? 'No' : 'Sí'),
                                        tp.auth,
                                        ((tp.auth == 0) ? 'No' : 'Sí'),
                                        tp.idSys,
                                        tp.nameSys,
                                        tp.deleted,
                                    ]
                                );
                            }
                            table['table_tp_incidence'].clear().draw();
                            table['table_tp_incidence'].rows.add(dataTp).draw();
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
                        'nameTp': this.nameTp,
                        'idCl': this.idCl,
                        'nameCl': this.nameCl,
                        'active': this.active,
                        'auth': this.auth,
                        'idSys': this.idSys,
                        'nameSys': this.nameSys,
                    })
                    .then(response => {
                        let res = response.data;
                        if (res.success) {
                            $('#createModal').modal('hide');
                            SGui.showOk();
                            //this.lTpIncidence = res.lTpIncidence;
                            var dataTp = [];
                            for (let tp of res.lTpIncidence) {
                                dataTp.push(
                                    [
                                        tp.idTp,
                                        tp.nameTp,
                                        tp.idCl,
                                        tp.nameCl,
                                        tp.active,
                                        ((tp.active == 0) ? 'No' : 'Sí'),
                                        tp.auth,
                                        ((tp.auth == 0) ? 'No' : 'Sí'),
                                        tp.idSys,
                                        tp.nameSys,
                                        tp.deleted,
                                    ]
                                );
                            }
                            table['table_tp_incidence'].clear().draw();
                            table['table_tp_incidence'].rows.add(dataTp).draw();
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
            axios.post(this.oData.deleteRoute, {
                    'idTp': data[this.indexesTpIncidenceTable.idTp],
                })
                .then(response => {
                    let res = response.data;
                    if (res.success) {
                        SGui.showOk();
                        //this.lAreas = res.lAreas;
                        var datalTpIncidence = [];
                        for (let tp of res.lTpIncidence) {
                            datalTpIncidence.push(
                                [
                                    tp.idTp,
                                    tp.nameTp,
                                    tp.idCl,
                                    tp.nameCl,
                                    tp.active,
                                    ((tp.active == 0) ? 'No' : 'Sí'),
                                    tp.auth,
                                    ((tp.auth == 0) ? 'No' : 'Sí'),
                                    tp.idSys,
                                    tp.nameSys,
                                    tp.deleted,
                                ]
                            );
                        }
                        table['table_tp_incidence'].clear().draw();
                        table['table_tp_incidence'].rows.add(datalTpIncidence).draw();
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
})
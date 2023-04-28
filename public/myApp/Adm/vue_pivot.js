var app = new Vue({
    el: '#pivot_incidence',
    data: {
        oData: oServerData,
        lTpIncidence: oServerData.lTpIncidence,
        lPivot: oServerData.lPivot,
        lInteractSystem: oServerData.lInteractSystem,
        indexesPivotTable: oServerData.indexesPivotTable,
        idPiv: 0,
        idTp: 0,
        nameTp: '',
        tpExt: 0,
        clExt: 0,
        idSys: 0,
        nameSys: ''
    },
    mounted() {
        let self = this;
        var datalTpIncidence = [];
        var datalInteractSystem = [];
        datalTpIncidence.push({ id: '', text: '' });
        datalInteractSystem.push({ id: '', text: '' });

        for (var i = 0; i < self.lTpIncidence.length; i++) {
            datalTpIncidence.push({ id: self.lTpIncidence[i].id_incidence_tp, text: self.lTpIncidence[i].incidence_tp_name });
        }

        for (var i = 0; i < self.lInteractSystem.length; i++) {
            datalInteractSystem.push({ id: self.lInteractSystem[i].id_int_sys, text: self.lInteractSystem[i].name });
        }

        $('#selTpIncC')
            .select2({
                placeholder: '',
                data: datalTpIncidence,
            })
            .on('select2:select', function(e) {
                self.idTp = e.params.data.id;
            });

        $('#selTpIncE')
            .select2({
                placeholder: '',
                data: datalTpIncidence,
            })
            .on('select2:select', function(e) {
                self.idTp = e.params.data.id;
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
                this.idPiv = 0;
                this.idTp = 0;
                this.nameTp = '';
                this.tpExt = 0;
                this.clExt = 0;
                this.idSys = 0;
                this.nameSys = '';

                $('#createModal').modal('show');
            } else {
                this.idPiv = data[this.indexesPivotTable.idPiv];
                this.idTp = data[this.indexesPivotTable.idTp];
                this.nameTp = data[this.indexesPivotTable.nameTp];
                this.tpExt = data[this.indexesPivotTable.tpExt];
                this.clExt = data[this.indexesPivotTable.clExt];
                this.idSys = data[this.indexesPivotTable.idSys];
                this.nameSys = data[this.indexesPivotTable.nameSys];

                // $('#selUser').val(this.superviser_id).trigger('change');
                $('#selTpIncE').val(this.idTp).trigger('change');
                $('#selIntSysE').val(this.idSys).trigger('change');

                $('#editModal').modal('show');
            }
        },

        save() {
            if (this.idTp == 0) {
                SGui.showError("El tipo de incidencia no puede ser vacio");
                return false;
            }
            if (this.idSys == 0) {
                SGui.showError("El sistema de interacción no puede ser vacio");
                return false;
            }
            if (this.tpExt == 0) {
                SGui.showError("El tipo externo no puede ser vacio");
                return false;
            }
            if (this.clExt == 0) {
                SGui.showError("La clase externa no puede ser vacio");
                return false;
            }
            SGui.showWaiting(5000);
            if (this.idPiv != 0) {
                axios.post(this.oData.updateRoute, {
                        'idPiv': this.idPiv,
                        'idTp': this.idTp,
                        'nameTp': this.nameTp,
                        'tpExt': this.tpExt,
                        'clExt': this.clExt,
                        'idSys': this.idSys,
                        'nameSys': this.nameSys,

                    })
                    .then(response => {
                        let res = response.data;
                        if (res.success) {
                            $('#editModal').modal('hide');
                            SGui.showOk();
                            //this.lTpIncidence = res.lTpIncidence;
                            var dataPiv = [];
                            for (let piv of res.lPivot) {
                                dataPiv.push(
                                    [
                                        piv.idPiv,
                                        piv.idTp,
                                        piv.nameTp,
                                        piv.tpExt,
                                        piv.clExt,
                                        piv.idSys,
                                        piv.nameSys,
                                    ]
                                );
                            }
                            table['table_pivot'].clear().draw();
                            table['table_pivot'].rows.add(dataPiv).draw();
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
                        'idTp': this.idTp,
                        'nameTp': this.nameTp,
                        'tpExt': this.tpExt,
                        'clExt': this.clExt,
                        'idSys': this.idSys,
                        'nameSys': this.nameSys,
                    })
                    .then(response => {
                        let res = response.data;
                        if (res.success) {
                            $('#createModal').modal('hide');
                            SGui.showOk();
                            //this.lTpIncidence = res.lTpIncidence;
                            var dataPiv = [];
                            for (let piv of res.lPivot) {
                                dataPiv.push(
                                    [
                                        piv.idPiv,
                                        piv.idTp,
                                        piv.nameTp,
                                        piv.tpExt,
                                        piv.clExt,
                                        piv.idSys,
                                        piv.nameSys,
                                    ]
                                );
                            }
                            table['table_pivot'].clear().draw();
                            table['table_pivot'].rows.add(dataPiv).draw();
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
                    'idPiv': data[this.indexesPivotTable.idPiv],
                })
                .then(response => {
                    let res = response.data;
                    if (res.success) {
                        SGui.showOk();
                        //this.lAreas = res.lAreas;
                        var dataPiv = [];
                        for (let piv of res.lPivot) {
                            dataPiv.push(
                                [
                                    piv.idPiv,
                                    piv.idTp,
                                    piv.nameTp,
                                    piv.tpExt,
                                    piv.clExt,
                                    piv.idSys,
                                    piv.nameSys,
                                ]
                            );
                        }
                        table['table_pivot'].clear().draw();
                        table['table_pivot'].rows.add(dataPiv).draw();
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
var app = new Vue({
    el: '#user',
    data: {
        oData: oServerData,
        lUser: oServerData.lUser,
        lPlan: oServerData.lPlan,
        lOrgChart: oServerData.lOrgChart,
        lRol: oServerData.lRol,
        indexesUserTable: oServerData.indexesUserTable,
        idUser: 0,
        username: '',
        fullname: '',
        mail: '',
        numUser: 0,
        benDate: '',
        nameOrg: '',
        nameVp: '',
        active: 0,
        passRess: 0,
        selArea: 0,
        selVac: 0,
        selRol: 0,
    },
    mounted() {
        let self = this;
        var datalAreas = [];
        var datalPlan = [];
        var datalRol = [];
        for (var i = 0; i < self.lOrgChart.length; i++) {
            datalAreas.push({ id: self.lOrgChart[i].id_org_chart_job, text: self.lOrgChart[i].job_name_ui });
        }
        for (var i = 0; i < self.lPlan.length; i++) {
            datalPlan.push({ id: self.lPlan[i].id_vacation_plan, text: self.lPlan[i].vacation_plan_name });
        }
        for (var i = 0; i < self.lRol.length; i++) {
            datalRol.push({ id: self.lRol[i].id_rol, text: self.lRol[i].rol });
        }
        $('#selArea')
            .select2({
                placeholder: 'selecciona área',
                data: datalAreas,
            }).on('select2:select', function(e) {
                self.selArea = e.params.data.id;
            });

        $('#selVac')
            .select2({
                placeholder: 'selecciona plan',
                data: datalPlan,
            }).on('select2:select', function(e) {
                self.selVac = e.params.data.id;
            });
        $('#selRol')
            .select2({
                placeholder: 'selecciona rol',
                data: datalRol,
            }).on('select2:select', function(e) {
                self.selRol = e.params.data.id;
            });
        $('#selArea').val('').trigger('change');
        $('#selVac').val('').trigger('change');
        $('#selRol').val('').trigger('change');
    },
    methods: {
        showModal(data = null) {
            this.idUser = data[this.indexesUserTable.idUser];
            this.username = data[this.indexesUserTable.username];
            this.fullname = data[this.indexesUserTable.fullname];
            this.mail = data[this.indexesUserTable.mail];
            this.numUser = data[this.indexesUserTable.numUser];
            this.benDate = data[this.indexesUserTable.benDate];
            this.nameRol = data[this.indexesUserTable.nameRol];
            this.idRol = data[this.indexesUserTable.idRol];
            this.nameOrg = data[this.indexesUserTable.nameOrg];
            this.idOrg = parseInt(data[this.indexesUserTable.idOrg]);
            this.nameVp = data[this.indexesUserTable.nameVp];
            this.idPlan = parseInt(data[this.indexesUserTable.idPlan]);
            this.active = parseInt(data[this.indexesUserTable.active]);
            this.passRess = 0;
            this.selArea = this.idOrg;
            this.selVac = this.idPlan;
            this.selRol = this.idRol;
            $('#selArea').val(this.idOrg).trigger('change');
            $('#selVac').val(this.idPlan).trigger('change');
            $('#selRol').val(this.idRol).trigger('change');

            $('#editModal').modal('show');

        },

        save() {
            if (this.username == '') {
                SGui.showError("El usuario no puede estar vacio");
                return false;
            }
            if (this.mail == '') {
                SGui.showError("El mail no puede estar vacio");
                return false;
            }
            SGui.showWaiting(5000);

            axios.post(this.oData.updateRoute, {
                    'idUser': this.idUser,
                    'username': this.username,
                    'full_name': this.fullname,
                    'mail': this.mail,
                    'active': this.active,
                    'passRess': this.passRess,
                    'selArea': this.selArea,
                    'selVac': this.selVac,
                    'selRol': this.selRol,
                })
                .then(response => {
                    let res = response.data;
                    if (res.success) {
                        $('#editModal').modal('hide');
                        SGui.showOk();
                        //this.lTpIncidence = res.lTpIncidence;
                        var dataUser = [];
                        for (let us of res.lUser) {
                            dataUser.push(
                                [
                                    us.idUser,
                                    us.username,
                                    us.fullname,
                                    us.mail,
                                    us.numUser,
                                    us.benDate,
                                    us.nameRol,
                                    us.idRol,
                                    us.nameOrg,
                                    us.idOrg,
                                    us.nameVp,
                                    us.idPlan,
                                    us.active,
                                    ((us.active == 0) ? 'No' : 'Sí'),
                                ]
                            );
                        }
                        table['table_user'].clear().draw();
                        table['table_user'].rows.add(dataUser).draw();

                    } else {
                        SGui.showError(res.message);
                    }
                })
                .catch(function(error) {
                    console.log(error);
                    SGui.showError(error);
                });
        },
    },
})
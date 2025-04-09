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
        scheduleId: null,
        benDate: '',
        nameOrg: '',
        nameVp: '',
        active: 0,
        passRess: 0,
        selArea: 0,
        selVac: 0, 
        selRol: 0,
        lSchedules: oServerData.lSchedules,
        selSchedule: null,
        can_change_dp: 0,
        can_change_cv: 0,
    },
    mounted() {
        let self = this;
        var datalAreas = [];
        var datalPlan = [];
        var datalSchedules = [];
        var datalRoles = [];
        for (var i = 0; i < self.lOrgChart.length; i++) {
            datalAreas.push({ id: self.lOrgChart[i].id_org_chart_job, text: self.lOrgChart[i].job_name_ui });
        }
        for (var i = 0; i < self.lPlan.length; i++) {
            datalPlan.push({ id: self.lPlan[i].id_vacation_plan, text: self.lPlan[i].vacation_plan_name });
        }
        for (var i = 0; i < self.lSchedules.length; i++) {
            datalSchedules.push({ id: self.lSchedules[i].id, text: self.lSchedules[i].name });
        }
        for (var i = 0; i < self.lRol.length; i++) {
            datalRoles.push({ id: self.lRol[i].id_rol, text: self.lRol[i].rol });
        }
        $('#selArea')
            .select2({
                placeholder: 'selecciona nodo org.',
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
        $('#selArea').val('').trigger('change');
        $('#selVac').val('').trigger('change');

        $('#selSchedule')
            .select2({
                placeholder: 'selecciona horario',
                data: datalSchedules,
            }).on('select2:select', function(e) {
                self.selSchedule = e.params.data.id;
            });
        
        $('#selRol')
            .select2({
                placeholder: 'selecciona rol',
                data: datalRoles,
            }).on('select2:select', function(e) {
                self.selRol = e.params.data.id;
            });
        $('#selRol').val('').trigger('change');

        $('#selSchedule').val('').trigger('change');
    },
    methods: {
        showModal(data = null) {
            this.idUser = data[this.indexesUserTable.idUser];
            this.username = data[this.indexesUserTable.username];
            this.fullname = data[this.indexesUserTable.fullname];
            this.mail = data[this.indexesUserTable.mail];
            this.scheduleId = data[this.indexesUserTable.scheduleId];
            this.benDate = data[this.indexesUserTable.benDate];
            this.nameOrg = data[this.indexesUserTable.nameOrg];
            this.idOrg = parseInt(data[this.indexesUserTable.idOrg]);
            this.nameVp = data[this.indexesUserTable.nameVp];
            this.idPlan = parseInt(data[this.indexesUserTable.idPlan]);
            this.active = parseInt(data[this.indexesUserTable.active]);
            this.idRol = parseInt(data[this.indexesUserTable.idRol]);
            this.passRess = 0;
            this.can_change_dp = parseInt(data[this.indexesUserTable.can_change_dp]);
            this.can_change_cv = parseInt(data[this.indexesUserTable.can_change_cv]);
            this.selArea = this.idOrg;
            this.selVac = this.idPlan;
            this.selRol = this.idRol;
            $('#selArea').val(this.idOrg).trigger('change');
            $('#selVac').val(this.idPlan).trigger('change');
            $('#selRol').val(this.idRol).trigger('change');
            $('#selSchedule').val(this.scheduleId).trigger('change');

            $('#editModal').modal('show');

        },

        save() {
            if (this.username == '') {
                SGui.showError("Debe llenar el campo Usuario");
                return false;
            }
            if (this.mail == '') {
                SGui.showError("Debe llenar el campo Correo");
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
                    'selSchedule': this.selSchedule,
                    'selRol': this.selRol,
                    'can_change_dp': this.can_change_dp,
                    'can_change_cv': this.can_change_cv,
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
                                    us.schedule_id,
                                    (us.schedule_name != null ? us.schedule_name : 'NA'),
                                    us.benDate,
                                    us.nameOrg,
                                    us.idOrg,
                                    us.nameVp,
                                    us.idPlan,
                                    us.active,
                                    ((us.active == 0) ? 'No' : 'Sí'),
                                    us.idRol,
                                    us.can_change_dp,
                                    us.can_change_cv,
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
var app = new Vue({
    el: '#user',
    data: {
        oData: oServerData,
        lUser: oServerData.lUser,
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
    },
    mounted() {

    },
    methods: {
        showModal(data = null) {
            this.idUser = data[this.indexesUserTable.idUser];
            this.username = data[this.indexesUserTable.username];
            this.fullname = data[this.indexesUserTable.fullname];
            this.mail = data[this.indexesUserTable.mail];
            this.numUser = data[this.indexesUserTable.numUser];
            this.benDate = data[this.indexesUserTable.benDate];
            this.nameOrg = data[this.indexesUserTable.nameOrg];
            this.nameVp = data[this.indexesUserTable.nameVp];
            this.active = parseInt(data[this.indexesUserTable.active]);
            this.passRess = 0;

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
                                    us.nameOrg,
                                    us.nameVp,
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
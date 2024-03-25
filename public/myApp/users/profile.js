var app = new Vue({
    el: '#profile',
    data: {
        oData: oServerData,
        levels: oServerData.levels,
        myConf_level: oServerData.myConf_level,
        user: oServerData.user,
        password: null,
        confirm_password: null,
        changePass: false,
        showPassword: false,
        typeInputPass: 'password',
        reportChecked: oServerData.reportChecked,
        always_send: oServerData.reportAlways_send ? 'option2' : 'option1',
    },
    mounted(){
        self = this;

        $('.select2-class').select2({});

        $('#sel_levels').select2({
            placeholder: 'Selecciona nivel',
            data: self.levels,
        }).on('select2:select', function(e) {
            self.myConf_level = e.params.data.id;
            self.updateReports();
        });

        if(self.myConf_level == null){
            self.myConf_level = 0;
        }
        $('#sel_levels').val(self.myConf_level).trigger('change');
    },
    methods: {
        updatePass(){
            if(this.password == null || this.password == ""){
                SGui.showMessage('', 'Debe ingresar el campo "Contraseña"', 'warning');
                return;
            }
            if(this.confirm_password == null || this.confirm_password == ""){
                SGui.showMessage('', 'Debe ingresar el campo "Confirmar contraseña"', 'warning');
                return;
            }
            if(this.confirm_password != this.password){
                SGui.showMessage('', 'Los campos "Contraseña" y "Confirmar contraseña" deben coincidir, por favor, introduzcalos de nuevo', 'warning');
                return;
            }

            axios.post(this.oData.user_update, {
                'password': this.password,
                'confirm_password': this.confirm_password,
            }).then( response => {
                let data = response.data;
                if(data.success){
                    SGui.showMessage('', data.message, data.icon);
                    this.password = null;
                    this.confirm_password = null;
                    this.changePass = false;
                    window.location.reload();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            }).catch(function (error){
                console.log(error);
                SGui.showMessage('', error, 'error');
            })
        },

        showPass(){
            this.showPassword = !this.showPassword;
            this.showPassword == true ? this.typeInputPass = 'text' : this.typeInputPass = 'password';
        },

        updateReports(){
            SGui.showWaiting(15000);

            let send;
            if(this.always_send == 'option1'){
                send = false;
            }else{
                send = true;
            }

            let route = this.oData.updateReportRoute;
            axios.post(route,{
                'is_active': this.reportChecked,
                'always_send': send,
                'myConf_level': self.myConf_level,
            })
            .then(result => {
                let data = result.data;
                if(data.success){
                    this.reportChecked = data.checked;
                    SGui.showOk();
                }else{
                    this.reportChecked = data.checked;
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function(error){
                console.log(error);
                SGui.showError(error);
            });
        }
    }
})
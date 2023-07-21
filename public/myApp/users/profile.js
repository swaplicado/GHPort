var app = new Vue({
    el: '#profile',
    data: {
        oData: oServerData,
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
                SGui.showMessage('', 'Los campos "Contraseña" y "Confirmar contraseña" deben coincidir', 'warning');
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
                SGui.showMessage('', 'Error al actualizar el registro', 'error');
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
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
    }
})
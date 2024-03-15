var app = new Vue({
    el: '#usersShowInSystemApp',
    data: {
        oData: oServerData,
        lUsers: oServerData.lUsers,
    },
    mounted(){
        self = this;
    },
    methods: {
        updateShowUserInSystem(user_id, name, checkBox){
            let checked = $('#'+checkBox).is(':checked');
            let route = this.oData.updateShowUserRoute;
            
            axios.post(route, {
                'showInSystem': checked,
                'user_id': user_id,
            })
            .then(result => {
                let data = result.data;
                if(data.success){
                    createToast( name + ' ACTUALIZADO', data.toastType, 6000);
                }else{
                    createToast( name + ' NO ACTUALIZADO', data.toastType, 6000);
                    $('#'+checkBox).prop('checked', !checked);
                }
            })
            .catch(function (error) {
                console.log(error);
                createToast(name + ' NO ACTUALIZADO', 'error', 6000);
                $('#'+checkBox).prop('checked', !checked);
            })
        }
    }
});
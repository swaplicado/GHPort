var app = new Vue({
    el: '#appDelegation',
    data: {
        oData: oServerData,
        lDelegations: oServerData.lDelegations,
        lUsers: oServerData.lUsers,
        start_date: null,
        end_date: null,
        user_delegated: null,
        user_delegation: null,
    },
    mounted(){
        var datalUser = [{id: '', text: ''}];
        for(var i = 0; i<this.lUsers.length; i++){
            datalUser.push({id: this.lUsers[i].id, text: this.lUsers[i].full_name_ui});
        }

        $('#user_delegated')
            .select2({
                placeholder: 'selecciona',
                data: datalUser,
            });

        $('#user_delegation')
            .select2({
                placeholder: 'selecciona',
                data: datalUser,
            });
    },
    methods: {
        showModal(data = null){
            if(data != null){

            }else{

            }
            $('#modal_delegation').modal('show');
        },
    },
})
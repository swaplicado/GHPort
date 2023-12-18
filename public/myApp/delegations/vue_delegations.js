var app = new Vue({
    el: '#appDelegation',
    data: {
        oData: oServerData,
        indexes: oServerData.indexesDelegation,
        lDelegations_created: oServerData.lDelegations_created,
        lDelegations_asigned: oServerData.lDelegations_asigned,
        lUsers: oServerData.lUsers,
        lMyManagers: oServerData.lMyManagers,
        start_date: null,
        end_date: null,
        user_delegated: null,
        user_delegation: null,
        myDelegation: false,
        delegation_id: null,
        closeDelegation: false,
    },
    mounted(){
        var datalUser = [];
        for(var i = 0; i<this.lUsers.length; i++){
            datalUser.push({id: this.lUsers[i].id, text: this.lUsers[i].full_name_ui});
        }

        var datalMyManagers = [];
        for(var i = 0; i<this.lMyManagers.length; i++){
            datalMyManagers.push({id: this.lMyManagers[i].id, text: this.lMyManagers[i].full_name_ui});
        }

        $('#user_delegated')
            .select2({
                placeholder: 'selecciona',
                data: datalMyManagers,
            });

        $('#user_delegation_d')
            .select2({
                placeholder: 'selecciona',
                data: datalUser,
            });

        $('#user_delegation_n')
            .select2({
                placeholder: 'selecciona',
                data: datalUser,
            });

        $('#user_delegated').val('').trigger('change');
        $('#user_delegation_d').val('').trigger('change');
        $('#user_delegation_n').val('').trigger('change');
    },
    methods: {
        showModal(data = null){
            this.myDelegation = true;
            $('#user_delegation_d').val('').trigger('change');
            if(data != null){
                this.delegation_id = data[this.indexes.id_delegation];
                this.start_date = data[this.indexes.start_date];
                this.end_date = data[this.indexes.end_date];
                this.closeDelegation = !data[this.indexes.is_active];
                $('#modal_edit_delegation').modal('show');
            }else{
                this.start_date = null;
                this.end_date = null;
                this.user_delegated = null;
                this.user_delegation = null;
                $('#modal_my_delegation').modal('show');
            }
        },

        showModalDelegations(){
            $('#user_delegated').val('').trigger('change');
            $('#user_delegation_n').val('').trigger('change');
            this.myDelegation = false;
            this.start_date = null;
            this.end_date = null;
            this.user_delegated = null;
            this.user_delegation = null;
            $('#modal_delegations').modal('show');
        },

        saveDelegation(){
            if(this.start_date == null || this.start_date == ''){
                SGui.showMessage('', 'Debe introducir una fecha de inicio', 'warning');
                return;
            }

            if(this.end_date != null){
                if(moment(this.start_date).isAfter(this.end_date) || moment(this.start_date).isSame(this.end_date)){
                    SGui.showMessage('', 'La fecha de fin debe ser posterior a la fecha de inicio', 'warning');
                    return;
                }
            }

            this.user_delegated = $('#user_delegated').val();
            if(this.myDelegation){
                this.user_delegation = $('#user_delegation_d').val();
            }else{
                this.user_delegation = $('#user_delegation_n').val();
            }

            if((this.user_delegated == null || this.user_delegated == '') && !this.myDelegation){
                SGui.showMessage('', 'Debe introducir el usuario ausente', 'warning');
                return;
            }

            if(this.user_delegation == null || this.user_delegation == ''){
                SGui.showMessage('', 'Debe introducir el usuario encargado', 'warning');
                return;
            }

            SGui.showWaiting(15000);
            axios.post(this.oData.saveDelegationRoute, {
                'start_date': this.start_date,
                'end_date': this.end_date,
                'user_delegated': this.user_delegated,
                'user_delegation': this.user_delegation,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.reDrawDelegationTable(data.lDelegations);
                    $('#modal_delegations').modal('hide');
                    $('#modal_my_delegation').modal('hide');
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(error) {
                console.log(error);
                SGui.showError(error);
            })
        },

        updateDelegation(){
            if(this.start_date == null || this.start_date == ''){
                SGui.showMessage('', 'Debe introducir una fecha de inicio', 'warning');
                return;
            }

            if(this.end_date != null){
                if(moment(this.start_date).isAfter(this.end_date) || moment(this.start_date).isSame(this.end_date)){
                    SGui.showMessage('', 'La fecha de fin debe ser posterior a la fecha de inicio', 'warning');
                    return;
                }
            }

            SGui.showWaiting(15000)
            axios.post(this.oData.updateDelegationRoute, {
                'start_date': this.start_date,
                'end_date': this.end_date,
                'closeDelegation': this.closeDelegation,
                'delegation_id': this.delegation_id,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.reDrawDelegationTable(data.lDelegations);
                    $('#modal_edit_delegation').modal('hide');
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(error) {
                console.log(error);
                SGui.showError(error);
            })
        },

        reDrawDelegationTable(data){
            var dataDeleg = [];
            for(var deleg of data){
                dataDeleg.push(
                    [
                        deleg.id_delegation,
                        deleg.user_delegation_id,
                        deleg.user_delegated_id,
                        deleg.is_active,
                        deleg.user_delegated_name,
                        deleg.user_delegation_name,
                        deleg.start_date,
                        deleg.end_date,
                    ]
                );
            }
            table['table_delegation_created'].clear().draw();
            table['table_delegation_created'].rows.add(dataDeleg).draw();
        },

        deleteRegistry(data){
            Swal.fire({
                title: '¿Desea eliminar la delegación?',
                html: '<b>Usuario ausente:</b> ' + data[this.indexes.user_delegated_name] +
                        '<br>' +
                        '<b>Usuario encargado:</b> ' +  
                        data[this.indexes.user_delegation_name] +
                        '<br>' +
                        '<b>Fecha inicio:</b> ' +
                        data[this.indexes.start_date] +
                        '<br>' +
                        '<b>Fecha fin:</b> ' +
                        (data[this.indexes.end_date] != null ? data[this.indexes.end_date] : 'Indefinida'),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deleteDelegation(data[this.indexes.id_delegation]);
                }
            })
        },

        deleteDelegation(delegation_id){
            SGui.showWaiting(15000);
            axios.post(this.oData.deleteDelegationRoute, {
                'delegation_id': delegation_id,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.reDrawDelegationTable(data.lDelegations);
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(error){
                console.log(error);
                SGui.showError(error);
            })
        },
    },
})
var app = new Vue({
    el: '#specialType',
    data: {
        oData: oServerData,
        indexSpecialType: oServerData.indexSpecialType,
        lSpecialType: oServerData.lSpecialType,
        lSituation: oServerData.lSituation,
        situation_id: 1,
        name: null,
        code: null,
        id_specialType: null,
    },
    mounted(){
        var self = this;
        $('#sel_situation').select2({
            placeholdere: 'selecciona',
            data: self.lSituation
        })
        .on('select2:select', function (e){
            self.situation_id = e.params.data.id;
        });
    },
    methods: {
        showModal(data = null){
            if(data != null){
                this.id_specialType = data[this.indexSpecialType.id];
                this.name = data[this.indexSpecialType.name];
                this.code = data[this.indexSpecialType.code];
                this.situation_id = data[this.indexSpecialType.situation_id];
                $('#sel_situation').val(this.situation_id).trigger('change');
            }else{
                this.id_specialType = null;
                this.name = null;
                this.code = null;
                this.situation_id = 1;
            }
            $('#modal_special_type').modal('show');
        },

        save(){
            let route = null;
            if(this.id_specialType == null){
                route = this.oData.routeSave;
            }else{
                route = this.oData.routeUpdate;
            }

            if(this.name == null || this.name == ""){
                SGui.showMessage('', 'Debe ingresar un nombre de solicitud', 'warning');
                return;
            }

            if(this.situation_id == null || this.situation_id == ""){
                SGui.showMessage('', 'Debe ingresar la situación de solicitud', 'warning');
                return;
            }

            SGui.showWaiting(15000);
            axios.post(route, {
                'id': this.id_specialType,
                'situation_id': this.situation_id,
                'name': this.name,
                'code': this.code,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    $('#modal_special_type').modal('hide');
                    SGui.showOk();
                    this.reDrawTableSpecialType(data.lSpecialType);
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(error) {
                console.log(error);
                SGui.showError();
            });
        },

        reDrawTableSpecialType(lSpecialType){
            var dataSpecialType = [];
            for(let special of lSpecialType){
                dataSpecialType.push(
                    [
                        special.id_special_type,
                        special.situation,
                        special.name,
                        special.code,
                        special.situation_name,
                    ]
                );
            }
            table['table_special'].clear().draw();
            table['table_special'].rows.add(dataSpecialType).draw();
        },

        deleteRegistry(data){
            Swal.fire({
                title: '¿Desea eliminar el tipo de solicitud?',
                html: data[this.indexSpecialType.name],
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deleteSpecial(data[this.indexSpecialType.id]);
                }
            })
        },

        deleteSpecial(id){
            SGui.showWaiting(15000);

            axios.post(this.oData.routeDelete, {
                'id': id,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.reDrawTableSpecialType(data.lSpecialType);
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(e){
                console.log(e);
                SGui.showError(e);
            })
        }
    }
})
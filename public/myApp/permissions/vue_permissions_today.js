var app =  new Vue({
    el: '#permissions_today_app',
    data: {
        oData: oServerData,
        oDateUtils: new SDateUtils(),
        lPermissions: oServerData.lPermissions,
        today: oServerData.today,
        lClass: oServerData.lClass,
        lTypes: oServerData.lTypes,
        class_id: null,
        type_id: null,
    },
    mounted(){
        self = this;

        $('.select2-class-modal').select2({
            dropdownParent: $('#modal_permission')
        });

        $('.select2-class').select2({});

        $('#permission_tp_filter').select2({
            data: self.lTypes,
        }).on('select2:select', function(e) {
            self.type_id = e.params.data.id;
        });
    },
    methods: {
        refresh(){
            SGui.showWaiting(15000);
            let route = this.oData.permissionsTodayGetRoute;
            axios.get(route, {

            })
            .then(result => {
                let data = result.data;
                if(data.success){
                    this.reDrawTablePermissions('table_permissions', data.lPermissions);
                    SGui.showOk();
                }else{
                    SGui.showMessage('', 'No se cargaron los permisos, intente mas tarde');
                }
            })
            .catch( function(error){
                console.log(error);
                SGui.showError(error);
            }); 
        },

        reDrawTablePermissions(table_name, lPermissions) {
            var dataPermissions = [];
            for (let permission of lPermissions) {
                dataPermissions.push(
                    [
                        permission.id_hours_leave,
                        permission.cl_permission_id,
                        permission.type_permission_id,
                        permission.full_name,
                        permission.permission_cl_name,
                        permission.permission_tp_name,
                        permission.time,
                    ]
                );
            }
            table[table_name].clear().draw();
            table[table_name].rows.add(dataPermissions).draw();
        },
    }
});
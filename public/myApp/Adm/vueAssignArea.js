var app = new Vue({
    el: '#assignArea',
    data: {
        oData: oServerData,
        lAreas: oServerData.lAreas,
        lUsers: oServerData.lUsers,
        area: null,
        area_id: null,
        superviser_id: null,
        father_area_id: null,
    },
    mounted(){
        let self = this;
        var datalAreas = [];
        datalAreas.push({id: '', text: ''});
        for(var i = 0; i<self.lAreas.length; i++){
            datalAreas.push({id: self.lAreas[i].id_area, text: self.lAreas[i].area});
        }
        $('#selUser')
            .select2({
                placeholder: 'selecciona usuario',
                data: self.lUsers,
            })
            .on('select2:select', function (e){
                self.superviser_id = e.params.data.id;
            });
            
        $('#selArea')
            .select2({
                placeholder: 'selecciona área',
                data: datalAreas,
            })
            .on('select2:select', function (e){
                self.father_area_id = e.params.data.id;
            });
    },
    methods: {
        showModal(data){
            this.area_id = data[0];
            this.father_area_id = data[1];
            this.superviser_id = data[2];
            this.area = data[3];

            $('#selUser').val(this.superviser_id).trigger('change');
            $('#selArea').val(this.father_area_id).trigger('change');

            $('#editModal').modal('show');
        },

        save(){
            SGui.showWaiting(5000);
            axios.post(this.oData.updateRoute, {
                'area_id': this.area_id,
                'father_area_id': this.father_area_id,
                'superviser_id': this.superviser_id,
            })
            .then(response => {
                let res = response.data;
                if(res.success){
                    $('#editModal').modal('hide');
                    SGui.showOk();
                    location.reload();
                }else{
                    SGui.showError(res.message);
                }
            })
            .catch(function(error) {
                console.log(error);
                SGui.showError(error);
            });
        }
    },
})
var app = new Vue({
    el: '#specialTempTypes',
    data: {
        oData: oServerData,
        oDateUtils: new SDateUtils(),
        indexes: oServerData.indexes,
        lSpecialSeasonType: oServerData.lSpecialSeasonType,
        id_special_season_type: null,
        name: null,
        key_code: null,
        priority: 5,
        color: '#66FF66',
        text_color: 'black',
        description: null,
    },
    mounted(){

    },
    methods: {
        showModal(data = null){
            if(data == null){
                this.id_special_season_type = null;
                this.name = null;
                this.key_code = null;
                this.priority = 5;
                this.color = '#66FF66';
                this.text_color = 'black';
                this.description = null;
            }else{
                this.id_special_season_type = data[this.indexes['id_special_season_type']];
                this.name = data[this.indexes['name']];
                this.key_code = data[this.indexes['key_code']];
                this.priority = data[this.indexes['priority']];
                this.color = data[this.indexes['color']];
                this.text_color = data[this.indexes['priority']] > 3 ? 'white' : 'black';
                this.description = data[this.indexes['description']];
            }
            $('#modal_season_type').modal('show');
        },

        saveSeasonType(){
            SGui.showWaiting(15000);
            if(this.id_special_season_type == null){
                var route = this.oData.SeasonTypeSaveRoute;
            }else{
                var route =  this.oData.SeasonTypeUpdateRoute;
            }

            axios.post(route, {
                'id_special_season_type': this.id_special_season_type,
                'name': this.name,
                'key_code': this.key_code,
                'priority': this.priority,
                'description': this.description,
                'color': this.color,
            })
            .then(result => {
                let data = result.data;
                if(data.success){
                    this.reDrawSeasonTypesTable(data.lSpecialSeasonType);
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

        deleteRegistry(data){
            Swal.fire({
                title: '¿Desea eliminar la temporada especial?',
                html: '<b>'+ data[this.indexes['name']] +'</b>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deleteSeasonType(data[this.indexes.id_special_season_type]);
                }
            })
        },

        deleteSeasonType(id){
            SGui.showWaiting(15000);
            axios.post(this.oData.SeasonTypeDeleteRoute, {
                'id_special_season_type': id,
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    this.reDrawSeasonTypesTable(data.lSpecialSeasonType);
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function(error) {
                console.log(error);
                SGui.showError(error);
            });
        },

        reDrawSeasonTypesTable(data){
            dataSeasonType = []
            for (let season of data) {
                dataSeasonType.push([
                    season.id_special_season_type,
                    season.color,
                    season.name,
                    season.key_code,
                    season.priority,
                    season.description,
                    this.oDateUtils.formatDate(season.updated_at),
                    season.full_name_ui,
                ])
            }

            table['table_special_season_types'].clear().draw();
            table['table_special_season_types'].rows.add(dataSeasonType).draw();

            for (let i = 0; i < dataSeasonType.length; i++) {
                let cell = table['table_special_season_types'].cell(i, this.indexes['priority']).node();
                switch (dataSeasonType[i][this.indexes['priority']]) {
                    case 1:
                        $( cell ).addClass( 'priority_1' );
                        break;
                    case 2:
                        $( cell ).addClass( 'priority_2' );
                        break;
                    case 3:
                        $( cell ).addClass( 'priority_3' );
                        break;
                    case 4:
                        $( cell ).addClass( 'priority_4' );
                        break;
                    case 5:
                        $( cell ).addClass( 'priority_5' );
                        break;
                
                    default:
                        break;
                }
            }

        },

        changeColor(){
            switch (this.priority) {
                case '1':
                    this.color =  '#006600';
                    this.text_color = 'white';
                    break;
                case '2':
                    this.color =  '#009900';
                    this.text_color = 'white';
                    break;
                case '3':
                    this.color =  '#00CC00';
                    this.text_color = 'black';
                    break;
                case '4':
                    this.color =  '#00FF00';
                    this.text_color = 'black';
                    break;
                case '5':
                    this.color =  '#66FF66';
                    this.text_color = 'black';
                    break;
            
                default:
                    break;
            }
        },
    }
})
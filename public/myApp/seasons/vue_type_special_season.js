var appTypeSpecialSeason = new Vue({
    el: '#typeSpecialSeason',
    data: {
        oData: oServerData,
        oDateUtils: new SDateUtils(),
        indexes: oServerData.indexes,
        lSpecialSeasonType: oServerData.lSpecialSeasonType,
        copylSpecialSeasonType: oServerData.lSpecialSeasonType,
        id_special_season_type: null,
        name: null,
        key_code: null,
        priority: 1,
        priorityclass: 'priority_1',
        color: 'priority_1',
        text_color: 'white',
        description: null,
    },
    mounted(){

    },
    methods: {
        initView(){
            this.checkLastPriority();
        },

        showModal(data = null){
            if(data == null){
                this.id_special_season_type = null;
                this.name = null;
                this.key_code = null;
                this.priority = 1;
                this.color = 'priority_1';
                this.text_color = 'white';
                this.description = null;
                this.checkLastPriority();
                this.changeColor();
                if(this.priority >= 5){
                    SGui.showMessage('Solo puedes agregar 5 tipos de temporada especial', 'Elimina un registro para continuar', 'info')
                    return;
                }
            }else{
                this.id_special_season_type = data[this.indexes['id_special_season_type']];
                this.name = data[this.indexes['name']];
                this.key_code = data[this.indexes['key_code']];
                this.priority = data[this.indexes['priority']];
                this.color = data[this.indexes['color']];
                this.text_color = data[this.indexes['priority']] > 3 ? 'white' : 'black';
                this.description = data[this.indexes['description']];
                this.priorityclass = 'priority_' + this.priority;
            }
            $('#modal_season_type').modal('show');
        },

        saveSeasonType(){
            SGui.showWaiting(15000);
            $('#modal_season_type').modal('hide');
            if(this.id_special_season_type == null){
                var route = this.oData.SeasonTypeSaveRoute;
            }else{
                var route =  this.oData.SeasonTypeUpdateRoute;
            }

            this.changeColor();

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
                    this.copylSpecialSeasonType = data.lSpecialSeasonType;
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

        deleteRegistry(){
            Swal.fire({
                title: 'Se eliminará la última temporada especial',
                text: 'Desea continuar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deleteSeasonType();
                }
            })
        },

        deleteSeasonType(){
            SGui.showWaiting(15000);
            axios.post(this.oData.SeasonTypeDeleteRoute, {
                
            })
            .then(response => {
                var data = response.data;
                if(data.success){
                    this.copylSpecialSeasonType = data.lSpecialSeasonType;
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
                case 1:
                    this.color =  'priority_1';
                    this.text_color = 'white';
                    break;
                case 2:
                    this.color =  'priority_2';
                    this.text_color = 'white';
                    break;
                case 3:
                    this.color =  'priority_3';
                    this.text_color = 'black';
                    break;
                case 4:
                    this.color =  'priority_4';
                    this.text_color = 'black';
                    break;
                case 5:
                    this.color =  'priority_5';
                    this.text_color = 'black';
                    break;
            
                default:
                    break;
            }
        },

        checkLastPriority(){
            for (const st of this.copylSpecialSeasonType) {
                if(st.priority >= this.priority){
                    this.priority = st.priority + 1;
                    this.priorityclass = 'priority_' + this.priority;
                }
            }
        },
    }
})
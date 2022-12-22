var appSpecialSeason = new Vue({
    el: '#specialSeason',
    data: {
        oData: oServerData,
        lDeptos: oServerData.lDeptos,
        lAreas: oServerData.lAreas,
        lEmp: oServerData.lEmp,
        lComp: [],
        title: 'Departamento',
        colorTitle: 'colorDepartamentoTitle',
        colorBody: 'colorDepartamento',
        display_seasons: false,
        table_class: [],
        lOptions: [],
        lSpecialSeason: [],
    },
    mounted(){
        let self = this;
        var datalDeptos = [];
        for(var i = 0; i<self.lDeptos.length; i++){
            datalDeptos.push({id: self.lDeptos[i].id_department, text: self.lDeptos[i].department_name_ui});
        }

        $('#selOptions')
            .select2({
                placeholder: 'selecciona',
                data: datalDeptos,
            });

        this.btn_SeasonActive('btn_depto', '#38c172');
    },
    methods: {
        initView(){
            console.log('appSpecialSeason');
        },

        btn_SeasonActive(id, color) {
            const btn_ids = ['btn_depto', 'btn_area', 'btn_emp', 'btn_comp'];
            let btn = document.getElementById(id);
            btn.style.backgroundColor = color;
            btn.style.color = '#fff';

            for (const bt_id of btn_ids) {
                if(bt_id != id){
                    let bt = document.getElementById(bt_id);
                    bt.style.backgroundColor = '#fff';
                    bt.style.color = 'black';
                    bt.style.boxShadow = '0 0 0';
                }
            }
        },

        SetDepto(){
            this.btn_SeasonActive('btn_depto', '#38c172');
            this.cleanOptions();
            $('#selOptions').empty().trigger("change");
            var datalDeptos = [];
            for(var i = 0; i<this.lDeptos.length; i++){
                datalDeptos.push({id: this.lDeptos[i].id_department, text: this.lDeptos[i].department_name_ui});
            }

            $('#selOptions')
                .select2({
                    placeholder: 'selecciona',
                    data: datalDeptos,
                });
            this.title = 'Departamento';
            this.colorTitle = 'colorDepartamentoTitle';
            this.colorBody = 'colorDepartamento';
        },

        SetArea(){
            this.btn_SeasonActive('btn_area', '#6c757d');
            this.cleanOptions();
            $('#selOptions').empty().trigger("change");
            var datalAreas = [];
            for(var i = 0; i<this.lAreas.length; i++){
                datalAreas.push({id: this.lAreas[i].id_org_chart_job, text: this.lAreas[i].job_name});
            }

            $('#selOptions')
                .select2({
                    placeholder: 'selecciona',
                    data: datalAreas,
                });
            this.title = 'Area funcional';
            this.colorTitle = 'colorAreaTitle';
            this.colorBody = 'colorArea';
        },

        SetEmpleado(){
            this.btn_SeasonActive('btn_emp', '#6cb2eb');
            this.cleanOptions();
            $('#selOptions').empty().trigger("change");
            var datalEmp = [];
            for(var i = 0; i<this.lEmp.length; i++){
                datalEmp.push({id: this.lEmp[i].id, text: this.lEmp[i].full_name_ui});
            }

            $('#selOptions')
                .select2({
                    placeholder: 'selecciona',
                    data: datalEmp,
                });
            this.title = 'Empleado';
            this.colorTitle = 'colorEmpleadoTitle';
            this.colorBody = 'colorEmpleado';
        },

        SetEmpresa(){
            this.btn_SeasonActive('btn_comp', '#ffed4a');
            this.cleanOptions();
            $('#selOptions').empty().trigger("change");
            this.title = 'Empresa';
            this.colorTitle = 'colorEmpresaTitle';
            this.colorBody = 'colorEmpresa';
        },

        init(){
            var options = $('#selOptions').select2("data");
            if(options.length > 0){
                axios.post(this.oData.getSpecialSeasonRoute, {
                    'options': options,
                    'type': this.title,
                })
                .then( result => {
                    let data = result.data;
                    if(data.success){
                        this.lOptions = options;
                        this.lSpecialSeason = data.lSpecialSeason;
                        let oSpecialSeason = null;
                        for (const opt of options) {
                            oSpecialSeason = this.lSpecialSeason.find(({ id_special_season }) => id_special_season === opt.id);
                            let seasons = [];
                            if(oSpecialSeason != undefined && oSpecialSeason != null){
                                for (const oSeason of oSpecialSeason) {
                                    switch (oSeason.priority) {
                                        case 1:
                                            seasons.push({ class: 'priority_1', priority: oSeason.priority});
                                            break;
                                        case 2:
                                            seasons.push({ class: 'priority_2', priority: oSeason.priority});
                                            break;
                                        case 3:
                                            seasons.push({ class: 'priority_3', priority: oSeason.priority});
                                            break;
                                        case 4:
                                            seasons.push({ class: 'priority_4', priority: oSeason.priority});
                                            break;
                                        case 5:
                                            seasons.push({ class: 'priority_5', priority: oSeason.priority});
                                            break;
                                    
                                        default:
                                            seasons.push({ class: 'priority_0', priority: 0});
                                            break;
                                    }
                                }
                            }else{
                                for (let i = 0; i < 12; i++) {
                                    seasons.push({ class: 'priority_0', priority: 0});
                                }
                            }
                            this.table_class[opt.text] = seasons;
                        }
                        this.display_seasons = true;
                    }else{
                        SGui.showMessage('', data.message, data.icon);
                    }
                })
                .catch(function(error){
                    console.log(error);
                    SGui.showError(error);
                });
            }else{
                SGui.showMessage('', 'Debe seleccionar al menos una de las opciones', 'info');
                this.cleanOptions();
            }
        },

        setSpecialSeason(key, index){
            switch (this.table_class[key][index].priority) {
                case 0:
                    this.table_class[key][index].priority = 5;
                    this.table_class[key][index].class = 'priority_5';
                    break;
                case 1:
                    this.table_class[key][index].priority--;
                    this.table_class[key][index].class = 'priority_0';
                    break;
                case 2:
                    this.table_class[key][index].priority--;
                    this.table_class[key][index].class = 'priority_1';
                    break;
                case 3:
                    this.table_class[key][index].priority--;
                    this.table_class[key][index].class = 'priority_2';
                    break;
                case 4:
                    this.table_class[key][index].priority--;
                    this.table_class[key][index].class = 'priority_3';
                    break;
                case 5:
                    this.table_class[key][index].priority--;
                    this.table_class[key][index].class = 'priority_4';
                    break;
            
                default:
                    this.table_class[key][index].priority = 0;
                    this.table_class[key][index].class = 'priority_0';
                    break;
            }
            this.$forceUpdate();
        },

        cleanOptions(){
            this.display_seasons = false;
            this.lOptions = [];
            this.lSpecialSeason = [];
            this.table_class = [];
        }
    }
});
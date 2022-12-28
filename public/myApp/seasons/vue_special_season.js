var appSpecialSeason = new Vue({
    el: '#specialSeason',
    data: {
        oData: oServerData,
        lDeptos: oServerData.lDeptos,
        lAreas: oServerData.lAreas,
        lEmp: oServerData.lEmp,
        lCompany: oServerData.lCompany,
        title: 'Departamento',
        type: 'depto',
        colorTitle: 'colorDepartamentoTitle',
        colorBody: 'colorDepartamento',
        display_seasons: false,
        table_class: {},
        lOptions: [],
        lSpecialSeason: [],
        lSpecialSeasonType: [],
        year: oServerData.year,
        months: [
                'Enero',
                'Febrero',
                'Marzo',
                'Abril',
                'Mayo',
                'Junio',
                'Julio',
                'Agosto',
                'Septiembre',
                'Octubre',
                'Noviembre',
                'Diciembre'
            ],
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
            this.SetDepto();
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
            this.type = 'depto';
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
            this.type = 'area';
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
            this.type = 'emp';
        },

        SetEmpresa(){
            this.btn_SeasonActive('btn_comp', '#ffed4a');
            this.cleanOptions();
            $('#selOptions').empty().trigger("change");
            var datalCompany = [];
            for(var i = 0; i<this.lCompany.length; i++){
                datalCompany.push({id: this.lCompany[i].id_company, text: this.lCompany[i].company_name_ui});
            }

            $('#selOptions')
                .select2({
                    placeholder: 'selecciona',
                    data: datalCompany,
                });
            this.title = 'Empresa';
            this.colorTitle = 'colorEmpresaTitle';
            this.colorBody = 'colorEmpresa';
            this.type = 'comp';
        },

        init(){
            var options = $('#selOptions').select2("data");
            if(options.length > 0){
                SGui.showWaiting(10000);
                axios.post(this.oData.getSpecialSeasonRoute, {
                    'options': options,
                    'type': this.type,
                    'year': this.year,
                })
                .then( result => {
                    let data = result.data;
                    if(data.success){
                        this.lOptions = options;
                        this.lSpecialSeason = data.lSpecialSeason;
                        this.lSpecialSeasonType = data.lSpecialSeasonType;
                        let oSpecialSeason = null;
                        for (const opt of options) {
                            switch (this.type) {
                                case 'depto':
                                    oSpecialSeason = this.lSpecialSeason.filter(({ depto_id }) => depto_id == opt.id);
                                    break;
                                case 'area':
                                    oSpecialSeason = this.lSpecialSeason.filter(({ org_chart_job_id }) => org_chart_job_id == opt.id);
                                    break;
                                case 'emp':
                                    oSpecialSeason = this.lSpecialSeason.filter(({ user_id }) => user_id == opt.id);
                                    break;
                                case 'comp':
                                    oSpecialSeason = this.lSpecialSeason.filter(({ company_id }) => company_id == opt.id);
                                    break;
                                default:
                                    break;
                            }
                            
                            let seasons = {};
                            if(oSpecialSeason != undefined && oSpecialSeason != null){
                                for (const oSeason of oSpecialSeason) {
                                    seasons[oSeason.month] = {
                                                        class: oSeason.color,
                                                        priority: oSeason.priority,
                                                        text: oSeason.name,
                                                        season_id: oSeason.id_special_season,
                                                        type: this.type,
                                                        id_type: opt.id,
                                                    };
                                }
                                let keys = Object.keys(seasons);
                                for (let i = 0; i < this.months.length; i++) {
                                    let res = keys.find(element => element == this.months[i]);
                                    if(res == undefined){
                                        seasons[this.months[i]] = { class: 'priority_0',
                                                                    priority: 0,
                                                                    text: '',
                                                                    season_id: null,
                                                                    type: this.type,
                                                                    id_type: opt.id,
                                                                };
                                    }
                                }
                            }else{
                                for (let i = 0; i < this.months.length; i++) {
                                    seasons[this.months[i]] = { class: 'priority_0',
                                                                priority: 0,
                                                                text: '',
                                                                season_id: null,
                                                                type: this.type,
                                                                id_type: opt.id,
                                                            };
                                }
                            }
                            this.table_class[opt.text] = seasons;
                        }
                        this.display_seasons = true;
                        this.$forceUpdate();
                        swal.close();
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
            let result;
            switch (this.table_class[key][index].priority) {
                case 0:
                    result = this.lSpecialSeasonType.find(({ priority }) => priority === 5);
                    if(result != undefined && result != null){
                        this.table_class[key][index].priority = 5;
                        this.table_class[key][index].class = 'priority_5';
                        this.table_class[key][index].text = result.name;
                        // this.table_class[key][index].season_id = result.id_special_season;
                    }else{
                        this.table_class[key][index].priority = 0;
                        this.table_class[key][index].class = 'priority_0';
                        this.table_class[key][index].text = '';
                        // this.table_class[key][index].season_id = '';
                    }
                    break;

                case 1:
                        this.table_class[key][index].priority = 0;
                        this.table_class[key][index].class = 'priority_0';
                        this.table_class[key][index].text = '';
                        // this.table_class[key][index].season_id = '';
                    break;

                case 2:
                    result = this.lSpecialSeasonType.find(({ priority }) => priority === 1);
                    if(result != undefined && result != null){
                        this.table_class[key][index].priority--;
                        this.table_class[key][index].class = 'priority_1';
                        this.table_class[key][index].text = result.name;
                        // this.table_class[key][index].season_id = result.id_special_season;
                    }else{
                        this.table_class[key][index].priority = 0;
                        this.table_class[key][index].class = 'priority_0';
                        this.table_class[key][index].text = '';
                        // this.table_class[key][index].season_id = '';
                    }
                    break;

                case 3:
                    result = this.lSpecialSeasonType.find(({ priority }) => priority === 2);
                    if(result != undefined && result != null){
                        this.table_class[key][index].priority--;
                        this.table_class[key][index].class = 'priority_2';
                        this.table_class[key][index].text = result.name;
                        // this.table_class[key][index].season_id = result.id_special_season;
                    }else{
                        this.table_class[key][index].priority = 0;
                        this.table_class[key][index].class = 'priority_0';
                        this.table_class[key][index].text = '';
                        // this.table_class[key][index].season_id = '';
                    }
                    break;

                case 4:
                    result = this.lSpecialSeasonType.find(({ priority }) => priority === 3);
                    if(result != undefined && result != null){
                        this.table_class[key][index].priority--;
                        this.table_class[key][index].class = 'priority_3';
                        this.table_class[key][index].text = result.name;
                        // this.table_class[key][index].season_id = result.id_special_season;
                    }else{
                        this.table_class[key][index].priority = 0;
                        this.table_class[key][index].class = 'priority_0';
                        this.table_class[key][index].text = '';
                        // this.table_class[key][index].season_id = '';
                    }
                    break;

                case 5:
                    result = this.lSpecialSeasonType.find(({ priority }) => priority === 4);
                    if(result != undefined && result != null){
                        this.table_class[key][index].priority--;
                        this.table_class[key][index].class = 'priority_4';
                        this.table_class[key][index].text = result.name;
                        // this.table_class[key][index].season_id = result.id_special_season;
                    }else{
                        this.table_class[key][index].priority = 0;
                        this.table_class[key][index].class = 'priority_0';
                        this.table_class[key][index].text = '';
                        // this.table_class[key][index].season_id = '';
                    }
                    break;
            
                default:
                    this.table_class[key][index].priority = 0;
                    this.table_class[key][index].class = 'priority_0';
                    this.table_class[key][index].text = '';
                    // this.table_class[key][index].season_id = '';
                    break;
            }
            this.$forceUpdate();
        },

        cleanOptions(){
            this.display_seasons = false;
            this.lOptions = [];
            this.lSpecialSeason = [];
            this.table_class = {};
        },

        saveSpecialSeasons(){
            SGui.showWaiting(15000);
            axios.post(this.oData.saveSpecialSeasonRoute,{
                'table_class': this.table_class,
                'type': this.type,
                'year': this.year,
            })
            .then( response => {
                let data  = response.data;
                if(data.success){
                    this.init();
                    swal.close();
                    SGui.showOk();
                }else{
                    this.init();
                    swal.close();
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function(error){
                this.init();
                swal.close();
                console.log(error);
                SGui.showError(error);
            });
        },

        copySeasonToNextYear(){
            (async () => {
                if (await SGui.showConfirm('Se actualizarán los registros del proximo año','Desea continuar?','warning')) {
                    SGui.showWaiting(15000);
                    axios.post(this.oData.copyToNextYearRoute,{
                        'table_class': this.table_class,
                        'type': this.type,
                        'year': (this.year + 1),
                    })
                    .then( response => {
                        let data  = response.data;
                        if(data.success){
                            swal.close();
                            SGui.showOk();
                        }else{
                            swal.close();
                            SGui.showMessage('', data.message, data.icon);
                        }
                    })
                    .catch(function(error){
                        swal.close();
                        console.log(error);
                        SGui.showError(error);
                    });
                }
            })();
        }
    }
});
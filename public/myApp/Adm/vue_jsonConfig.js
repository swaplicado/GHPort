var app = new Vue({
    el: '#specialType',
    data: {
        oData: oServerData,
        route: oServerData.route,
        lOrgchart: oServerData.lOrgchart,
        selRoot: 0,
        selDefault: 0,
        idRoot: oServerData.idRoot,
        idDefault: oServerData.idDefault,
    },
    mounted() {
        let self = this;
        var datalRoot = [];
        var datalDefault = [];

        for (var i = 0; i < self.lOrgchart.length; i++) {
            datalRoot.push({ id: self.lOrgchart[i].id_org_chart_job, text: self.lOrgchart[i].job_name_ui });
            datalDefault.push({ id: self.lOrgchart[i].id_org_chart_job, text: self.lOrgchart[i].job_name_ui });
        }

        $('#selRoot')
            .select2({
                placeholder: 'selecciona nodo',
                data: datalRoot,
            }).on('select2:select', function(e) {
                self.selRoot = e.params.data.id;
            });

        $('#selDefault')
            .select2({
                placeholder: 'selecciona nodo',
                data: datalDefault,
            }).on('select2:select', function(e) {
                self.selDefault = e.params.data.id;
            });

        $('#selRoot').val(self.idRoot).trigger('change');
        $('#selDefault').val(self.idDefault).trigger('change');
    },
    methods: {
        save() {
            SGui.showWaiting(15000);
            axios.post(this.route, {
                    'id_root': this.selRoot,
                    'id_default': this.selDefault,
                })
                .then(result => {
                    let data = result.data;
                    if (data.success) {
                        SGui.showOk();
                    } else {
                        SGui.showMessage('', data.message, data.icon);
                    }
                })
                .catch(function(error) {
                    console.log(error);
                    SGui.showError();
                });
        },

    }
})
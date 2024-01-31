var app = new Vue({
    el: '#applicationsApp',
    data: {
        oData: oServerData,
        indexesApplications: oServerData.indexesApplications,
        lEmployees: oServerData.lEmployees,
        lApplications: oServerData.lApplications,
        lClases: oServerData.lClases,
        lStatus: oServerData.lStatus,
        lConstants: oServerData.lConstants,
        clase: oServerData.lConstants['CLASE_TODO'],
        status: oServerData.lConstants['APPLICATION_ENVIADO'],
        filterEmployee: 0,
        oApplication: null,
        oEmployee: null,
    },
    mounted() {
        var self = this;
        $('.select2-class').select2({});

        $('#filtro_clase').select2({
            data: self.lClases,
        }).on('select2:select', function(e) {
            self.clase = e.params.data.id;
        });

        $('#filtro_status').select2({
            data: self.lStatus,
        }).on('select2:select', function(e) {
            self.status = e.params.data.id;
        });

        $('#filtro_employee').select2({
            data: self.lEmployees,
        }).on('select2:select', function(e) {
            self.filterEmployee = e.params.data.id;
        });
    },
    methods: {
        drawApplicationsTable(lApplications){
            var dataApplications = [];
            for(let application in lApplications){
                dataApplications.push(
                    [
                        application.request_id,
                        application.request_class_id,
                        application.request_status_id,
                        application.employee_id,
                        application.folio_n,
                        application.employee,
                        application.request_class,
                        application.request_type,
                        application.start_date,
                        application.end_date,
                        application.time,
                        application.status,
                        application.date_send_n
                    ]
                );
            }
            table['applications_table'].clear().draw();
            table['applications_table'].rows.add(dataApplications).draw();
        },

        getApplication(request_class, request_id, employee_id){
            let route = this.oData.getApplicationRoute;

            return new Promise(resolve => {
                axios.post(route, {
                    'request_class': request_class,
                    'request_id': request_id,
                    'employee_id': employee_id
                })
                .then(result => {
                    let data = result.data;
                    if(data.success){
                        this.oApplication = data.application;
                        this.oEmployee = data.employee;
                        resolve(data.application);
                    }else{
                        SGui.showError(data.message);
                    }
                })
                .catch(function(error){
                    console.log(error);
                    SGui.showError(error);
                });
            });
        },

        async showDataModal(data){
            SGui.showWaiting();
            await this.getApplication(
                data[this.indexesApplications.request_class_id],
                data[this.indexesApplications.request_id],
                data[this.indexesApplications.employee_id]
            );
        },

        goToRequest(){
            if(table['applications_table'].row('.selected').data() == undefined){
                SGui.showMessage('','Debe seleccionar un renglón','info');
                return;
            }
            SGui.showWaiting();
            let data = table['applications_table'].row('.selected').data();
            let route = '';
            switch (parseInt(data[this.indexesApplications.request_class_id])) {
                case this.lConstants['VACACIONES']:
                    route = this.oData.vacationsRoute + "/" + data[this.indexesApplications.request_id];
                    break;
                case this.lConstants['INCIDENCIA']:
                    route = this.oData.incidencesRoute + "/" + data[this.indexesApplications.request_id];
                    break;
                case this.lConstants['PERMISO_PERSONAL_HORAS']:
                    route = this.oData.permisoPersonalRoute + "/" + data[this.indexesApplications.request_id];
                    break;
                    case this.lConstants['PERMISO_LABORAL_HORAS']:
                    route = this.oData.permisoLaboralRoute + "/" + data[this.indexesApplications.request_id];
                    break;
            
                default:
                    break;
            }
            window.location.href = route;
        }
    }
})
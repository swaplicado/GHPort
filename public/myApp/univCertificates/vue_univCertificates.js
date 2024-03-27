var app = new Vue({
    el: '#certificatesApp',
    data: {
        oData: oServerData,
        indexesEmployeesTable: oServerData.indexesEmployeesTable,
        indexesCuadrantsTable: oServerData.indexesCuadrantsTable,
        lEmployees: oServerData.lEmployees,
        lEmployeesToCuadrants: [],
        lEmployeesCuadrants: [],
        filterType: [],
        AssignmentsToCertificates: [],
        oUser: oServerData.oUser,
        viewMode: 'myCert',
        roles: oServerData.roles,
    },
    mounted(){
        self = this;

        $('.select2-class').select2({});

        var dataFilterWithCertificate = [
            {'id': 0, 'text': 'Con certificado'},
            {'id': 1, 'text': 'Todos'},
        ];
        $('#filter_withCertificate').select2({
            data: dataFilterWithCertificate,
        }).on('select2:select', function(e) {
            
        });

        if(this.oUser.rol_id == this.roles.ESTANDAR){
            let myCert = document.getElementById('myCert');
            myCert.click();
        }
    },
    methods: {
        clearData(){
            this.lEmployeesToCuadrants = [];
            this.lEmployeesCuadrants = [];
            this.AssignmentsToCertificates = [];
        },

        setlEmployeesCuadrants(){
            data = table['employees_table'].rows({ selected: true }).data().toArray();

            this.lEmployeesToCuadrants = [];
            data.forEach(item => {
                this.lEmployeesToCuadrants.push(item[this.indexesEmployeesTable.id_employee]);
            });
        },

        getCuadrants(){
            if(this.viewMode == 'empCert'){
                let arrData = table['employees_table'].rows({ selected: true }).data().toArray();
                if(arrData.length == 0){
                    SGui.showMessage('', 'Debe seleccionar un renglon', 'warning');
                    return;
                }
                SGui.showWaiting();
                this.setlEmployeesCuadrants();
            }else if(this.viewMode == 'myCert'){
                SGui.showWaiting();
                this.lEmployeesToCuadrants = [];
                this.lEmployeesToCuadrants.push(this.oUser.id);
            }
            let route = this.oData.getCuadrantsRoute;

            axios.post(route, {
                'lEmployeesToCuadrants': this.lEmployeesToCuadrants,
            })
            .then(response => {
                let data = response.data;
                if(data.success){
                    this.lEmployeesCuadrants = data.lEmployeesCuadrants;
                    this.drawCuadrantsTable(this.lEmployeesCuadrants);
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(error => {
                console.log(error);
                SGui.showError(error);
            })
        },

        /**Dibuja la tabla de cuadrantes */
        drawCuadrantsTable(lEmployeesToCuadrants){
            var datalEmployeesToCuadrants = [];
            for(let emp of lEmployeesToCuadrants){
                for(let cuadrant of emp.cuadrants){
                    if(cuadrant.asignado != 1){
                        continue;
                    }
                    datalEmployeesToCuadrants.push(
                        [
                            emp.id,
                            cuadrant.asignado == 1 ? cuadrant.id_assignment : '',
                            1,
                            cuadrant.id_cuadrant,
                            '',
                            '',
                            cuadrant.withCertificate,
                            emp.full_name,
                            cuadrant.cuadrant_name,
                            '',
                            'Cuadrante: ' + cuadrant.cuadrant_name,
                            cuadrant.asignado == 1 ? cuadrant.cuadrantStatus : 'No asignado',
                        ]
                    );
                    if(typeof cuadrant.modules !== 'undefined'){
                        for(let module of cuadrant.modules){
                            datalEmployeesToCuadrants.push(
                                [
                                    emp.id,
                                    cuadrant.id_assignment,
                                    2,
                                    cuadrant.id_cuadrant,
                                    module.id_module,
                                    '',
                                    module.withCertificate,
                                    emp.full_name,
                                    cuadrant.cuadrant_name,
                                    module.module,
                                    'Módulo: ' + module.module,
                                    module.moduleStatus,
                                ]
                            );
                            if(typeof module.courses !== 'undefined'){
                                for(let course of module.courses){
                                    // if(course.courseStatus != "Aprobado"){
                                    //     continue;
                                    // }
                                    datalEmployeesToCuadrants.push(
                                        [
                                            emp.id,
                                            cuadrant.id_assignment,
                                            3,
                                            cuadrant.id_cuadrant,
                                            module.id_module,
                                            course.id_course,
                                            course.withCertificate,
                                            emp.full_name,
                                            cuadrant.cuadrant_name,
                                            module.module,
                                            course.course,
                                            course.courseStatus,
                                        ]
                                    );
                                }
                            }
                        }
                    }
                }
            }

            // this.filterCuadrantTable();

            table['cuadrants_table'].clear().draw();
            table['cuadrants_table'].rows.add(datalEmployeesToCuadrants).draw();

            for(let i = 0; i < datalEmployeesToCuadrants.length; i++){
                switch (datalEmployeesToCuadrants[i][this.indexesCuadrantsTable.id_type]) {
                    case 1:
                        // table['cuadrants_table'].row(i).nodes().to$().addClass('cuadrantColor');
                        let dataCuadrant = table['cuadrants_table'].row(i).data();
                        if(dataCuadrant[this.indexesCuadrantsTable.status] != 'Cursado'){
                            table['cuadrants_table'].row(i).nodes().to$().addClass('noSelectableRow');
                        }
                        table['cuadrants_table'].row(i).nodes().to$().addClass('dtrg-level-1');
                        break;
                    case 2:
                        // table['cuadrants_table'].row(i).nodes().to$().addClass('moduleColor');
                        let dataModule = table['cuadrants_table'].row(i).data();
                        if(dataModule[this.indexesCuadrantsTable.status] != 'Cursado'){
                            table['cuadrants_table'].row(i).nodes().to$().addClass('noSelectableRow');
                        }
                        table['cuadrants_table'].row(i).nodes().to$().addClass('dtrg-level-2');
                        break;
                    case 3:
                        // table['cuadrants_table'].row(i).nodes().to$().addClass('courseColor');
                        break;
                    default:
                        break;
                }
                
                if(!datalEmployeesToCuadrants[i][this.indexesCuadrantsTable.withCertificate]){
                    table['cuadrants_table'].row(i).nodes().to$().addClass('noSelectableRow');
                }
            }
        },

        filterCuadrantTable(){
            this.filterType = [];
            let checkboxCuadrant = document.getElementById('checkCuadrants');
            let checkboxModule = document.getElementById('checkModules');
            let checkboxCourse = document.getElementById('checkCourses');

            if(checkboxCuadrant.checked){
                this.filterType.push(1);
            }
            if(checkboxModule.checked){
                this.filterType.push(2);
            }
            if(checkboxCourse.checked){
                this.filterType.push(3);
            }

            table['cuadrants_table'].draw();
        },

        setlAssignmentsToCertificates(){
            data = table['cuadrants_table'].rows({ selected: true }).data().toArray();
            this.AssignmentsToCertificates = [];
            data.forEach(item => {
                this.AssignmentsToCertificates.push(
                    {
                        'id_employee_univ': item[this.indexesCuadrantsTable.id_employee_univ],
                        'employee': item[this.indexesCuadrantsTable.Colaborador],
                        'type': item[this.indexesCuadrantsTable.id_type],
                        'id_assignment': item[this.indexesCuadrantsTable.id_assigment],
                        'id_cuadrant': item[this.indexesCuadrantsTable.id_cuadrant],
                        'id_module': item[this.indexesCuadrantsTable.id_module],
                        'id_course': item[this.indexesCuadrantsTable.id_course],
                    }
                );
            });
        },

        findInTable(valorABuscar, indexABuscar){
            var filaCoincidente = null;
            var datosFilas = table['cuadrants_table'].rows().data();
            // Iterar sobre los datos de las filas para buscar la coincidencia
            for (var i = 0; i < datosFilas.length; i++) {
                var fila = datosFilas[i];
                if (fila[indexABuscar] == valorABuscar) {
                    filaCoincidente = fila;
                    break;
                }
            }

            if (filaCoincidente !== null) {
                console.log("Fila coincidente:", filaCoincidente);
                return filaCoincidente;
            } else {
                console.log("No se encontró ninguna coincidencia.");
                return null;
            }

        },

        getCertificates(){
            let arrData = table['cuadrants_table'].rows({ selected: true }).data().toArray();
            if(arrData.length == 0 && lRowsCuadrants.length == 0 && lRowsModules.length == 0){
                SGui.showMessage('', 'Debe seleccionar un renglon', 'warning');
                return;
            }
            SGui.showWaiting();
            let route = this.oData.getCertificatesRoute;
            this.setlAssignmentsToCertificates();

            axios({
                method: 'post',
                url: route,
                data: {
                    'AssignmentsToCertificates': this.AssignmentsToCertificates
                },
                responseType: 'blob' // Asegúrate de especificar 'blob' como el tipo de respuesta
            })
            .then(response => {
                // Crear un objeto de URL para el archivo zip y simular un clic en un enlace para descargarlo
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'certificados_univAETH.zip');
                document.body.appendChild(link);
                link.click();
                SGui.showOk();
            })
            .catch(error => {
                console.log(error);
                SGui.showError(error);
            });
        },

        drawEmployeesTable(lEmployees){
            let datalEmployees = [];
            for(let emp of lEmployees){
                datalEmployees.push(
                    [
                        emp.id,
                        emp.full_name,
                        emp.employee_num,
                        emp.area,
                        emp.department_name_ui,
                        emp.job_name_ui,
                    ]
                );
            }

            table['employees_table'].clear().draw();
            table['employees_table'].rows.add(datalEmployees).draw();
        },

        getAllEmployees(){
            SGui.showWaiting();
            let route;
            let is_checked = document.getElementById('checkBoxAllEmployees').checked;
            if(is_checked){
                if(this.oUser.rol_id == this.roles.GH || this.oUser.rol_id == this.roles.ADMIN){
                    route = this.oData.getAllEmployeesRoute;
                }else if(this.oUser.rol_id == this.roles.JEFE){
                    route = this.oData.getAllMyEmployeesRoute;
                }
                axios.get(route, {
    
                })
                .then(response => {
                    let data = response.data;
                    if(data.success){
                        this.drawEmployeesTable(data.lEmployees);
                        SGui.showOk();
                    }else{
                        SGui.showMessage('', data.message, data.icon);
                    }
                })
                .catch(error => {
                    console.log(error);
                    SGui.showError(error);
                })
            }else{
                let route = this.oData.getMyEmployeesRoute;
                axios.get(route, {
                    
                })
                .then(response => {
                    let data = response.data;
                    if(data.success){
                        this.drawEmployeesTable(data.lEmployees);
                        SGui.showOk();
                    }else{
                        SGui.showMessage('', data.message, data.icon);
                    }
                })
                .catch(error => {
                    console.log(error);
                    SGui.showError(error);
                });
            }
        },

        setViewMode(mode){
            this.viewMode = mode;

            const btn_ids = ['myCert', 'empCert'];
            let btn = document.getElementById(mode);
            btn.style.backgroundColor = '#858796';
            btn.style.color = '#fff';

            for (const bt_id of btn_ids) {
                if (bt_id != mode) {
                    let bt = document.getElementById(bt_id);
                    bt.style.backgroundColor = '#fff';
                    bt.style.color = '#858796';
                    bt.style.boxShadow = '0 0 0';
                }
            }

            if(this.viewMode == 'myCert'){
                this.getCuadrants();
            }else if(this.viewMode == 'empCert'){
                table['cuadrants_table'].clear().draw();
            }
        }
    }
})
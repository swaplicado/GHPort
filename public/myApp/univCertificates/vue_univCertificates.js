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
    },
    mounted(){
        $('.select2-class').select2({});

        var dataFilterWithCertificate = [
            {'id': 0, 'text': 'Con certificado'},
            {'id': 1, 'text': 'Todos'},
        ];
        $('#filter_withCertificate').select2({
            data: dataFilterWithCertificate,
        }).on('select2:select', function(e) {
            
        });
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
            let arrData = table['employees_table'].rows({ selected: true }).data().toArray();
            if(arrData.length == 0){
                SGui.showMessage('', 'Debe seleccionar un renglon', 'warning');
                return;
            }
            SGui.showWaiting();
            this.setlEmployeesCuadrants();
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
                    datalEmployeesToCuadrants.push(
                        [
                            cuadrant.asignado == 1 ? cuadrant.id_assignment : '',
                            1,
                            cuadrant.id_cuadrant,
                            '',
                            '',
                            cuadrant.withCertificate,
                            emp.full_name,
                            cuadrant.cuadrant_name,
                            '',
                            '',
                            cuadrant.asignado == 1 ? cuadrant.cuadrantStatus : 'No asignado',
                        ]
                    );
                    if(typeof cuadrant.modules !== 'undefined'){
                        for(let module of cuadrant.modules){
                            datalEmployeesToCuadrants.push(
                                [
                                    cuadrant.id_assignment,
                                    2,
                                    cuadrant.id_cuadrant,
                                    module.id_module,
                                    '',
                                    module.withCertificate,
                                    emp.full_name,
                                    cuadrant.cuadrant_name,
                                    module.module,
                                    '',
                                    module.moduleStatus,
                                ]
                            );
                            if(typeof module.courses !== 'undefined'){
                                for(let course of module.courses){
                                    datalEmployeesToCuadrants.push(
                                        [
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

            this.filterCuadrantTable();

            table['cuadrants_table'].clear().draw();
            table['cuadrants_table'].rows.add(datalEmployeesToCuadrants).draw();

            for(let i = 0; i < datalEmployeesToCuadrants.length; i++){
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
                        'type': item[this.indexesCuadrantsTable.id_type],
                        'id_assignment': item[this.indexesCuadrantsTable.id_assigment],
                        'id_cuadrant': item[this.indexesCuadrantsTable.id_cuadrant],
                        'id_module': item[this.indexesCuadrantsTable.id_module],
                        'id_course': item[this.indexesCuadrantsTable.id_course],
                    }
                );
            });
        },

        getCertificates(){
            let arrData = table['cuadrants_table'].rows({ selected: true }).data().toArray();
            if(arrData.length == 0){
                SGui.showMessage('', 'Debe seleccionar un renglon', 'warning');
                return;
            }
            SGui.showWaiting();
            let route = this.oData.getCertificatesRoute;
            this.setlAssignmentsToCertificates();


            axios.post(route, {
                'AssignmentsToCertificates': this.AssignmentsToCertificates,
            })
            .then(response => {
                let data = response.data;
                if(data.success){
                    let base64Pdf = data.lCertificates;
                    const link = document.createElement('a');
                    link.href = `data:application/pdf;base64,${base64Pdf}`;
                    link.target = '_blank';
                    link.download  = 'archivo.pdf';  // Nombre de archivo para descargar
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link); // Opcional: eliminar el enlace después de hacer clic

                    // document.write("<iframe width='100%' height='100%' src='data:application/pdf;base64," 
                    // + encodeURI(base64Pdf) + "'></iframe>");

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
    }
})
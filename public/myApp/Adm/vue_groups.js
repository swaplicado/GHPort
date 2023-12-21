var app = new Vue({
    el: '#groupsApp',
    data: {
        oData: oServerData,
        lGroups: oServerData.lGroups,
        idGroup: null,
        groupName: null,
        isEdit: false,
        indexesGroupsTable: oServerData.indexesGroupsTable,

        idGroupToAssign: null,
        lEmployeesAssigned: [],
        lEmployeesNoAssigned:  [],
        indexesEmpAssign: oServerData.indexesEmpAssign,
        indexesEmpNoAssign: oServerData.indexesEmpNoAssign,
    },
    mounted(){

    },
    methods: {
        /**
         * Metodos para la vista de grupos de empleados  
         */

        /**Metodo para dibujar la tabla grupos */
        drawGroupsTable(lGroups){
            let dataGroups = [];
            for (let gp of lGroups) {
                dataGroups.push(
                    [
                        gp.id_group,
                        gp.name
                    ]
                )
            }

            table['groups_table'].clear().draw();
            table['groups_table'].rows.add(dataGroups).draw();
        },

        /**Limpia de data de vue */
        clearData(){
            this.isEdit = false;
            this.idGroup = null;
            this.groupName = null;
        },

        /**Muestra el modal de creacion o edicion de grupos */
        showModal(data){
            this.clearData();
            if(data != null){
                this.isEdit = true;
                this.idGroup = data[this.indexesGroupsTable.id_group];
                this.groupName = data[this.indexesGroupsTable.groupName];
            }

            $('#modal_groups').modal('show');
        },

        /**Metodo para guardar un grupo nuevo o guardar la edicion */
        saveGroup(){
            SGui.showWaiting(15000);
            let route;
            if(!this.isEdit){
                route = this.oData.saveRoute;
            }else{
                route = this.oData.updateRoute;
            }

            axios.post(route, {
                'idGroup': this.idGroup,
                'groupName': this.groupName,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.lGroups = data.lGroups;
                    this.drawGroupsTable(this.lGroups);
                    SGui.showOk();
                    $('#modal_groups').modal('hide');
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(error){
                console.log(error);
                SGui.showError(error);
            });
        },

        /**Metodo para confirmar eliminar grupo */
        deleteRegistry(data){
            Swal.fire({
                title: '¿Desea eliminar el grupo?',
                html: '<b>' + data[this.indexesGroupsTable.groupName] + '</b> ',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deleteGroup(data[this.indexesGroupsTable.id_group]);
                }
            })
        },

        /**Metodo para eliminar un grupo */
        deleteGroup(idGroup){
            SGui.showWaiting(15000);

            let route = this.oData.deleteRoute;
            axios.post(route, {
                'idGroup': idGroup,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.lGroups = data.lGroups;
                    this.drawGroupsTable(this.lGroups);
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function(error){
                console.log(error);
                SGui.showError(error);
            });
        },

        /*******************************************************************************************************************  */
        /**
         * Metodos para la asignacion de grupos a empleados
         */

        /**Metodo para limpiar los datos de la asignacion de grupos */
        clearDataToAssign(){
            this.lEmployeesAssigned = null;
            this.lEmployeesNoAssigned = null;
            this.idGroupToAssign = null;
            table['employeesNoAssignTable'].clear().draw();
            table['employeesAssignTable'].clear().draw();
        },

        /**Muestra el modal para la asignacion de grupo */
        showModalGroupAssign(){
            if (table['groups_table'].row('.selected').data() == undefined) {
                SGui.showError("Debe seleccionar un renglón");
                return;
            }
            this.clearDataToAssign();

            let data = table['groups_table'].row('.selected').data();
            this.idGroupToAssign = data[this.indexesGroupsTable.id_group];
            this.getGroupAssigned();
            $('#modal_groups_assign').modal('show');
        },

        /**metodo para obtener los empleados asignados a un grupo */
        getGroupAssigned(){
            SGui.showWaiting(15000);
            let route = this.oData.getUsersAssignRoute;
            axios.post(route, {
                'idGroup': this.idGroupToAssign,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    this.lEmployeesAssigned = data.lEmpAssigned;
                    this.lEmployeesNoAssigned = data.lEmpNoAssigned;
                    this.drawNoAssignEmployees(this.lEmployeesNoAssigned);
                    this.drawAssignEmployees(this.lEmployeesAssigned);
                    SGui.showOk();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(error){
                console.log(error);
                SGui.showError(error);
            });
        },

        /**Dibuja la tabla de empleados no asignados */
        drawNoAssignEmployees(lEmployeesNoAssigned){
            var dataEmployeesNoAssigned = [];
            for(let emp of lEmployeesNoAssigned){
                dataEmployeesNoAssigned.push(
                    [
                        emp.id_employee,
                        emp.employee,
                    ]
                );
            }
            table['employeesNoAssignTable'].clear().draw();
            table['employeesNoAssignTable'].rows.add(dataEmployeesNoAssigned).draw();
        },

        /**Dibuja la tabla de empleados asignados */
        drawAssignEmployees(lEmployeesAssigned){
            var dataEmployeesAssigned = [];
            for(let emp of lEmployeesAssigned){
                dataEmployeesAssigned.push(
                    [
                        emp.id_employee,
                        emp.employee,
                    ]
                );
            }
            table['employeesAssignTable'].clear().draw();
            table['employeesAssignTable'].rows.add(dataEmployeesAssigned).draw();
        },

        /**Metodo para pasar los renglones seleccionados de la tabla employeesNoAssignTable a la tabla employeesAssignTable*/
        passToEmpAssign(isAll){
            let data;
            if(isAll){
                data = table['employeesNoAssignTable'].rows().data().toArray();
            }else{
                data = table['employeesNoAssignTable'].rows({ selected: true }).data().toArray();
            }

            data.forEach(item => {
                this.lEmployeesAssigned.push(
                    {
                        'id_employee': item[this.indexesEmpAssign.id_employee],
                        'employee': item[this.indexesEmpAssign.employee]
                    }
                );

                let index = this.lEmployeesNoAssigned.findIndex(emp => emp.id_employee == item[this.indexesEmpAssign.id_employee])
                this.lEmployeesNoAssigned.splice(index, 1); 
            });

            this.lEmployeesAssigned.sort((a, b) => {
                const nameA = a.employee.toUpperCase();
                const nameB = b.employee.toUpperCase();
                if(nameA < nameB){
                    return -1;
                }
                if(nameA > nameB){
                    return 1;
                }
                return 0
            });

            table['employeesNoAssignTable'].search('');
            table['employeesAssignTable'].search('');

            this.drawNoAssignEmployees(this.lEmployeesNoAssigned);
            this.drawAssignEmployees(this.lEmployeesAssigned);
        },

        /**Metodo para pasar los renglones seleccionados de la tabla employeesAssignTable a la tabla employeesNoAssignTable*/
        passToEmpNoAssign(isAll){
            let data;
            if(isAll){
                data = table['employeesAssignTable'].rows().data().toArray();
            }else{
                data = table['employeesAssignTable'].rows({ selected: true }).data().toArray();
            }

            data.forEach(item => {
                this.lEmployeesNoAssigned.push(
                    {
                        'id_employee': item[this.indexesEmpNoAssign.id_employee],
                        'employee': item[this.indexesEmpNoAssign.employee]
                    }
                );

                let index = this.lEmployeesAssigned.findIndex(emp => emp.id_employee == item[this.indexesEmpNoAssign.id_employee])
                this.lEmployeesAssigned.splice(index, 1); 
            });

            this.lEmployeesNoAssigned.sort((a, b) => {
                const nameA = a.employee.toUpperCase();
                const nameB = b.employee.toUpperCase();
                if(nameA < nameB){
                    return -1;
                }
                if(nameA > nameB){
                    return 1;
                }
                return 0
            });

            table['employeesNoAssignTable'].search('');
            table['employeesAssignTable'].search('');

            this.drawNoAssignEmployees(this.lEmployeesNoAssigned);
            this.drawAssignEmployees(this.lEmployeesAssigned);
        },

        setAssign(){
            SGui.showWaiting(15000);
            let route = this.oData.setAssignRoute;
            axios.post(route, {
                'idGroup': this.idGroupToAssign,
                'lEmployeesAssigned': this.lEmployeesAssigned,
                'lEmployeesNoAssigned': this.lEmployeesNoAssigned,
            })
            .then( result => {
                let data = result.data;
                if(data.success){
                    SGui.showOk();
                    $('#modal_groups_assign').modal('hide');
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function(error){
                console.log(error);
                SGui.showError(error);
            });
        }
    }
});
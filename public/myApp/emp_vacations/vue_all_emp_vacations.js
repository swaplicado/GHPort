var app = new Vue({
    el: '#allEmpVacationApp',
    data: {
        oData: oServerData,
        oDateUtils: new SDateUtils(),
        lEmployees: oServerData.lEmployees,
        copylEmployees: [],
        emp: null,
        items: [],
        item: [],
        oReDrawTables: new SReDrawTables()
    },
    mounted(){
        this.copylEmployees = this.lEmployees;
    },
    methods: {
        getEmployees(index, emp_id, org_job_id, is_head_user){
            if(is_head_user && typeof this.copylEmployees[index].has_geted == 'undefined'){
                SGui.showWaiting(5000);
                this.copylEmployees[index].has_geted = true;
                var route = this.oData.getlEmployees_route;
                route = route.replace(':OrgjobId', org_job_id);
                
                axios.get(route, {
                })
                .then(response => {
                    var data = response.data;
                    var lEmployees_size = this.copylEmployees.length - 1;
                    this.copylEmployees = this.copylEmployees.concat(data);
                    for(var i = 0; i<data.length; i++){
                        var emp = data[i];
                        var head_accord ='<div class="card shadow mb-4">'
                            +'<a '
                                +'href="#id_'+emp.employee_num+'" class="d-block card-header py-3" data-toggle="collapse" '
                                +'role="button" aria-expanded="false" aria-controls="'+emp.employee_num+'" '
                                +'onclick="getEmployees('+(i + lEmployees_size)+',' +emp.id+','+ emp.org_chart_job_id+','+ emp.is_head_user+');"'
                            +'> '
                            +'<h6 class="m-0 font-weight-bold text-primary">'
                                +'<table style="width: 100%">'
                                    +'<tbody>'
                                        +'<tr>'
                                            +'<td style="width: 30%">'+emp.employee+'</td>'
                                            +'<td style="width: 10%; border-left: solid 1px rgb(172, 172, 172); text-align: center;">'
                                                if(emp.company_id == 1){
                                                    head_accord = head_accord
                                                        +'<img src="../img/aeth.png" width="50px" height="20px" alt="">';
                                                }else if(emp.company_id == 2){
                                                    head_accord = head_accord
                                                        +'<img src="../img/tron.png" width="25px" height="20px" alt="">';
                                                }else if(emp.company_id == 3){
                                                    head_accord = head_accord
                                                        +'<img src="../img/swap_logo_22.png" width="40px" height="20px" alt="">';
                                                }else if(emp.company_id == 5){
                                                    head_accord = head_accord
                                                        +'<img src="../img/ame.png" width="50px" height="20px" alt="">';
                                                }
                                            head_accord = head_accord
                                            +'</td>'
                                            +'<td style="width: 30%; border-left: solid 1px rgb(172, 172, 172); text-align: center;">Vacaciónes pendientes:'+emp.tot_vacation_remaining+' días</td>';
                                            if(emp.is_head_user){
                                                head_accord = head_accord
                                                +'<td style="width: 30%; border-left: solid 1px rgb(172, 172, 172); text-align: center;">'
                                                    +'Encargado de area'
                                                    +'<span class="bx bxs-group"></span>'
                                                +'</td>';
                                            }else{
                                                head_accord = head_accord
                                                +'<td style="width: 30%;"></td>'
                                            }
                                            head_accord = head_accord
                                        +'</tr>'
                                    +'</tbody>'
                                +'</table>'
                            +'</h6> '
                            +'</a> ';
    
                        var body_accord = '<div class="collapse" id="id_'+emp.employee_num+'">'
                            +'<div class="card-body">'
                                +'<div class="col-md-6 card border-left-primary">'
                                    +'<table style="margin-left: 10px;" id="table_info_'+emp.employee_num+'">'
                                        +'<thead>'
                                            +'<th></th>'
                                            +'<th></th>'
                                        +'</thead>'
                                        +'<tbody>'
                                            +'<tr>'
                                                +'<th>Nombre:</th>'
                                                +'<td>'+emp.full_name+'</td>'
                                            +'</tr>'
                                            +'<tr>'
                                                +'<th>Fecha ingreso:</th>'
                                                +'<td>'+this.oDateUtils.formatDate(emp.last_admission_date)+'</td>'
                                            +'</tr>'
                                            +'<tr>'
                                                +'<th>Antigüedad:</th>'
                                                +'<td>'+emp.antiquity+' al día de hoy</td>'
                                            +'</tr>'
                                            +'<tr>'
                                                +'<th>Departamento:</th>'
                                                +'<td>'+emp.department_name_ui+'</td>'
                                            +'</tr>'
                                            +'<tr>'
                                                +'<th>Puesto:</th>'
                                                +'<td>'+emp.job_name_ui+'</td>'
                                            +'</tr>'
                                            +'<tr>'
                                                +'<th>Plan de vacaciones:</th>'
                                                +'<td>'+emp.vacation_plan_name+'</td>'
                                            +'</tr>'
                                        +'</tbody>'
                                    +'</table>'
                                +'</div>'
                                +'<br>'
                                +'<div class="row">'
                                    +'<div class="col-md-12">'
                                        +'<div style="float: right;">'
                                            +'<button class="btn btn-primary" onclick="getHistoryVac(' + "'" + 'table_' + emp.employee_num + "'" + ',' + emp.id + ');' + '">Ver historial</button>'
                                            +'<button class="btn btn-secondary" onclick="hiddeHistory(' + "'" + 'table_' + emp.employee_num + "'" + ',' + emp.id + ');' + '">Ocultar historial</button>'
                                        +'</div>'
                                    +'</div>'
                                +'</div>'
                                +'<table class="table table-bordered" id="table_'+emp.employee_num+'">'
                                    +'<thead class="thead-light">'
                                        +'<th>Periodo</th>'
                                        +'<th>Aniversario</th>'
                                        +'<th>Vac. ganadas</th>'
                                        +'<th>Vac. gozadas</th>'
                                        +'<th>Vac. vencidas</th>'
                                        +'<th>Vac. solicitadas</th>'
                                        +'<th>Vac. pendientes</th>'
                                    +'</thead>'
                                    +'<tbody>';
                                    for(var vac of emp.vacation){
                                        body_accord = body_accord
                                            +'<tr>'
                                            +'<td>'+this.oDateUtils.formatDate(vac.date_start)+' a '+this.oDateUtils.formatDate(vac.date_end)+'</td>'
                                            +'<td>'+vac.id_anniversary+'</td>'
                                            +'<td>'+vac.vacation_days+'</td>'
                                            +'<td>'+vac.num_vac_taken+'</td>'
                                            +'<td>'+vac.expired+'</td>'
                                            +'<td>'+vac.request+'</td>';
    
                                            if(vac.remaining < 0){
                                                body_accord = body_accord
                                                    +'<td style="color: red">'+vac.remaining+'</td>';
                                            }else{
                                                body_accord = body_accord
                                                    +'<td>'+vac.remaining+'</td>';
                                            }
                                            body_accord = body_accord
                                        +'</tr>';
                                    }
                                    body_accord = body_accord
                                        +'<tr class="thead-light">'
                                            +'<td></td>'
                                            +'<th>Total</th>'
                                            +'<td>'+emp.tot_vacation_days+'</td>'
                                            +'<td>'+emp.tot_vacation_taken+'</td>'
                                            +'<td>'+emp.tot_vacation_expired+'</td>'
                                            +'<td>0</td>';
                                            if(emp.tot_vacation_remaining < 0){
                                                body_accord = body_accord 
                                                +'<td style="color: red;">'+emp.tot_vacation_remaining+'</td>';
                                            }else{
                                                body_accord = body_accord
                                                +'<td>'+emp.tot_vacation_remaining+'</td>';
                                            }
                                            body_accord = body_accord
                                        +'</tr>'
                                    +'</tbody>'
                                +'</table>'
                                +'<br>'
                                +'<span id="id_span_'+emp.id+'"></span></span>'
                            +'</div>'
                        +'</div>'
                    +'</div>';
    
                        var html = head_accord + body_accord;
    
                        var id = 'id_span_'+emp_id;
    
                        var temp = document.createElement('div');
                        temp.innerHTML = html;
    
                        document.getElementById(id).appendChild(temp);
                        load('table_info_'+emp.employee_num);
                        load('table_'+emp.employee_num);
                    }
                })
                .catch(function (error) {
                    SGui.showError('Error al obtener los registros');
                });
            }
        },

        getHistoryVac(table_id, user_id){
            SGui.showWaiting(5000);
            axios.post( this.oData.getVacationHistoryRoute, {
                'user_id': user_id
            })
            .then( result => {
                var data = result.data;
                if(data.success){
                    this.oReDrawTables.reDrawVacationsTable(table_id, data);
                    swal.close();
                }else{
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch(function(error){
                console.log(error);
                SGui.showError();
            })
        },

        hiddeHistory(table_id, user_id){
            SGui.showWaiting(10000);
            axios.post(this.oData.hiddeHistoryRoute, {
                'user_id':  user_id
            })
            .then(response => {
                let data = response.data;
                if(data.success){
                    this.oReDrawTables.reDrawVacationsTable(table_id, data);
                    swal.close();
                }else{
                    swal.close();
                    SGui.showMessage('', data.message, data.icon);
                }
            })
            .catch( function (error){
                console.log(error);
                swal.close();   
            });
        }
    },
})
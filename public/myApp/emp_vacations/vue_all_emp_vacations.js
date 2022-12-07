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
                        var head_accord ='<div class="card shadow mb-4">'+
                            '<a '+
                                'href="#id_'+emp.employee_num+'" class="d-block card-header py-3" data-toggle="collapse" '+
                                'role="button" aria-expanded="false" aria-controls="'+emp.employee_num+'" '+
                                'onclick="getEmployees('+(i + lEmployees_size)+',' +emp.id+','+ emp.org_chart_job_id+','+ emp.is_head_user+');"'+
                            '> '+
                            '<h6 class="m-0 font-weight-bold text-primary">'
                            +'<div class="row">'
                                +'<div class="col-md-3">'
                                    +emp.employee+' '
                                +'</div>'
                                +'<div class="col-md-9">'
                                    +'<div class="row">'
                                        +'<span class="col-md-4">'
                                            +'<span style="width: 0; border-right: 1px solid #bcbdc2; height: calc(4.375rem - 2rem); margin: auto 1rem"></span>'
                                            +'Vacaciónes pendientes: '+emp.tot_vacation_remaining+' días '
                                        +'</span>';

                                        if(emp.is_head_user){
                                            head_accord = head_accord +
                                                '<span class="col-md-8">'+
                                                    '<span style="width: 0; border-right: 1px solid #bcbdc2; height: calc(4.375rem - 2rem); margin: auto 1rem"></span>'+
                                                    'Encargado de area'+
                                                    '<span class="bx bxs-group"></span>'+
                                                '</span>';
                                        }

                            head_accord = head_accord
                                    +'</div>'
                                +'</div>'
                            +'</div>'
                            +'</h6> '
                            +'</a> ';
    
                        var body_accord = '<div class="collapse" id="id_'+emp.employee_num+'">'+
                            '<div class="card-body">'+
                                '<div class="col-md-6 card border-left-primary">'+
                                    '<table style="margin-left: 10px;">'+
                                        '<thead>'+
                                            '<th></th>'+
                                            '<th></th>'+
                                        '</thead>'+
                                        '<tbody>'+
                                            '<tr>'+
                                                '<th>Nombre:</th>'+
                                                '<td>'+emp.full_name+'</td>'+
                                            '</tr>'+
                                            '<tr>'+
                                                '<th>Fecha ingreso:</th>'+
                                                '<td>'+emp.last_admission_date+'</td>'+
                                            '</tr>'+
                                            '<tr>'+
                                                '<th>Antigüedad:</th>'+
                                                '<td>'+emp.antiquity+' al día de hoy</td>'+
                                            '</tr>'+
                                            '<tr>'+
                                                '<th>Departamento:</th>'+
                                                '<td>'+emp.department_name_ui+'</td>'+
                                            '</tr>'+
                                            '<tr>'+
                                                '<th>Puesto:</th>'+
                                                '<td>'+emp.job_name_ui+'</td>'+
                                            '</tr>'+
                                            '<tr>'+
                                                '<th>Plan de vacaciones:</th>'+
                                                '<td>'+emp.vacation_plan_name+'</td>'+
                                            '</tr>'+
                                        '</tbody>'+
                                    '</table>'+
                                '</div>'+
                                '<br>'+
                                '<table class="table table-bordered">'+
                                    '<thead class="thead-light">'+
                                        '<th>Periodo</th>'+
                                        '<th>Aniversario</th>'+
                                        '<th>Vac. ganadas</th>'+
                                        '<th>Vac. gozadas</th>'+
                                        '<th>Vac. vencidas</th>'+
                                        '<th>Vac. solicitadas</th>'+
                                        '<th>Vac. pendientes</th>'+
                                    '</thead>'+
                                    '<tbody>';
                                    for(var vac of emp.vacation){
                                        body_accord = body_accord+
                                            '<tr>'+
                                            '<td>'+vac.date_start+' a '+vac.date_end+'</td>'+
                                            '<td>'+vac.id_anniversary+'</td>'+
                                            '<td>'+vac.vacation_days+'</td>'+
                                            '<td>'+vac.num_vac_taken+'</td>'+
                                            '<td>'+vac.expired+'</td>'+
                                            '<td>'+vac.request+'</td>';
    
                                            if(vac.remaining < 0){
                                                body_accord = body_accord +
                                                    '<td style="color: red">'+vac.remaining+'</td>';
                                            }else{
                                                body_accord = body_accord +
                                                '<td>'+vac.remaining+'</td>';
                                            }
                                            body_accord = body_accord+
                                        '</tr>';
                                    }
                                    body_accord = body_accord+
                                        '<tr class="thead-light">'+
                                            '<td></td>'+
                                            '<th>Total</th>'+
                                            '<td>'+emp.tot_vacation_days+'</td>'+
                                            '<td>'+emp.tot_vacation_taken+'</td>'+
                                            '<td>'+emp.tot_vacation_expired+'</td>'+
                                            '<td>0</td>';
                                            if(emp.tot_vacation_remaining < 0){
                                                body_accord = body_accord +
                                                '<td style="color: red;">'+emp.tot_vacation_remaining+'</td>';
                                            }else{
                                                body_accord = body_accord +
                                                '<td>'+emp.tot_vacation_remaining+'</td>';
                                            }
                                            body_accord = body_accord+
                                        '</tr>'+
                                    '</tbody>'+
                                '</table>'+
                                '<br>'+
                                '<span id="id_span_'+emp.id+'"></span></span>'+
                            '</div>'+
                        '</div>'+
                    '</div>';
    
                        var html = head_accord + body_accord;
    
                        var id = 'id_span_'+emp_id;
    
                        var temp = document.createElement('div');
                        temp.innerHTML = html;
    
                        document.getElementById(id).appendChild(temp);
                    }
                })
                .catch(function (error) {
                    SGui.showError('Error al obtener los registros');
                });
            }
        }
    },
})
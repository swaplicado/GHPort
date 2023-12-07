<div class="modal fade" id="modal_assign" tabindex="-1" role="dialog" aria-labelledby="assign"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assign">Asignar plan de vacaciones: @{{vacation_plan_name}}</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5 pre-scrollable">
                        <table class="table table-bordered" id="table_users" style="width: 100%;">
                            <thead>
                                <th>id</th>
                                <th>Colaboradores</th>
                            </thead>
                            <tbody>
                                
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-2" style="text-align: center">
                        <table class="table">
                            <thead>
                                <th style="border: none">&nbsp;</th>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="border: none">
                                        <button class="btn btn-secondary" v-on:click="passTolUsersAssign();" title="Pasar uno a la derecha">
                                            <span class='bx bxs-chevron-right'></span>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border: none">
                                        <button class="btn btn-secondary" v-on:click="passTolUsers();" title="Pasar uno a la izquierda">
                                            <span class='bx bxs-chevron-left'></span>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border: none">
                                        <button class="btn" style="background-color: #81D4FA;" v-on:click="passAllTolUsersAssign();" title="Pasar todos a la derecha">
                                            <span class='bx bxs-chevrons-right'></span>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border: none">
                                        <button class="btn" style="background-color: #81D4FA;" v-on:click="passAllTolUsers();" title="Pasar todos a la derecha">
                                            <span class='bx bxs-chevrons-left'></span>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-5 pre-scrollable">
                        <table class="table table-bordered" id="table_users_assigned" style="width: 100%;">
                            <thead>
                                <th>id</th>
                                <th>Colab. Asign.</th>
                            </thead>
                            <tbody>
                                
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cerrar</button>
                <button class="btn btn-primary" type="button" v-on:click="saveAssignVacationPlan();">Guardar</button>
            </div>
        </div>
    </div>
</div>
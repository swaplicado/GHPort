<div class="modal fade" id="modal_events_assign" tabindex="-1" role="dialog" aria-labelledby="modal_events_assign_label"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-small">
                <h5 class="modal-title" id="modal_events_assign_label">Asignación de evento</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body modal-body-small">
                <div class="container">
                    <a class="btn btn-outline-secondary" id="bEmployee" href="#" v-on:click="changeAssign('employee', 'bEmployee');">Asignar por empleado</a>
                    <a class="btn btn-outline-secondary" id="bGroup" href="#" v-on:click="changeAssign('group', 'bGroup');">Asignar por grupo</a>
                    <br>
                    <br>
                    <div class="row" v-show="assignBy == 'employee'" style="border: 1px solid #e3e6f0;">
                        <div class="col-md-5 pre-scrollable">
                            <table class="table table-bordered" id="employeesNoAssignTable" style="width: 100%;">
                                <thead class="thead-light">
                                    <th>id</th>
                                    <th>Colaboradores sin asignar</th>
                                </thead>
                            </table>
                        </div>
                        <div class="col-md-2">
                            <br>
                            <div class="row centerItem">
                                <button class="btn btn-secondary" v-on:click="passToEmpAssign(false);" title="Pasar a la derecha">
                                    <span class='bx bxs-chevron-right'></span>
                                </button>
                            </div>
                            <br>
                            <div class="row centerItem">
                                <button class="btn btn-secondary" v-on:click="passToEmpNoAssign(false);" title="Pasar a la izquierda">
                                    <span class='bx bxs-chevron-left'></span>
                                </button>
                            </div>
                            <br>
                            <div class="row centerItem">
                                <button class="btn" style="background-color: #81D4FA;" v-on:click="passToEmpAssign(true);" title="Pasar todos a la derecha">
                                    <span class='bx bxs-chevrons-right'></span>
                                </button>
                            </div>
                            <br>
                            <div class="row centerItem">
                                <button class="btn" style="background-color: #81D4FA;" v-on:click="passToEmpNoAssign(true);" title="Pasar todos a la izquierda">
                                    <span class='bx bxs-chevrons-left'></span>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-5 pre-scrollable">
                            <table class="table table-bordered" id="employeesAssignTable" style="width: 100%;">
                                <thead class="thead-light">
                                    <th>id</th>
                                    <th>Colaboradores asignados</th>
                                </thead>
                            </table>
                        </div>
                    </div>                    
                    <div class="row" v-show="assignBy == 'group'">
                        <div class="col-md-5 pre-scrollable">
                            <table class="table table-bordered" id="groupsNoAssignTable" style="width: 100%;">
                                <thead class="thead-light">
                                    <th>id</th>
                                    <th>Grupos sin asignar</th>
                                </thead>
                            </table>
                        </div>
                        <div class="col-md-2">
                            <br>
                            <div class="row centerItem">
                                <button class="btn btn-secondary" v-on:click="passToGroupAssign(false);" title="Pasar a la derecha">
                                    <span class='bx bxs-chevron-right'></span>
                                </button>
                            </div>
                            <br>
                            <div class="row centerItem">
                                <button class="btn btn-secondary" v-on:click="passToGroupNoAssign(false);" title="Pasar a la izquierda">
                                    <span class='bx bxs-chevron-left'></span>
                                </button>
                            </div>
                            <br>
                            <div class="row centerItem">
                                <button class="btn" style="background-color: #81D4FA;" v-on:click="passToGroupAssign(true);" title="Pasar todos a la derecha">
                                    <span class='bx bxs-chevrons-right'></span>
                                </button>
                            </div>
                            <br>
                            <div class="row centerItem">
                                <button class="btn" style="background-color: #81D4FA;" v-on:click="passToGroupNoAssign(true);" title="Pasar todos a la izquierda">
                                    <span class='bx bxs-chevrons-left'></span>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-5 pre-scrollable">
                            <table class="table table-bordered" id="groupsAssignTable" style="width: 100%;">
                                <thead class="thead-light">
                                    <th>id</th>
                                    <th>Grupos asignados</th>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer modal-footer-small">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" v-on:click="setAssignEmployee()" v-if="assignBy == 'employee'">Guardar</a>
                <button type="button" class="btn btn-primary" v-on:click="setAssignGroup()" v-if="assignBy == 'group'">Guardar</a>
            </div>
        </div>
    </div>
</div>
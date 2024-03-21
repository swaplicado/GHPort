<div class="modal fade" id="modal_groups_assign" tabindex="-1" role="dialog" aria-labelledby="modal_groups_assign_label"
    aria-hidden="true">
    <div class="modal-dialog" role="document" style="max-width: 1140px;">
        <div class="modal-content">
            <div class="modal-header modal-header-small">
                <h5 class="modal-title" id="modal_groups_assign_label">Asignación de grupo</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body modal-body-small">
                <div class="container">
                    <div class="row" style="border: 1px solid #e3e6f0;">
                        <div class="col-md-5 pre-scrollable">
                            <table class="table table-bordered" id="employeesNoAssignTable" style="width: 100%;">
                                <thead class="thead-light">
                                    <th>id</th>
                                    <th>Colaboradores sin asignar</th>
                                    <th>Área</th>
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
                                    <th>Área</th>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer modal-footer-small">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" v-on:click="setAssign()">Guardar</a>
            </div>
            <div class="modal-footer modal-footer-small">
                <div class="container">
                    <div>
                        <label class="form-label" for="listColabs"><h5>Colaboradores asignados:</h5></label>
                        <ol class="ulColumns3 ol_bold" name="listColabs">
                            <template v-for="(colab, index) in lEmployeesAssigned">
                                <li class="li_bold">
                                    @{{colab.employee}}
                                </li>
                            </template>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
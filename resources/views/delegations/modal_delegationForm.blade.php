<div class="modal fade" id="modal_delegations" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Nueva delegación para colaborador</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="startDate">Fecha inicio:</label>
                                <input class="form-control" type="date" name="" id="startDate" v-model="start_date">
                            </div>
                            <div class="col-md-4">
                                <label for="endDate">(Opcional) Fecha fin:</label>
                                <input class="form-control" type="date" name="" id="endDate" v-model="end_date">
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="user_delegated">Usuario ausente:</label>
                                <select class="select2-class" name="" id="user_delegated" style="width: 100%"></select>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="delegation">Usuario encargado:</label>
                                <select class="select2-class" name="" id="user_delegation_n" style="width: 100%"></select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" v-on:click="saveDelegation();">Guardar</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_my_delegation" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Nueva delegación</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="startDate">Fecha inicio:</label>
                                <input class="form-control" type="date" name="" id="startDate" v-model="start_date">
                            </div>
                            <div class="col-md-4">
                                <label for="endDate">(Opcional) Fecha fin:</label>
                                <input class="form-control" type="date" name="" id="endDate" v-model="end_date">
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="delegation">Usuario encargado:</label>
                                <select class="select2-class-myDelegation" name="" id="user_delegation_d" style="width: 100%"></select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" v-on:click="saveDelegation();">Guardar</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_edit_delegation" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Editar delegación</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="startDate">Fecha inicio:</label>
                                <input class="form-control" type="date" name="" id="startDate" v-model="start_date">
                            </div>
                            <div class="col-md-4">
                                <label for="endDate">(Opcional) Fecha fin:</label>
                                <input class="form-control" type="date" name="" id="endDate" v-model="end_date">
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" id="exampleCheck1" v-model="closeDelegation">
                                    <label class="form-check-label" for="exampleCheck1" style="color: red">Cerrar delegaciòn</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" v-on:click="updateDelegation();">Guardar</a>
            </div>
        </div>
    </div>
</div>
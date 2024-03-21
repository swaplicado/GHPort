<div class="modal fade" id="modal_groups" tabindex="-1" role="dialog" aria-labelledby="modal_groups_label"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-small">
                <h5 class="modal-title" id="modal_groups_label">@{{isEdit == true ? 'Editar grupo' : 'Nuevo grupo'}}</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body modal-body-small">
                <div class="row">
                    <div class="col-md-1">
                        <label for="groupName">Grupo:*</label>
                    </div>
                    <div class="col-md-11">
                        <input type="text" class="form-control" name="groupName" id="groupName" v-model="groupName">
                    </div>
                </div>
            </div>
            <div class="modal-footer modal-footer-small">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" v-on:click="saveGroup()">Guardar</a>
            </div>
        </div>
    </div>
</div>
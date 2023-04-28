<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Editar configuraciones</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <label for="selArea">Tipo de incidencia:</label>
                <select class="select2-class-create-tipo-e" id="selTpIncE" name="selTpIncE" style="width: 90%;"></select>
                <label for="selArea">Sistema con el que interactua:</label>
                <select class="select2-class-create-sistema-e" id="selIntSysE" name="selIntSysE" style="width: 90%;"></select>
                <label for="selArea">Id de tipo incidencia externa:</label>
                <input type="number" id="tpExtE" name="tpExtE" style="width: 90%;" v-model="tpExt">
                <label for="selArea">Id de clase incidencia externa:</label>
                <input type="number" id="clExtE" name="clExtE" style="width: 90%;" v-model="clExt">
                
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" v-on:click="save();">Guardar</a>
            </div>
        </div>
    </div>
</div>
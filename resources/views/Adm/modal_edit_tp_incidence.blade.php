<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Crear tipo de incidencia</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <label for="selArea">Tipo de incidencia:*</label>
                <input type="text" id="tpIncidenceE" name="tpIncidenceE" style="width: 90%;" v-model="nameTp">
                <label for="selArea">Clase de incidencia:*</label>
                <select class="select2-class-create-clase" id="selClIncE" name="selClIncE" style="width: 90%;"></select>
                <label for="selArea">Sistema con el que interactua:*</label>
                <select class="select2-class-create-sistema" id="selIntSysE" name="selIntSysE" style="width: 90%;"></select>
                <br><br>

                <input type="checkbox" id="activeE" name="activeE" value="active" v-model="active">
                <label for="selArea">Esta activo</label>
                
                <br>
                
                <input type="checkbox" id="need_authE" name="need_authE" value="need_auth" v-model="auth">
                <label for="selArea">Necesita autorización</label>
                
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" v-on:click="save();">Guardar</a>
            </div>
        </div>
    </div>
</div>
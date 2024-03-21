<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
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
                <input type="text" id="tpIncidenceC" name="tpIncidenceC" style="width: 90%;" v-model="nameTp">
                <label for="selArea">Clase de incidencia:*</label>
                <select class="select2-class-create-clase-c" id="selClIncC" name="selClIncC" style="width: 90%;"></select>
                <label for="selArea">Sistema con el que interactua:*</label>
                <select class="select2-class-create-sistema-c" id="selIntSysC" name="selIntSysC" style="width: 90%;"></select>
                <br><br>

                <input type="checkbox" id="activeC" name="activeC" value="active" v-model="active">
                <label for="selArea">Esta activo</label>
                
                <br>
                
                <input type="checkbox" id="need_authC" name="need_authC" value="need_auth" v-model="auth">
                <label for="selArea">Necesita autorización</label>
                
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" v-on:click="save();">Guardar</a>
            </div>
        </div>
    </div>
</div>
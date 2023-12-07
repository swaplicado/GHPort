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
                <label for="selArea">Colaborador:</label>
                <input type="text"  style="width: 90%;" v-model="fullname" disabled>
                <label for="selArea">Usuario:</label>
                <input type="text" id="username" name="username" style="width: 90%;" v-model="username">
                <label for="selArea">Correo:</label>
                <input type="text" id="mail" name="mail" style="width: 90%;" v-model="mail">
                <label for="selArea">Área funcional: <span title="Puesto en el organigrama" class="bx bx-info-circle"></span></label>
                <select class="select2-class" id="selArea" name="selArea" v-model="selArea" style="width: 90%;"></select>
                <label for="selArea">Plan vacaciones: <span title="Días de vacaciones correspondientes" class="bx bx-info-circle"></span></label>
                <select class="select2-class" id="selVac" name="selVac" v-model="selVac" style="width: 90%;"></select>             
                <label for="selSchedule">Horario: <span title="Horario" class="bx bx-info-circle"></span></label>
                <select class="select2-class" id="selSchedule" name="selSchedule" v-model="selSchedule" style="width: 90%;"></select>             
                <br><br>

                <input type="checkbox" id="active" name="active" value="active" v-model="active">
                <label for="selArea">Esta activo</label>
                
                <br>
                
                <input type="checkbox" id="passRes" name="passRes" value="passRes" v-model="passRess">
                <label for="selArea">Restablecer contraseña por defecto</label>
                
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" v-on:click="save();">Guardar</a>
            </div>
        </div>
    </div>
</div>
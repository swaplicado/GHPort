<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="max-width: 1140px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Editar usuario</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-2">
                        <label for="selArea">Colaborador:</label>
                    </div>
                    <div class="col-md-10">
                        <input type="text"  style="width: 90%;" v-model="fullname" disabled>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <label for="selArea" style="margin-top: 10px; margin-bottom: 0px;">Usuario:*</label>
                    </div>
                    <div class="col-md-10">
                        <input type="text" id="username" name="username" style="width: 90%;" v-model="username">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <label for="selArea" style="margin-top: 10px; margin-bottom: 0px;">Correo:*</label>
                    </div>
                    <div class="col-md-10">
                        <input type="text" id="mail" name="mail" style="width: 90%;" v-model="mail">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <label for="selArea" style="margin-top: 10px; margin-bottom: 0px;">Área funcional:* <span title="Puesto en el organigrama" class="bx bx-info-circle"></span></label>
                    </div>
                    <div class="col-md-10">
                        <select class="select2-class" id="selArea" name="selArea" v-model="selArea" style="width: 90%;"></select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <label for="selArea" style="margin-top: 10px; margin-bottom: 0px;">Plan vacaciones:* <span title="Días de vacaciones correspondientes" class="bx bx-info-circle"></span></label>
                    </div>
                    <div class="col-md-10">
                        <select class="select2-class" id="selVac" name="selVac" v-model="selVac" style="width: 90%;"></select>             
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <label for="selSchedule" style="margin-top: 10px; margin-bottom: 0px;">Horario: <span title="Horario" class="bx bx-info-circle"></span></label>
                    </div>
                    <div class="col-md-10">
                        <select class="select2-class" id="selSchedule" name="selSchedule" v-model="selSchedule" style="width: 90%;"></select>             
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <label for="selSchedule" style="margin-top: 10px; margin-bottom: 0px;">Rol: <span title="Rol" class="bx bx-info-circle"></span></label>
                    </div>
                    <div class="col-md-10">
                        <select class="select2-class" id="selRol" name="selRol" v-model="selRol" style="width: 90%;"></select>             
                    </div>
                </div>
                <br><br>

                <input type="checkbox" id="active" name="active" value="active" v-model="active">
                <label for="selArea">Esta activo</label>
                
                <br>
                
                <input type="checkbox" id="passRes" name="passRes" value="passRes" v-model="passRess">
                <label for="selArea">Restablecer contraseña por defecto</label>
                
                <br>

                <input type="checkbox" id="can_change_dp" name="can_change_dp" v-model="can_change_dp">
                <label for="">Edición de datos personales habilitado</label>
                
                <br>

                <input type="checkbox" id="can_change_cv" name="can_change_cv" v-model="can_change_cv">
                <label for="">Edición de curriculum vitae habilitado</label>
                
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" v-on:click="save();">Guardar</a>
            </div>
        </div>
    </div>
</div>
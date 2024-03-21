<div class="modal fade" id="modal_season_type" tabindex="-1" role="dialog" aria-labelledby="season_type"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="season_type">Temporada especial</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row justify-content-center">
                    <div class="col-md-2 col-xs-12">
                        <label for="name" class="form-label">Nombre:*</label>
                    </div>
                    <div class="col-md-8 col-xs-12">
                        <input name="name" id="name" v-model="name" class="form-control">
                    </div>
                </div>
                <br>
                <div class="row justify-content-center">
                    <div class="col-md-2 col-xs-12">
                        <label for="key_code" class="form-label">Clave:*</label>
                    </div>
                    <div class="col-md-8 col-xs-12">
                        <input name="key_code" id="key_code" v-model="key_code" class="form-control">
                    </div>
                </div>
                <br>
                <div class="row justify-content-center">
                    <div class="col-md-2 col-xs-12">
                        <label for="priority" class="form-label">prioridad:*</label>
                    </div>
                    <div class="col-md-8 class-xs-12">
                        <select class="form-control" name="priority" id="priority" v-bind:style="{ 'color': text_color, 'background-color': color }" v-on:change="changeColor();" v-model="priority" style="width: 20%">
                            <option value="1" style="background-color: #006600">1</option>
                            <option value="2" style="background-color: #009900">2</option>
                            <option value="3" style="background-color: #00CC00">3</option>
                            <option value="4" style="background-color: #00FF00">4</option>
                            <option value="5" style="background-color: #66FF66">5</option>
                        </select>
                    </div>
                </div>
                <br>
                <div class="row justify-content-center">
                    <div class="col-md-2 col-xs-12">
                        <label for="description">Descripción:</label>
                    </div>
                    <div class="col-md-8 col-xs-12">
                        <textarea class="form-control" name="description" id="description" v-model="description"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" type="button" v-on:click="saveSeasonType();">Guardar</button>
            </div>
        </div>
    </div>
</div>
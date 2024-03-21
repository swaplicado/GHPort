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
                        <label for="priority" class="form-label">prioridad:*</label>
                    </div>
                    <div class="col-md-8 class-xs-12">
                        <input type="number" readonly v-model="priority" v-bind:class="['form-control', priorityclass]">
                    </div>
                </div>
                <br>
                <div class="row justify-content-center">
                    <div class="col-md-2 col-xs-12">
                        <label for="priority" class="form-label">Color:*</label>
                    </div>
                    <div class="col-md-8 class-xs-12">
                        <input type="color" class="form-control" id="color-picker" v-model="hexColor">
                    </div>
                </div>
                <br>
                <div class="row justify-content-center">
                    <div class="col-md-2 col-xs-12">
                        <label for="description">Descripción:*</label>
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
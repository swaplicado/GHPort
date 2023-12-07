<div class="modal fade" id="modal_recover" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Reactivación de vacaciones</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="table_modal_expiredVac" style="width: 100%">
                        <thead class="thead-light">
                            <th>vacations_id</th>
                            <th>Periodo</th>
                            <th>Vacaciones del periodo</th>
                            <th>Vacaciones consumidas</th>
                            <th>Vacaciones vencidas</th>
                            <th>Vacaciones recuperadas</th>
                        </thead>
                        <tbody>
                            
                        </tbody>
                    </table>
                </div>
                <div class="separator"></div>
                <div v-if="vacationsExpired.length > 0">
                    <label for="">Días a reactivar:</label>
                    <input type="number" min="0" :max="maxValue" v-model="daysToRecover">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" v-on:click="saveRecovered()">Guardar</a>
            </div>
        </div>
    </div>
</div>
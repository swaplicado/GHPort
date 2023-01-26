<div class="modal fade" id="modal_Mysolicitud" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Solicitud de vacaciones</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Días correspondientes al aniversario actual</th>
                            <th>Días proporcionales del próximo aniversario</th>
                            <th>Días al cumplir el próximo aniversario</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>@{{actual_vac_days}}</td>
                            <td>@{{prop_vac_days}}</td>
                            <td>@{{prox_vac_days}}</td>
                        </tr>
                    </tbody>
                </table>
                <div class="card">
                    <div class="card-body">
                        @include('layouts.Nomeclatura_calendario', ['id' => 'nomeclaturaMyRequest'])
                        <div style="text-align: left;">
                            {{-- <div class="form-check">
                                <input class="form-check-input" type="checkbox" v-model="take_rest_days" v-on:change="getDataDays();" id="restDays">
                                <label class="form-check-label" for="restDays">
                                    Tomar dias de descanso.
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" v-model="take_holidays" v-on:change="getDataDays();" id="holidays">
                                <label class="form-check-label" for="holidays">
                                    Tomar dias no laborables.
                                </label>
                            </div> --}}
                        </div>
                        <div style="text-align: center">
                            <span id="two-inputs-myRequest">
                                <span hidden>
                                    <input id="date-range200-myRequest" type="date" value="" class="form-control" style="width: 30%; display: inline" readonly> a <input id="date-range201-myRequest" type="date" value="" class="form-control" style="width: 30%; display: inline" readonly>
                                </span>
                                <input class="form-control" v-model="startDate" style="width: 30%; display: inline" readonly> a <input class="form-control" v-model="endDate" style="width: 30%; display: inline" readonly>
                                <br>
                                <br>
                            </span>
                            <br>
                            <br>
                            <div>
                                <button type="button" class="btn btn-primary" id="clear" :disabled="!valid">Limpiar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <div class="card">
                    <div class="card-body">
                        <div>
                            <label class="form-label" for="comments">Comentarios:</label>
                            <textarea class="form-control" name="comments" id="comments" style="width: 99%;" v-model="comments"></textarea>
                        </div>
                        <br>
                        <div>
                            <label class="form-label" for="efectiveDays" style="display: inline;">Días efectivos:</label>
                            <input class="form-control" name="efectiveDays" type="number" v-model="takedDays" readonly style="width: 10%; display: inline;">
                        </div>
                        <br>
                        <div>
                            <label class="form-label" for="calendarDays" style="display: inline;">Días calendario:</label>
                            <input class="form-control" name="calendarDays" type="number" v-model="totCalendarDays" readonly style="width: 10%; display: inline;">
                        </div>
                        <br>
                        <div>
                            <label class="form-label" for="listDays">Días de vacaciones:</label>
                            <ul class="ulColumns3" name="listDays">
                                <li v-for="day in lDays">@{{day}}</li>
                            </ul>
                        </div>
                        <div>
                            <label class="form-label" for="start_date" style="display: inline;">Fecha inicio:</label>
                            <input class="form-control" v-model="startDate" readonly style="width: 20%; display: inline;">
                            &nbsp;
                            <label class="form-label" for="end_date" style="display: inline;">Fecha fin:</label>
                            <input class="form-control" v-model="endDate" readonly style="width: 20%; display: inline;">
                            &nbsp;
                            <label class="form-label" for="return_date" style="display: inline;">Fecha regreso:</label>
                            <input class="form-control" v-model="returnDate" readonly style="width: 20%; display: inline;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" v-on:click="requestVac()" :disabled="!valid">Guardar</a>
            </div>
        </div>
    </div>
</div>
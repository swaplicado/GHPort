<div class="modal fade" id="modal_specialVac" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
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
                        <table>
                            <thead>

                            </thead>
                            <tbody>
                                <tr>
                                    <td><b>Inicio:</b></td>
                                    <td><input class="form-control" v-model="startDate" readonly></td>
                                </tr>
                                <tr>
                                    <td><b>Fin:</b></td>
                                    <td><input class="form-control" v-model="endDate" readonly></td>
                                </tr>
                                <tr>
                                    <td><b>Regreso:</b></td>
                                    <td><input class="form-control" v-model="returnDate" readonly></td>
                                </tr>
                                <tr>
                                    <td><b>Días efectivos:</b></td>
                                    <td><input class="form-control" name="efectiveDays" type="number" v-model="takedDays" readonly></td>
                                </tr>
                                <tr>
                                    <td><b>Días calendario:</b></td>
                                    <td><input class="form-control" name="calendarDays" type="number" v-model="totCalendarDays" readonly></td>
                                </tr>
                            </tbody>
                        </table>
                        <br>
                        <div>
                            <label class="form-label" for="listDays"><b>Desglose de los días de vacaciones:</b></label>
                            <ol class="ulColumns3" name="listDays">
                                <template v-for="(day, index) in lDays">
                                    <li v-bind:style="{'color': day.taked ? 'green' : 'red'}">
                                        <label class="" :for="'exampleCheck'+index">@{{day.date}}</label>
                                        <input v-if="!day.bussinesDay" type="checkbox" class="" :id="'exampleCheck'+index" v-on:click="setTakedDay(index, 'exampleCheck'+index);" :checked="day.taked">
                                    </li>
                                </template>
                            </ol>
                        </div>
                        <div>
                            <label class="form-label" for="comments"><b>Comentarios:</b></label>
                            <textarea class="form-control" name="comments" id="comments" style="width: 99%;" v-model="comments"></textarea>
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
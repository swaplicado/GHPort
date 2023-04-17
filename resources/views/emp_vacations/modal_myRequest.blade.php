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
                                <table style="width: 100%;">
                                    <thead></thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                @include('layouts.Nomeclatura_calendario', ['id' => 'nomeclaturaMyRequest'])
                                            </td>
                                            <td>
                                                <input class="form-control" v-model="startDate" style="width: 40%; display: inline" readonly> a <input class="form-control" v-model="endDate" style="width: 40%; display: inline" readonly>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-primary inline" id="clear" :disabled="!valid">Limpiar</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <br>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div>
                            <table>
                                <thead></thead>
                                <tbody>
                                    <tr>
                                        <td style="vertical-align: top;"><b>Fecha inicio:</b></td>
                                        <td style="vertical-align: top;"><input class="form-control" v-model="startDate" readonly></td>
                                        <td style="vertical-align: top;"><p>&nbsp &nbsp</p></td>
                                        <td style="vertical-align: top;"><b>Dias calendario:</b></td>
                                        <td style="vertical-align: top;"><input class="form-control" name="calendarDays" type="number" v-model="totCalendarDays" readonly></td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align: top;"><b>Fecha fin:</b></td>
                                        <td style="vertical-align: top;"><input class="form-control" v-model="endDate" readonly></td>
                                        <td style="vertical-align: top;"><p>&nbsp &nbsp</p></td>
                                        <td style="vertical-align: top;"><b>Dias efectivos:</b></td>
                                        <td style="vertical-align: top;"><input class="form-control" name="efectiveDays" type="number" v-model="takedDays" readonly></td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align: top;"><b>Fecha regreso:</b></td>
                                        <td style="vertical-align: top;">
                                            <table :hidden="showDatePickerSimple">
                                                <thead></thead>
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <input class="form-control" v-model="returnDate" readonly>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-secondary" v-on:click="editMyReturnDate()"><span class="bx bx-pencil"></span></button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <input type="text" ref="datepicker" name="datepicker" :hidden="!showDatePickerSimple">
                                        </td>
                                        <td style="vertical-align: top;"></td>
                                        <td style="vertical-align: top;">
                                            <b>Tipo solicitud:</b>
                                        </td>
                                        <td style="vertical-align: top;">
                                            <div style="overflow-y: auto;">
                                                <ul style="margin: 0; padding: 0; list-style-position: inside;">
                                                    <li v-for="type in lTypes">@{{type}}</li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
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
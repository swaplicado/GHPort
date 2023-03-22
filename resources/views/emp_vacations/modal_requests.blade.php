<div class="modal fade" id="modal_solicitud" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
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
                <div class="card">
                    <div class="card-body">
                        <div v-if="oUser != null" style="border-bottom: solid 1px rgba(0,0,0,.125);">
                            <table class="table">
                                <thead>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><b>Colaborador:</b>&nbsp</td>
                                        <td>@{{oUser.full_name}}</td>
                                        <td rowspan="3" style="text-align: center; vertical-align:middle;">
                                            <img class="rounded-circle" :src="'data:image/jpg;base64,'+oUser.photo64" style="width:100px;height:100px;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>Fecha de ingreso:</b>&nbsp</td>
                                        <td>@{{ oDateUtils.formatDate(oUser.benefits_date, 'ddd DD-MMM-YYYY')}}</td>
                                    </tr>
                                    <tr>
                                        <td><b>Antigüedad:</b>&nbsp</td>
                                        <td>@{{oUser.antiquity}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <br>
                        @include('layouts.Nomeclatura_calendario', ['id' => 'nomeclaturaRequest'])
                        <div style="text-align: left;">
                            {{-- <div class="form-check">
                                <input class="form-check-input" type="checkbox" disabled v-model="take_rest_days" v-on:change="getDataDays();" id="restDays">
                                <label class="form-check-label" for="restDays">
                                    Tomar dias de descanso.
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" disabled v-model="take_holidays" v-on:change="getDataDays();" id="holidays">
                                <label class="form-check-label" for="holidays">
                                    Tomar dias no laborables.
                                </label>
                            </div> --}}
                        </div>
                        <div style="text-align: center">
                            <span id="two-inputs">
                                <span hidden>
                                    <input id="date-range200" type="date" value="" class="form-control" style="width: 30%; display: inline" readonly> a <input id="date-range201" type="date" value="" class="form-control" style="width: 30%; display: inline" readonly>
                                </span>
                                <input class="form-control" v-model="startDate" style="width: 30%; display: inline" readonly> a <input class="form-control" v-model="endDate" style="width: 30%; display: inline" readonly>
                                <br>
                                <br>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div>
                            <table>
                                <thead>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><b>Fecha inicio:</b></td>
                                        <td><input class="form-control" v-model="startDate" readonly></td>
                                        <td><p>&nbsp &nbsp</p></td>
                                        <td><b>Días calendario:</b></td>
                                        <td><input class="form-control" name="totCalendarDays" type="number" v-model="totCalendarDays" readonly></td>
                                    </tr>
                                    <tr>
                                        <td><b>Fecha fin:</b></td>
                                        <td><input class="form-control" v-model="endDate" readonly></td>
                                        <td><p>&nbsp &nbsp</p></td>
                                        <td><b>Días efectivos:</b></td>
                                        <td><input class="form-control" name="takedDays" type="number" v-model="takedDays" readonly></td>
                                    </tr>
                                    <tr>
                                        <td><b>Fecha regreso:</b></td>
                                        <td><input class="form-control" v-model="returnDate" readonly></td>
                                        <td><p>&nbsp &nbsp</p></td>
                                        <td></td>
                                        <td></td>
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
                                    </li>
                                </template>
                            </ol>
                        </div>
                        <div>
                            <label for="comentarios_emp"><b>Comentarios del colaborador:</b></label>
                            <p v-if="emp_comments != null && emp_comments != ''">@{{emp_comments}}</p>
                            <p v-else>(Sin comentarios)</p>
                        </div>
                        <template v-if="isFromMail">
                            <div v-if="oApplication.request_status_id == 2">
                                <label class="form-label" for="comments"><b>Comentarios:</b></label>
                                <textarea class="form-control" name="comments" id="comments" style="width: 99%;" v-model="comments"></textarea>
                            </div>
                        </template>
                        <template v-else>
                            <label class="form-label" for="comments"><b>Comentarios:</b></label>
                            <textarea class="form-control" name="comments" id="comments" style="width: 99%;" v-model="comments"></textarea>
                        </template>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <template v-if="isFromMail">
                    <button v-if="oApplication.request_status_id == 2" type="button" class="btn btn-success" v-on:click="acceptRequest()"><span class="bx bxs-like"></span>&nbsp Aprobar</a>
                    <button v-if="oApplication.request_status_id == 2" type="button" class="btn btn-danger" v-on:click="rejectRequest"><span class="bx bxs-dislike"></span>&nbsp Rechazar</a>
                </template>
                <template v-else>
                    <button v-if="isApprove" type="button" class="btn btn-success" v-on:click="acceptRequest()">Aprobar</a>
                    <button v-else type="button" class="btn btn-danger" v-on:click="rejectRequest">Rechazar</a>
                </template>
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>
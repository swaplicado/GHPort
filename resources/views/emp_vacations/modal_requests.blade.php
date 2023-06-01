<div class="modal fade" id="modal_solicitud" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-small">
                <h5 class="modal-title" id="exampleModalLabel">Solicitud de vacaciones</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body modal-body-small">
                <div class="card">
                    <div class="card-body card-body-small">
                        <div v-if="oUser != null" style="border-bottom: solid 1px rgba(0,0,0,.125);">
                            <table class="table table-small">
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
                        <div class="myBreakLine"></div>
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
                                <table class="table-small" style="width: 100%;">
                                    <tbody>
                                        <thead></thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    @include('layouts.Nomeclatura_calendario', ['id' => 'nomeclaturaRequest'])
                                                </td>
                                                <td>
                                                    <input class="form-control" v-model="startDate" style="width: 30%; display: inline" readonly> a <input class="form-control" v-model="endDate" style="width: 30%; display: inline" readonly>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </tbody>
                                </table>
                                <div class="myBreakLine"></div>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body card-body-small">
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
                                        <td style="vertical-align: top;"><b>Fecha inicio:</b></td>
                                        <td style="vertical-align: top;"><input class="form-control" v-model="startDate" readonly></td>
                                        <td style="vertical-align: top;"><p>&nbsp &nbsp</p></td>
                                        <td style="vertical-align: top;"><b>Días calendario:</b></td>
                                        <td style="vertical-align: top;"><input class="form-control" name="totCalendarDays" type="number" v-model="totCalendarDays" readonly></td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align: top;"><b>Fecha fin:</b></td>
                                        <td style="vertical-align: top;"><input class="form-control" v-model="endDate" readonly></td>
                                        <td style="vertical-align: top;"><p>&nbsp &nbsp</p></td>
                                        <td style="vertical-align: top;"><b>Días efectivos:</b></td>
                                        <td style="vertical-align: top;"><input class="form-control" name="takedDays" type="number" v-model="takedDays" readonly></td>
                                    </tr>
                                    <tr>
                                        <td><b>Fecha regreso:</b></td>
                                        <td>
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
                                            <input type="text" name="reqDatepicker" :hidden="!showDatePickerSimple">
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
                        <div class="myBreakLine"></div>
                        <div>
                            <label class="form-label" for="listDays"><b>Desglose de los días de vacaciones:</b></label>
                            <ol class="ulColumns3" name="listDays">
                                <template v-for="(day, index) in lDays">
                                    <li v-if="day.taked"  v-bind:style="{'color': day.taked ? 'green' : 'red'}">
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
            <div class="modal-footer modal-footer-small">
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
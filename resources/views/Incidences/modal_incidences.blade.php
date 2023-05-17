<div class="modal fade" id="modal_incidences" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Incidencia</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <div v-show="!isEdit && !isRevision">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="">Selecciona clase:</label>
                                </div>
                                <div class="col-md-9">
                                    <select class="select2-class form-control" name="incident_class" id="incident_class" style="width: 90%;"></select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="">Selecciona Tipo:</label>
                                </div>
                                <div class="col-md-9">
                                    <select class="select2-class form-control" name="incident_type" id="incident_type" style="width: 90%;"></select>
                                </div>
                            </div>
                        </div>
                        <div v-if="isRevision">
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
                        </div>
                    </div>
                </div>
                <div style="text-align: center" v-show="showCalendar">
                    <div class="card">
                        <div class="card-body">
                            <span id="two-inputs-calendar">
                                <span hidden>
                                    <input id="date-range-001" type="date" value="" class="form-control" style="width: 30%; display: inline" readonly> a <input id="date-range-002" type="date" value="" class="form-control" style="width: 30%; display: inline" readonly>
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
                                            <td v-if="!isRevision">
                                                <button type="button" class="btn btn-primary inline" id="clear":hidden="!valid">Limpiar</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <br>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card" v-show="showCalendar">
                    <div class="card-body">
                        <table>
                            <thead></thead>
                            <tbody>
                                <tr v-if="isEdit || isRevision">
                                    <td><b>Clase de incidencia:</b></td>
                                    <td>@{{class_name}}</td>
                                    <td><p>&nbsp &nbsp</p></td>
                                    <td><b>Tipo de incidencia:</b></td>
                                    <td>@{{type_name}}</td>
                                </tr>
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
                                        <input type="text" id="datepicker" ref="datepicker" name="datepicker" :hidden="!showDatePickerSimple" @blur="showDatePickerSimple = false">
                                    </td>
                                    <td style="vertical-align: top;"></td>
                                    <td style="vertical-align: top;">
                                        <b>Tipo especial:</b>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <div style="overflow-y: auto;">
                                            <ul style="margin: 0; padding: 0; list-style-position: inside;">
                                                <li v-for="type in lSpecialTypes">@{{type}}</li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="type_id == oData.constants.TYPE_CUMPLEAÑOS">
                                    <td><b>Año aplicación:</b></td>
                                    <td><input type="number" class="form-control" v-model="birthDayYear" @blur="updateBirthDayYear" :readonly="isRevision"></td>
                                    <td><p>&nbsp &nbsp</p></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                        <div>
                            <label class="form-label" for="listDays"><b>Desglose de los días de incidencia:</b></label>
                            <ol class="ulColumns3" name="listDays">
                                <template v-for="(day, index) in lDays">
                                    <li v-bind:style="{'color': day.taked ? 'green' : 'red'}">
                                        <label class="" :for="'exampleCheck'+index">@{{day.date}}</label>
                                        <input v-if="!day.bussinesDay" type="checkbox" class="" :id="'exampleCheck'+index" v-on:click="setTakedDay(index, 'exampleCheck'+index);" :checked="day.taked">
                                    </li>
                                </template>
                            </ol>
                        </div>
                        <div v-if="isRevision">
                            <label for="comentarios_emp"><b>Comentarios del colaborador:</b></label>
                            <p v-if="emp_comments != null && emp_comments != ''">@{{emp_comments}}</p>
                            <p v-else>(Sin comentarios)</p>
                        </div>
                        <div>
                            <label class="form-label" for="comments"><b>Comentarios:</b></label>
                            <textarea class="form-control" name="comments" id="comments" style="width: 99%;" v-model="comments"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <template v-if="!isRevision">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" v-on:click="save()" v-if="valid">Guardar</a>
                </template>
                <template v-else-if="isRevision">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" v-on:click="approbeIncidence()" v-if="valid">Aprobar</a>
                    <button type="button" class="btn btn-danger" v-on:click="rejectIncidence()" v-if="valid">Rechazar</a>
                </template>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal_permission" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-small">
                <h5 class="modal-title" id="exampleModalLabel">Permiso</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body modal-body-small">
                <div class="card">
                    <div class="card-body card-body-small">
                        <div v-if="!isRevision">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="">Selecciona Tipo:</label>
                                </div>
                                <div class="col-md-9">
                                    <select class="select2-class-modal form-control" name="permission_type" id="permission_type" style="width: 90%;"></select>
                                </div>
                            </div>
                            <div class="myBreakLine"></div>
                            <div class="row">
                                <div class="col-md-3">
                                    <label for=""></label>
                                </div>
                                <div class="col-md-1">
                                    <label for="">Horas:</label>
                                </div>
                                <div class="col-md-1">
                                    <input type="number" class="form-input" id="horas" min="0" max="3" v-model="hours" v-on:focus="focusHours();" v-on:blur="formatValueHours();"/>
                                </div>
                                <div class="col-md-1">
                                    <label for="">Minutos:</label>
                                </div>
                                <div class="col-md-1">
                                    <input type="number" class="form-input" id="minutos" min="0" max="59" step="1" v-model="minutes" v-on:focus="focusMinutes();" v-on:blur="formatValueMinutes();"/>
                                </div>
                            </div>
                        </div>
                        <div v-if="isRevision">
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
                        </div>
                    </div>
                </div>
                <div style="text-align: center">
                    <div class="card">
                        <div class="card-body card-body-small">
                            <span id="two-inputs-calendar">
                                <span hidden>
                                    <input id="date-range-001" type="date" value="" class="form-control" style="width: 30%; display: inline" readonly> a <input id="date-range-002" type="date" value="" class="form-control" style="width: 30%; display: inline" readonly>
                                </span>
                                <table style="width: 100%;">
                                    <thead></thead>
                                    <tbody>
                                        <tr>
                                            <td style="width: 33%;">
                                                @include('layouts.Nomeclatura_calendario', ['id' => 'nomeclaturaMyRequest'])
                                            </td>
                                            <td v-if="!isRevision"  style="width: 33%;">
                                                <button type="button" class="btn btn-primary inline" id="clear":hidden="!valid">Limpiar</button>
                                            </td>
                                            <td>
                                                <!-- <input class="form-control" v-model="startDate" style="width: 40%; display: inline" readonly> a <input class="form-control" v-model="endDate" style="width: 40%; display: inline" readonly> -->
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="myBreakLine"></div>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body card-body-small">
                        <table>
                            <thead></thead>
                            <tbody>
                                <tr v-if="isRevision">
                                    <td style="vertical-align: top;"><b>Tipo:</b></td>
                                    <td style="vertical-align: top;"><input class="form-control" v-model="type_name" readonly></td>
                                    <td><p>&nbsp &nbsp</p></td>
                                    <td style="vertical-align: top;"><b>Tiempo</b></td>
                                    <td style="vertical-align: top;"><input class="form-control" v-model="time" readonly></td>
                                </tr>
                                <tr>
                                    <td style="vertical-align: top;"><b>Fecha</b></td>
                                    <td style="vertical-align: top;"><input class="form-control" v-model="startDate" readonly></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="myBreakLine"></div>
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
            <div class="modal-footer modal-footer-small">
                <template v-if="!isRevision">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" v-on:click="save()" v-if="valid">Guardar</a>
                </template>
                <template v-else-if="isRevision">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" v-on:click="approbePermission()" v-if="valid && isRevision">Aprobar</a>
                    <button type="button" class="btn btn-danger" v-on:click="rejectPermission()" v-if="valid && isRevision">Rechazar</a>
                </template>
            </div>
        </div>
    </div>
</div>
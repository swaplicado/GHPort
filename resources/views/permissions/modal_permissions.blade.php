<div class="modal fade" id="modal_permission" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-small">
                @if ($clase_permiso == 1 )
                    <h5 class="modal-title" id="exampleModalLabel">Permiso personal por horas</h5>
                @else
                    <h5 class="modal-title" id="exampleModalLabel">Tema laboral por horas</h5>
                @endif
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
                                    <label for="">Selecciona clase:*</label>
                                </div>
                                <div class="col-md-9">
                                    <select class="select2-class-modal form-control" name="permission_cl" id="permission_cl" style="width: 90%;"></select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="">Selecciona tipo:*</label>
                                </div>
                                <div class="col-md-9">
                                    <select class="select2-class-modal form-control" name="permission_type" id="permission_type" style="width: 90%;"></select>
                                </div>
                            </div>
                            <div class="myBreakLine"></div>
                            <div>
                                <span style="color: #4e73df">Seleccione un tipo y un dia del calendario para poder ingresar las horas</span>
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
                <div class="card" v-if="!isRevision">
                    <br>
                    <div v-if="(startDate != null && startDate != '') && type_id != null">
                        <div class="row" v-if="type_id == 1">
                            <template v-if="!haveSchedule">
                                <div class="col-md-4">
                                    <label for=""></label>
                                </div>
                                <div class="col-md-1">
                                    <label for=""><b>Horas:</b></label>
                                </div>
                                <div class="col-md-1">
                                    <input type="number" class="form-input" id="horas" min="0" max="24" step="1" v-model="hours" v-on:focus="focusHours();" v-on:blur="formatValueHours();"/>
                                </div>
                                <div class="col-md-1">
                                    <label for=""><b>Minutos:</b></label>
                                </div>
                                <div class="col-md-1">
                                    <input type="number" class="form-input" id="minutos" min="0" max="59" step="1" v-model="minutes" v-on:focus="focusMinutes();" v-on:blur="formatValueMinutes();"/>
                                </div>
                            </template>
                            <template v-else>
                                <div class="col-md-1">
                                    <label for=""><b>entrada:</b></label>
                                </div>
                                <div class="col-md-5">
                                    <input type="text" id="entryTime" class="form-control" ref="entryTime"
                                        name="example" autocomplete="off" :placeholder="entry" style="background-color: white !important"/>
                                </div>
                                <div class="col-md-1">
                                    <label for=""><b>salida:</b></label>
                                </div>
                                <div class="col-md-5">
                                    <input type="text" class="form-control" autocomplete="off" v-model="departure" disabled/>
                                </div>
                            </template>
                        </div>
                        <div class="row" v-if="type_id == 2 && (startDate != null && startDate != '')">
                            <template v-if="!haveSchedule">
                                <div class="col-md-4">
                                    <label for=""></label>
                                </div>
                                <div class="col-md-1">
                                    <label for=""><b>Horas:</b></label>
                                </div>
                                <div class="col-md-1">
                                    <input type="number" class="form-input" id="horas" min="0" max="24" v-model="hours" v-on:focus="focusHours();" v-on:blur="formatValueHours();"/>
                                </div>
                                <div class="col-md-1">
                                    <label for=""><b>Minutos:</b></label>
                                </div>
                                <div class="col-md-1">
                                    <input type="number" class="form-input" id="minutos" min="0" max="59" step="1" v-model="minutes" v-on:focus="focusMinutes();" v-on:blur="formatValueMinutes();"/>
                                </div>
                            </template>
                            <template v-else>
                                <div class="col-md-1">
                                    <label for=""><b>entrada:</b></label>
                                </div>
                                <div class="col-md-5">
                                    <input type="text" class="form-control" autocomplete="off" v-model="entry" disabled/>
                                </div>
                                <div class="col-md-1">
                                    <label for=""><b>salida:</b></label>
                                </div>
                                <div class="col-md-5">
                                    <input type="text" id="outTime" class="form-control" ref="outTime"
                                        name="example" autocomplete="off" :placeholder="departure" style="background-color: white !important"/>
                                </div>
                            </template>
                        </div>
                        <div class="row" v-if="type_id == 3 && (startDate != null && startDate != '')">
                            <div class="col-md-1">
                                <label for=""><b>Salida:</b></label>
                            </div>
                            <div class="col-md-5">
                                <input type="text" id="entryTime" class="form-control" ref="entryTime"
                                        name="example" autocomplete="off" :placeholder="entry" style="background-color: white !important"/>
                            </div>
                            <div class="col-md-1">
                                <label for=""><b>Regreso:</b></label>
                            </div>
                            <div class="col-md-5">
                                <input type="text" id="outTime" class="form-control" ref="outTime"
                                        name="example" autocomplete="off" :placeholder="departure" style="background-color: white !important"/>
                            </div>
                        </div>
                    </div>
                    <br>
                </div>
                <div class="card">
                    <div class="card-body card-body-small">
                        <template v-if="isRevision">
                            <div class="row">
                                <div class="col-md-1">
                                    <label for=""><b>Clase</b></label>
                                </div>
                                <div class="col-md-3">
                                    <input class="form-control" v-model="class_name" readonly>
                                </div>
                                <div class="col-md-1">
                                    <label for=""><b>Tipo</b></label>
                                </div>
                                <div class="col-md-3">
                                    <input class="form-control" v-model="type_name" readonly>
                                </div>
                                <div class="col-md-1">
                                    <label for=""><b>Tiempo</b></label>
                                </div>
                                <div class="col-md-3">
                                    <input class="form-control" v-model="time" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-1">
                                    <label for=""><b>Fecha</b></label>
                                </div>
                                <div class="col-md-3">
                                    <input class="form-control" v-model="startDate" readonly>
                                </div>
                                <template v-if="type_id != 3">
                                    <div class="col-md-1" v-if="haveSchedule == true">
                                        <label for=""><b>@{{ type_name }}</b></label>
                                    </div>
                                    <div class="col-md-3" v-if="haveSchedule == true">
                                        <input class="form-control" readonly :value="permission">
                                    </div>
                                    <div class="col-md-1">
                                        <label for=""><b>Horario</b></label>
                                    </div>
                                    <div class="col-md-3">
                                        <input class="form-control" readonly :value="haveSchedule == true ? (entry + ' a ' + departure) : 'Sin horario'">
                                    </div>
                                </template>
                                <template v-if="type_id == 3">
                                    <div class="col-md-1">
                                        <label for=""><b>Salida</b></label>
                                    </div>
                                    <div class="col-md-3">
                                        <input class="form-control" readonly :value="permission.inter_out">
                                    </div>
                                    <div class="col-md-1">
                                        <label for=""><b>Regreso</b></label>
                                    </div>
                                    <div class="col-md-3">
                                        <input class="form-control" readonly :value="permission.inter_ret">
                                    </div>
                                </template>
                            </div>
                            <div v-if="type_id == 3" class="row">
                                <div class="col-md-1">
                                    <label for=""><b>Horario</b></label>
                                </div>
                                <div class="col-md-3">
                                    <input class="form-control" readonly :value="haveSchedule == true ? (entry + ' a ' + departure) : 'Sin horario'">
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <div class="row">
                                <div class="col-md-1">
                                    <label for=""><b>Fecha</b></label>
                                </div>
                                <div class="col-md-3">
                                    <input class="form-control" v-model="startDate" readonly>
                                </div>
                                <div class="col-md-1">
                                    <label for=""><b>Horario</b></label>
                                </div>
                                <div class="col-md-3">
                                    <input class="form-control" readonly :value="haveSchedule == true ? (entry + ' a ' + departure) : 'Sin horario'">
                                </div>
                                <div class="col-md-1">
                                    <label for=""><b>Tiempo</b></label>
                                </div>
                                <div class="col-md-3">
                                    <input class="form-control" readonly :value="totalTime">
                                </div>
                            </div>
                        </template>
                        {{-- <table>
                            <thead></thead>
                            <tbody>
                                <tr v-if="isRevision">
                                    <td style="vertical-align: top;"><b>Clase:</b></td>
                                    <td style="vertical-align: top;"><input class="form-control" v-model="class_name" readonly></td>
                                    <td style="vertical-align: top;"><b>Tipo:</b></td>
                                    <td style="vertical-align: top;"><input class="form-control" v-model="type_name" readonly></td>
                                    <td><p>&nbsp &nbsp</p></td>
                                    <td style="vertical-align: top;"><b>Tiempo</b></td>
                                    <td style="vertical-align: top;"><input class="form-control" v-model="time" readonly></td>
                                </tr>
                                <tr>
                                    <td style="vertical-align: top;"><b>Fecha</b></td>
                                    <td style="vertical-align: top;"><input class="form-control" v-model="startDate" readonly></td>
                                    <td>Horario:</td>
                                    <td><input class="form-control" readonly :value="entry + ' a ' + departure"></td>
                                </tr>
                            </tbody>
                        </table> --}}
                        
                        <div class="myBreakLine"></div>
                        <div v-if="isRevision">
                            <label for="comentarios_emp"><b>Comentarios del colaborador:</b></label>
                            <p v-if="emp_comments != null && emp_comments != ''">@{{emp_comments}}</p>
                            <p v-else>(Sin comentarios)</p>
                        </div>
                        <div v-if="isRevision">
                            <label class="form-label" for="comments"><b>Comentarios:</b></label>
                            <textarea class="form-control" name="comments" id="comments" style="width: 99%;" v-model="comments"></textarea>
                        </div>
                        <div v-else>
                            <label class="form-label" for="comments"><b>Comentarios:*</b></label>
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
                    <button type="button" class="btn btn-success" v-on:click="approbePermission()" v-if="valid && isRevision"><span class="bx bxs-like"></span>&nbsp Aprobar</a>
                    <button type="button" class="btn btn-danger" v-on:click="rejectPermission()" v-if="valid && isRevision"><span class="bx bxs-dislike"></span>&nbsp Rechazar</a>
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                </template>
            </div>
        </div>
    </div>
</div>
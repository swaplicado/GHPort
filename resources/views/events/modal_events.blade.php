<div class="modal fade" id="modal_events" tabindex="-1" role="dialog" aria-labelledby="modal_events_label"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-small">
                <h5 class="modal-title" id="modal_events_label">@{{isEdit == true ? 'Editar evento' : 'Nuevo evento'}}</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body modal-body-small">
                <div class="container">
                    <div class="row">
                        <div class="col-md-2">
                            <label for="">Nombre:*</label>
                        </div>
                        <div class="col-md-10">
                            <input type="text" class="form-control" v-model="eventName">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <label for="">Prioridad:*</label>
                        </div>
                        <div class="col-md-2">
                            <input type="number" class="form-control" v-model="priority">
                        </div>
                    </div>
                    <br>
                    <div class="row centerItem">
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
                                        <td>
                                            <button type="button" class="btn btn-primary inline" id="clear":hidden="!valid">Limpiar</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="myBreakLine"></div>
                        </span>
                    </div>
                    <div class="row" style="margin-top: 5px;">
                        <div class="col-md-2">
                            <label for="startDate">Fecha inicio:</label>
                        </div>
                        <div class="col-md-4">
                            <input type="text" id="startDate" name="startDate" class="form-control" v-model="startDate" disabled>
                        </div>

                        <div class="col-md-2">
                            <label for="endDate">Fecha fin:</label>
                        </div>
                        <div class="col-md-4">
                            <input type="text" id="endDate" name="endDate" class="form-control" v-model="endDate" disabled>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <label for="takedDays">Días efectivos:</label>
                        </div>
                        <div class="col-md-4">
                            <input type="text" id="takedDays" name="takedDays" class="form-control" v-model="takedDays" disabled>
                        </div>
                        <div class="col-md-2">
                            <label for="totalDays">Días totales:</label>
                        </div>
                        <div class="col-md-4">
                            <input type="text" id="totalDays" name="totalDays" class="form-control" v-model="totCalendarDays" disabled>
                        </div>
                    </div>
                    <div class="myBreakLine"></div>
                    <div>
                        <label class="form-label" for="listDays"><b>Desglose de los días de calendario:</b></label>
                        <ol class="ulColumns3" name="listDays">
                            <template v-for="(day, index) in lDays">
                                <li v-bind:style="{'color': day.taked ? 'green' : 'red'}">
                                    <label class="" :for="'exampleCheck'+index">@{{day.date}}</label>
                                    <input v-if="!day.bussinesDay" type="checkbox" class="" :id="'exampleCheck'+index" v-on:click="setTakedDay(index, 'exampleCheck'+index);" :checked="day.taked">
                                </li>
                            </template>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="modal-footer modal-footer-small">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" v-on:click="saveEvent()">Guardar</a>
            </div>
        </div>
    </div>
</div>
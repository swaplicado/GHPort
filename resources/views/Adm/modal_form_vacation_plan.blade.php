<div class="modal fade" id="modal_vacation_plan" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Plan de vacaciones</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <div class="row justify-content-center">
                            <div class="col-md-1">
                                <label class="form-label">Nombre:</label>
                            </div>
                            <div class="col-md-4">
                                <input id="name" class="form-control" :disabled="onlyShow" v-model="name">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tipo pago:</label>
                            </div>
                            <div class="col-md-2" style="padding-left: 0;">
                                <select class="form-control" :disabled="onlyShow" v-model="payment_frec">
                                    <option value="0">Ambos</option>
                                    <option value="1">Semana</option>
                                    <option value="2">Quincena</option>
                                </select>
                            </div>
                        </div>
                        <br>
                        <div class="row justify-content-center">
                            <div class="col-md-1">
                                <label class="form-label">Fecha inicio:</label>
                            </div>
                            <div class="col-md-4">
                                <input type="date" class="form-control" :disabled="onlyShow" v-model="start_date">
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <label class="form-label" for="flexCheckIndeterminate">
                                        Sindicalizado
                                    </label>
                                    <input class="form-input" type="checkbox" :disabled="onlyShow" id="flexCheckIndeterminate" v-model="unionized">
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <table class="table">
                                    <thead>
                                        <th>
                                            <a v-if="!onlyShow" href="#" v-on:click="showInfo();">
                                                <span class="bx bx-info-circle"></span>
                                            </a>
                                            Año
                                        </th>
                                        <th>Días</th>
                                        <th>
                                            <button id="btn_add" type="button" v-if="!onlyShow" class="btn3d btn-info" title="Agregar renglon" v-on:click="addRow();">
                                                <span class="bx bx-plus"></span>
                                            </button>
                                        </th>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(row, index) in years" :key="index">
                                            <td><input class="form-control" type="number" :disabled="onlyShow" v-model="years[index].year" max="50" :readonly="index == 0" v-on:change="recalcRows(index);"></td>
                                            <td><input class="form-control" type="number" :disabled="onlyShow" v-model="years[index].days" v-on:change="checkDays(index);"></td>
                                            <td>
                                                <button v-if="index > 0 && !onlyShow" id="btn_rem" type="button" class="btn-circle btn-danger btn-sm" style="border: none;" title="Eliminar renglon" v-on:click="removeRow(index)">
                                                    <span class="bx bx-minus"></span>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" v-if="!onlyShow">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" :disabled="disabledSave" v-on:click="saveVacationPlan();">Guardar</a>
            </div>
            <div class="modal-footer" v-else>
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
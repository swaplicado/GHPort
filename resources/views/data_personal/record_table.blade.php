<div class="table-responsive">
    <table class="table table-bordered" id="{{$table_id}}" ref="{{$table_ref}}" width="100%" cellspacing="0">
        <thead class="thead-light">
            <th>Empresa</th>
            <th>Empleado</th>
            <th>Constancia sin salario</th>
            <th>Constancia con salario</th>
        </thead>
        <tbody>
            <tr v-for="User in DataUser">
                <td style="text-align: center;" v-if="User.company_id == 1" ><img src="{{ asset('img/aeth_logo.png') }}" width="50" height="50" alt="" ></td>
                <td style="text-align: center;" v-else-if="User.company_id == 2"><img src="{{ asset('img/tron.png') }}" width="50" height="50" alt="" ></td>
                <td style="text-align: center;" v-else-if="User.company_id == 3"><img src="{{ asset('img/tron.png') }}" width="50" height="50" alt="" ></td>
                <td style="text-align: center;" v-else-if="User.company_id == 4"><img src="{{ asset('img/swap_logo.png') }}" width="50" height="50" alt="" ></td>
                <td style="text-align: center;" v-else-if="User.company_id == 5"><img src="{{ asset('img/ame.png') }}" width="50" height="50" alt="" ></td>
                <td>@{{User.name}}</td>
                <td style="text-align: center;"><button v-on:click="download(User.id, false)" :id="User.id" type="button" class="btn3d btn-info" style="display: inline-block; margin-right: 5px" title="Ver y descargar">
                    <span class="bx bxs-download"></span></button></td>
                <td style="text-align: center;"><button v-on:click="download(User.id, true)" :id="User.id" type="button" class="btn3d btn-info" style="display: inline-block; margin-right: 5px" title="Ver y descargar">
                    <span class="bx bxs-download"></span></button></td>
            </tr>
        </tbody>
    </table>
</div>




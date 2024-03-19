<div class="table-responsive">
    <table class="table table-bordered" id="{{$table_id}}" ref="{{$table_ref}}" width="100%" cellspacing="0">
        <thead class="thead-light">
            <th>Usuario descarga</th>
            <th>Constancia colaborador</th>
            <th>Empresa colaborador</th>
            <th>Constancia con salario</th>
            <th>Fecha y hora</th>
        </thead>
        <tbody>
            <tr v-for="User in DataUser">
                <td>@{{User.full_name}}</td>
                <td>@{{User.full_name}}</td>
                <td style="text-align: center;" v-if="User.id_comp == 1" ><img src="{{ asset('img/aeth_logo.png') }}" width="50" height="50" alt="" ></td>
                <td style="text-align: center;" v-else-if="User.id_comp == 2"><img src="{{ asset('img/tron.png') }}" width="50" height="50" alt="" ></td>
                <td style="text-align: center;" v-else-if="User.id_comp == 3"><img src="{{ asset('img/tron.png') }}" width="50" height="50" alt="" ></td>
                <td style="text-align: center;" v-else-if="User.id_comp == 4"><img src="{{ asset('img/swap_logo.png') }}" width="50" height="50" alt="" ></td>
                <td style="text-align: center;" v-else-if="User.id_comp == 5"><img src="{{ asset('img/ame.png') }}" width="50" height="50" alt="" ></td>                
                <td style="text-align: center;" v-if="User.isSalary == 0"><img src="{{ asset('img/tache.png') }}" width="50" height="50" alt="" ></td>
                <td  style="text-align: center;" v-else-if="User.isSalary == 1"><img src="{{ asset('img/check.png') }}" width="50" height="50" alt="" ></td>
                <td>@{{oDateUtils.formatDate(User.created_at,'DD-MMM-YYYY HH:mm:ss')}}</td>
               
            </tr>
        </tbody>
    </table>
</div>




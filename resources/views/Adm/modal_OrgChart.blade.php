<div class="modal fade" id="modal_OrgChart" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">@{{name}}</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row justify-content-md-center">
                    <div class="col-md-12" style="text-align: center">
                        <img :src="img" style="border-radius:100px;width:80px;height:80px;">
                    </div>
                    <table class="table" style="width: 90%;">
                        <thead>
                            <tr>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="name != null && name != ''">
                                <td><b>Nombre:</b></td>
                                <td>@{{name}}</td>
                            </tr>
                            <tr>
                                <td><b>Nodo org.:</b></td>
                                <td>@{{area}}</td>
                            </tr>
                            <tr>
                                <td><b>Colab act/Colab Req:</b>&nbsp;</td>
                                <td>@{{jobs}}</td>
                            </tr>
                            <tr v-if="users.length > 1">
                                <table class="table" style="width: 90%;">
                                    <thead>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                    </thead>
                                    <tbody>
                                        <tr v-for="user in users">
                                            <td><b>Colaborador:</b></td>
                                            <td>@{{user.full_name_ui}}</td>
                                            <td>
                                                <template v-if="user.photo_base64_n">
                                                    <img class="rounded-circle" :src="'data:image/jpg;base64,'+user.photo_base64_n" style="width:3vmax;height:3vmax;">
                                                </template>
                                                <template v-else>
                                                    <img class="rounded-circle" src="{{ asset('img/avatar/profile2.png') }}" style="width:3vmax;height:3vmax;">
                                                </template>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
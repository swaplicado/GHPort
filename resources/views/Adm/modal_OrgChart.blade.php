<div class="modal fade" id="modal_OrgChart" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
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
                    <table>
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
                                <td><b>Area:</b></td>
                                <td>@{{area}}</td>
                            </tr>
                            <tr>
                                <td><b>Colab act/Colab Req:</b>&nbsp;</td>
                                <td>@{{jobs}}</td>
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
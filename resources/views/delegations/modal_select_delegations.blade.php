<div class="modal fade" id="modal_select_delegation" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    <b>Delegaciones</b>
                    <a href="http://192.168.1.251/dokuwiki/doku.php?id=wiki:logindelegacion" target="_blank">
                        <span class="bx bx-question-mark btn3d" style="display: inline-block; margin-left: 10px; background-color: #e4e4e4"></span>
                    </a>
                </h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form action="{{route('delegation_setDelegation')}}" method="POST">
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                            @csrf
                            <div class="row">
                                <div class="col-md-8">
                                    <label for="">Selecciona usuario:*</label>
                                    <select class="form-control" name="user_delegation" id="user_delegation">
                                        <option value=""></option>
                                        @foreach(Session::get('lDelegations') as $oDel)
                                            <option value="{{$oDel['id']}}">{{$oDel['name']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Seleccionar usuario</button>
                    </div>
                </form>
                <form action="{{route('delegation_recoverDelegation')}}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary">Regresar a mi usuario</button>
                </form>
            </div>
        </div>
    </div>
</div>
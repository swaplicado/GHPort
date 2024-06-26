@if(isset($crear))
    <button id="btn_crear" type="button" class="btn3d btn-success" style="display: inline-block; margin-right: 20px" title="Crear solicitud">
        <span class="bx bx-plus"></span>
    </button>
@endif
@if(isset($editar))
    <button id="btn_edit" type="button" class="btn3d btn-warning" style="display: inline-block; margin-right: 20px" title="Modificar solicitud">
        <span class="bx bx-edit-alt"></span>
    </button>
@endif
@if(isset($send))
    <button id="btn_send" type="button" class="btn3d btn-info" style="display: inline-block; margin-right: 20px" title="Enviar solicitud">
        <span class="bx bx-paper-plane"></span>
    </button>
@endif

@if(isset($sendAprov))
    @if(isset($sendAprovMethod))
        <button id="btn_sendAprov" onclick="{{$sendAprovMethod}}" type="button" class="btn3d" style="display: inline-block; margin-right: 20px; background-color: #4DB6AC" title="Enviar y autorizar">
            <span class="bx bxs-send"></span>
        </button>
    @endif

    @if(isset($sendAprovVueMethod))
        <button id="btn_sendAprov" type="button" v-on:click="{{$sendAprovVueMethod}}" class="btn3d" style="display: inline-block; margin-right: 20px; background-color: #4DB6AC" title="Enviar y autorizar">
            <span class="bx bxs-send"></span>
        </button>
    @endif

    @if(!isset($sendAprovMethod) && !isset($sendAprovVueMethod))
        <button id="btn_sendAprov" type="button" class="btn3d" style="display: inline-block; margin-right: 20px; background-color: #4DB6AC" title="Enviar y autorizar">
            <span class="bx bxs-send"></span>
        </button>
    @endif
@endif

@if(isset($accept))
    <button id="btn_accept" type="button" class="btn3d btn-success" style="display: inline-block; margin-right: 20px" title="Aprobar">
        <span class="bx bxs-like"></span>
    </button>
@endif
@if(isset($reject))
    <button id="btn_reject" type="button" class="btn3d btn-danger" style="display: inline-block; margin-right: 20px" title="Rechazar">
        <span class="bx bxs-dislike"></span>
    </button>
@endif
@if(isset($show))
    <button id="btn_show" type="button" class="btn3d bg-gray-400" style="display: inline-block; margin-right: 20px" title="Ver solicitud">
        <span class="bx bx-show-alt"></span>
    </button>
@endif
@if(isset($cancel))
    <button id="btn_cancel" type="button" class="btn3d btn-danger" style="display: inline-block; margin-right: 20px" title="Cancelar solicitud">
        <span class="bx bx-x"></span>
    </button>
@endif

@if(isset($asign))
    @if(isset($asignMethod))
        <button id="btn_asign" type="button" class="btn3d bg-gradient-light" 
            style="display: inline-block; margin-right: 20px" title="Asignaciones" onclick="{{$asignMethod}}">
            <span class="bx bx-transfer-alt"></span>
        </button>
    @endif

    @if(isset($asignVueMethod))
        <button id="btn_asign" type="button" class="btn3d bg-gradient-light" 
            style="display: inline-block; margin-right: 20px" title="Asignaciones" v-on:click="{{$asignVueMethod}}">
            <span class="bx bx-transfer-alt"></span>
        </button>
    @endif

    @if(!isset($asignMethod) && !isset($asignVueMethod))
        <button id="btn_asign" type="button" class="btn3d bg-gradient-light" 
            style="display: inline-block; margin-right: 20px" title="Asignaciones">
            <span class="bx bx-transfer-alt"></span>
        </button>
    @endif
@endif

@if(isset($delete))
    <button id="btn_delete" type="button" class="btn3d btn-danger" style="display: inline-block; margin-right: 20px" title="Eliminar solicitud">
        <span class="bx bxs-trash"></span>
    </button>
@endif
@if(isset($filterYear))
    <label>Filtrar por año:</label>
    <button v-on:click="year = year - 1;" class="btn btn-secondary" type="button" style = "display: inline;">
        <span class="bx bx-minus" ></span>
    </button>
    <input type="number" class="form-control" v-model="year" readonly style="width: 10ch; display: inline;">
    <button v-on:click="year = year + 1;" class="btn btn-secondary" type="button" style = "display: inline;">
        <span class="bx bx-plus"></span>
    </button>
    <button type="button" class="btn btn-primary"  v-on:click="filterYear();">
        <span class="bx bx-search"></span>
    </button>
@endif
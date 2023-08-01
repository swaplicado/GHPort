@if(isset($crear))
    <button id="btn_crear" type="button" class="btn3d btn-success" style="display: inline-block; margin-right: 5px" title="Nuevo registro">
        <span class="bx bx-plus"></span>
    </button>
@endif
@if(isset($editar))
    <button id="btn_edit" type="button" class="btn3d btn-warning" style="display: inline-block; margin-right: 5px" title="Editar registro">
        <span class="bx bx-edit-alt"></span>
    </button>
@endif
@if(isset($delete))
    <button id="btn_delete" type="button" class="btn3d btn-danger" style="display: inline-block; margin-right: 5px" title="Eliminar registro">
        <span class="bx bxs-trash"></span>
    </button>
@endif
@if(isset($send))
    <button id="btn_send" type="button" class="btn3d btn-info" style="display: inline-block; margin-right: 5px" title="Enviar">
        <span class="bx bx-paper-plane"></span>
    </button>
@endif
@if(isset($accept))
    <button id="btn_accept" type="button" class="btn3d btn-success" style="display: inline-block; margin-right: 5px" title="Aprobar">
        <span class="bx bxs-like"></span>
    </button>
@endif
@if(isset($reject))
    <button id="btn_reject" type="button" class="btn3d btn-danger" style="display: inline-block; margin-right: 5px" title="Rechazar">
        <span class="bx bxs-dislike"></span>
    </button>
@endif
@if(isset($show))
    <button id="btn_show" type="button" class="btn3d bg-gray-400" style="display: inline-block; margin-right: 5px" title="Ver registro">
        <span class="bx bx-show-alt"></span>
    </button>
@endif
@if(isset($cancel))
    <button id="btn_cancel" type="button" class="btn3d btn-danger" style="display: inline-block; margin-right: 5px" title="Cancelar">
        <span class="bx bx-x"></span>
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
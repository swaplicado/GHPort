@if(isset($crear))
    <button id="btn_crear" type="button" class="btn3d btn-success" style="border-radius: 50%; padding: 5px 10px; display: inline-block; margin-right: 5px" title="Nuevo registro">
        <span class="bx bx-plus"></span>
    </button>
@endif
@if(isset($editar))
    <button id="btn_edit" type="button" class="btn3d btn-warning" style="border-radius: 50%; padding: 5px 10px; display: inline-block; margin-right: 5px" title="Editar registro">
        <span class="bx bx-edit-alt"></span>
    </button>
@endif
@if(isset($delete))
    <button id="btn_delete" type="button" class="btn3d btn-danger" style="border-radius: 50%; padding: 5px 10px; display: inline-block; margin-right: 5px" title="Eliminar registro">
        <span class="bx bxs-trash"></span>
    </button>
@endif
@if(isset($send))
    <button id="btn_send" type="button" class="btn3d btn-info" style="border-radius: 50%; padding: 5px 10px; display: inline-block; margin-right: 5px" title="Enviar">
        <span class="bx bx-paper-plane"></span>
    </button>
@endif
@if(isset($accept))
    <button id="btn_accept" type="button" class="btn3d btn-success" style="border-radius: 50%; padding: 5px 10px; display: inline-block; margin-right: 5px" title="Aprobar">
        <span class="bx bxs-like"></span>
    </button>
@endif
@if(isset($reject))
    <button id="btn_reject" type="button" class="btn3d btn-danger" style="border-radius: 50%; padding: 5px 10px; display: inline-block; margin-right: 5px" title="Rechazar">
        <span class="bx bxs-dislike"></span>
    </button>
@endif
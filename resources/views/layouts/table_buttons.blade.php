@if(isset($crear))
    <button id="btn_crear" type="button" class="btn3d btn-success" style="border-radius: 50%; padding: 5px 10px; display: inline-block; margin-right: 5px" title="Editar registro">
        <span class="icon bx bx-plus"></span>
    </button>
@endif
@if(isset($editar))
    <button id="btn_edit" type="button" class="btn3d btn-warning" style="border-radius: 50%; padding: 5px 10px; display: inline-block; margin-right: 5px" title="Editar registro">
        <span class="icon bx bx-edit-alt"></span>
    </button>
@endif
@if(isset($delete))
    <button id="btn_delete" type="button" class="btn3d btn-danger" style="border-radius: 50%; padding: 5px 10px; display: inline-block; margin-right: 5px" title="Editar registro">
        <span class="icon bx bxs-trash"></span>
    </button>
@endif
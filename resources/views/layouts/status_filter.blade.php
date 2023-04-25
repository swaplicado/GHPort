@if($filterType == 1)
<label for="rqStatus">Filtrar por estatus: </label>
<select class="form-control inline" name="{{$status_name}}" id="{{$status_id}}" style="width: {{$width}};">
    <option value="1" selected>Creados</option>
    <option value="2">Enviados</option>
    <option value="3">Aprobados</option>
    <option value="4">Rechazados</option>
</select>&nbsp;&nbsp;
@elseif($filterType == 2)
<label for="rqStatus">Filtrar por estatus: </label>
<select class="form-control inline" name="{{$status_name}}" id="{{$status_id}}" style="width: {{$width}};">
    <option value="2" selected>Nuevos</option>
    <option value="3">Aprobados</option>
    <option value="4">Rechazados</option>
</select>&nbsp;&nbsp;
@endif
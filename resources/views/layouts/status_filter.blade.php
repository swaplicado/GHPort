@if($filterType == 1)
<label for="rqStatus">Filtrar por estatus: </label>
<select class="select2-class form-control inline" name="{{$status_name}}" id="{{$status_id}}" style="width: {{$width}};">
    @foreach($lStatus as $st)
        @if($st->id == 1)
            <option value="{{$st->id}}" selected>{{$st->name}}</option>
        @else
            <option value="{{$st->id}}">{{$st->name}}</option>
        @endif
    @endforeach
    {{--<option value="1" selected>Creados</option>
    <option value="2">Enviados</option>
    <option value="3">Aprobados</option>
    <option value="4">Rechazados</option>
    <option value="6">Cancelados</option>--}}
</select>&nbsp;&nbsp;
@elseif($filterType == 2)
<label for="rqStatus">Filtrar por estatus: </label>
<select class="select2-class form-control inline" name="{{$status_name}}" id="{{$status_id}}" style="width: {{$width}};">
    @foreach($lStatus as $st)
        @if($st->id == 2)
            <option value="{{$st->id}}" selected>{{$st->name}}</option>
        @else
            <option value="{{$st->id}}">{{$st->name}}</option>
        @endif
    @endforeach
    {{--<option value="2" selected>Nuevos</option>
    <option value="3">Aprobados</option>
    <option value="4">Rechazados</option>
    <option value="6">Cancelados</option>--}}
</select>&nbsp;&nbsp;
@endif
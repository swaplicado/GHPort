<button class="bx bx-question-mark btn3d" onclick="document.getElementById('customdiv').style.display == 'none' ? document.getElementById('customdiv').style.display = 'block' : document.getElementById('customdiv').style.display = 'none';" title="Popover Header">
</button>
<div class="row">
    <div class="col-xs-12 col-md-8" id="customdiv" style="display: none; z-index: 2; position: absolute; background-color: white; border: solid 1px black;">
        <label><b>Código de colores:</b></label>
        <a href="#" style="float: right; color: black;" onclick="document.getElementById('customdiv').style.display = 'none'"><b>X</b></a>
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($nomeclatura as $nome)
                    <tr>
                        @if ($nome['type'] == 'color')
                            <td style="background-color: {{$nome['color']}}"></td>
                            <td>{{$nome['text']}}</td>
                        @elseif($nome['type'] == 'img')
                            <td><img src="{{asset($nome['img'])}}" width="{{$nome['width']}}" height="{{$nome['height']}}"></td>
                            <td>{{$nome['text']}}</td>
                        @elseif($nome['type'] == 'class')
                            <td class="{{$nome['class']}}"></td>
                            <td>{{$nome['text']}}</td>
                        @endif
                    </tr>
                    <tr>
                        <td> </td>
                        <td> </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <br>
    </div>
</div>
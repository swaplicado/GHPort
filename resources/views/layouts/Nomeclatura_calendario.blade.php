<button class="bx bxs-palette btn3d" onclick="document.getElementById('{{$id}}').style.display == 'none' ? document.getElementById('{{$id}}').style.display = 'block' : document.getElementById('{{$id}}').style.display = 'none';" title="Código de colores">
</button>
<div class="row">
    <div class="col-xs-12 col-md-8" id="{{$id}}" style="display: none; z-index: 2; position: absolute; background-color: white; border: solid 1px black;">
        <label><b>Código de colores:</b></label>
        <a href="#" style="float: right; color: black;" onclick="document.getElementById('{{$id}}').style.display = 'none'"><b>X</b></a>
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="background-color: #49e"></td>
                    <td>(Solicitud de vacaciones actual)</td>
                </tr>
                <tr>
                    <td style="background-color: #e0e0e0b1"></td>
                    <td>(Día inhábil)</td>
                </tr>
                <tr>
                    <td style="background-color: #9f55d4"></td>
                    <td>(Día festivo)</td>
                </tr>
                <tr>
                    <td style="background-color: #f590eb"></td>
                    <td>(Solicitud de vacaciones)</td>
                </tr>
                <tr>
                    <td style="background-color: #ffe684"></td>
                    <td>(Día actual)</td>
                </tr>
                <tr>
                    <td><img src="{{asset('img/confetti.png')}}" width="30px" height="30px"></td>
                    <td>(Aniversario)</td>
                </tr>
                <tr>
                    <td><img src="{{asset('img/birthday-cake.png')}}" width="30px" height="30px"></td>
                    <td>(Cumpleaños)</td>
                </tr>
                <tr>
                    <td style="background-color: #66FF66"></td>
                    <td>(Temporada 5)</td>
                </tr>
                <tr>
                    <td style="background-color: #00FF00"></td>
                    <td>(Temporada 4)</td>
                </tr>
                <tr>
                    <td style="background-color: #00CC00"></td>
                    <td>(Temporada 3)</td>
                </tr>
                <tr>
                    <td style="background-color: #009900"></td>
                    <td>(Temporada 2)</td>
                </tr>
                <tr>
                    <td style="background-color: #006600"></td>
                    <td>(Temporada 1)</td>
                </tr>
            </tbody>
        </table>
        <br>
    </div>
</div>
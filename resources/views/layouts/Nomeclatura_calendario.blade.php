<button class="btn3d" onclick="document.getElementById('{{$id}}').style.display == 'none' ? document.getElementById('{{$id}}').style.display = 'block' : document.getElementById('{{$id}}').style.display = 'none';" title="Código de colores">
    <span class="bx bxs-palette "></span>
</button>
<div class="inline">
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
                    <td style="text-align: left;">&nbsp (Inicio/Fin de la solicitud)</td>
                </tr>
                <tr>
                    <td style="background-color: #f590eb"></td>
                    <td style="text-align: left;">&nbsp (Día de vacaciones)</td>
                </tr>
                <tr>
                    <td style="background-color: #ffad72"></td>
                    <td style="text-align: left;">&nbsp (Día con incidencia)</td>
                </tr>
                <tr>
                    <td style="background-color: #e0e0e0b1"></td>
                    <td style="text-align: left;">&nbsp (Día inhábil)</td>
                </tr>
                <tr>
                    <td style="background-color: #9f55d4"></td>
                    <td style="text-align: left;">&nbsp (Día festivo)</td>
                </tr>
                <tr>
                    <td style="background-color: #ffe684"></td>
                    <td style="text-align: left;">&nbsp (Día actual)</td>
                </tr>
                <tr>
                    <td><img src="{{asset('img/confetti.png')}}" width="30px" height="30px"></td>
                    <td style="text-align: left;">&nbsp (Aniversario del colaborador)</td>
                </tr>
                <tr>
                    <td><img src="{{asset('img/birthday-cake.png')}}" width="30px" height="30px"></td>
                    <td style="text-align: left;">&nbsp (Cumpleaños del colaborador)</td>
                </tr>
                <tr v-for="temp in lTemp">
                    <td v-bind:style="{backgroundColor: temp.color}"></td>
                    <td style="text-align: left;">&nbsp @{{temp.name}}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@extends('layouts.principal')

@section('content')
  <div class="card shadow mb-4">
    <div class="card-header">
      <h3>
        <b>Inicio</b>
        <a href="http://192.168.1.251/dokuwiki/doku.php?id=wiki:navegacion" target="_blank" title="Ayuda">
            <span class="bx bx-question-mark bx-tada bx-xs btn3d"
                style="display: inline-block; margin-left: 10px; background-color: #4e73df; color:white ;transform: tralade(1%)"></span>
        </a>
      </h3>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-1">
        </div>
        <div class="col-md-10" style="text-align: center">
          <figure>
            <blockquote class="blockquote">
              <h1>Bienvenido</h1>
              <h1>{{\App\Utils\delegationUtils::getFullNameUser()}}</h1>
              <h1> a Portal GH</h1>
            </blockquote>
            <figcaption class="blockquote-footer" style="padding-left: 7%">
                Bienvenido {{\App\Utils\delegationUtils::getFullNameUser()}} a Portal de Gestión Humana
            </figcaption>
          </figure>
        </div>
        <div class="col-md-1">
        </div>
      </div>
    </div>
  </div>
@endsection

@extends('layouts.principal')

@section('content')
<div class="container">
    <div class="row">
      <div class="col-md-1">
      </div>
      <div class="col-md-10" style="text-align: center">
        <figure>
          <blockquote class="blockquote">
            <h1>Bienvenido</h1>
            <h1>{{\App\Utils\delegationUtils::getFullNameUser()}}</h1>
            <h1> a PGH</h1>
          </blockquote>
          <figcaption class="blockquote-footer" style="padding-left: 7%">
              Bienvenido {{\App\Utils\delegationUtils::getFullNameUser()}} a Portal Gestión Humana
          </figcaption>
        </figure>
      </div>
      <div class="col-md-1">
      </div>
    </div>
  </div>
@endsection

@extends('layouts.principal')

@section('content')
<div class="container">
    <div class="row">
      <div class="col">
      </div>
      <div class="col-md-8">
        <figure>
          <blockquote class="blockquote">
            <h1>Bienvenido {{Auth::user()->full_name}} a PGH</h1>
          </blockquote>
          <figcaption class="blockquote-footer" style="padding-left: 7%">
            Bienvenido {{Auth::user()->full_name}} a Portal Gestion Humana
          </figcaption>
        </figure>
      </div>
    </div>
  </div>
@endsection

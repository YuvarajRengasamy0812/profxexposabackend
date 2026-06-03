@extends('frontEnd.layouts.roymaster')

@section('content')

<div class="home-page">

    @include('frontEnd.Roy.roybanner')
    @include('frontEnd.Roy.royabout')
    @include('frontEnd.Roy.royaluminium')
      @include('frontEnd.Roy.royservices')
    
       @include('frontEnd.Roy.royquestions')
       @include('frontEnd.Roy.roydoor')
    @include('frontEnd.Roy.roysection')

    @include('frontEnd.Roy.roycontact')

</div>

@endsection

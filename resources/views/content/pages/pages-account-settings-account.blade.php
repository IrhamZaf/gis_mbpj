@extends('layouts/layoutMaster')

@section('title', 'Tetapan akaun')

@section('content')
@include('content.pages.partials.demo-stub', [
  'title' => 'Tetapan akaun',
  'heading' => 'Tetapan akaun',
  'message' => 'Urus nama dan e-mel melalui pentadbir sistem. Untuk pertukaran kata laluan, hubungi admin GIS MBPJ.',
])
@endsection

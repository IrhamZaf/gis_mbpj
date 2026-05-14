@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Log Masuk - GIS MBPJ')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/@form-validation/popular.js',
'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/pages-auth.js'])
@endsection

@section('content')
<div class="authentication-wrapper authentication-cover">
  <a href="{{ url('/') }}" class="app-brand auth-cover-brand">
    <span class="app-brand-logo demo">@include('_partials.macros')</span>
    <span class="app-brand-text demo text-heading fw-bold">{{ config('variables.templateName') }}</span>
  </a>
  <div class="authentication-inner row m-0">
    <div class="d-none d-xl-flex col-xl-8 p-0">
      <div class="auth-cover-bg d-flex justify-content-center align-items-center">
        <img src="{{ asset('assets/img/illustrations/auth-login-illustration-' . $configData['theme'] . '.png') }}"
          alt="auth-login-cover" class="my-5 auth-illustration"
          data-app-light-img="illustrations/auth-login-illustration-light.png"
          data-app-dark-img="illustrations/auth-login-illustration-dark.png" />
        <img src="{{ asset('assets/img/illustrations/bg-shape-image-' . $configData['theme'] . '.png') }}"
          alt="auth-login-cover" class="platform-bg" data-app-light-img="illustrations/bg-shape-image-light.png"
          data-app-dark-img="illustrations/bg-shape-image-dark.png" />
      </div>
    </div>

    <div class="d-flex col-12 col-xl-4 align-items-center authentication-bg p-sm-12 p-6">
      <div class="w-px-400 mx-auto mt-12 pt-5">
        <h4 class="mb-1">Sistem Web GIS MBPJ</h4>
        <p class="mb-6">Log masuk untuk memantau insiden sinkhole dan cerun.</p>

        @if ($errors->any())
        <div class="alert alert-danger" role="alert">
          {{ $errors->first() }}
        </div>
        @endif

        <form class="mb-6" action="{{ route('login') }}" method="POST">
          @csrf
          <div class="mb-6 form-control-validation">
            <label for="email" class="form-label">E-mel</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required
              autofocus autocomplete="username" />
          </div>
          <div class="mb-6 form-password-toggle form-control-validation">
            <label class="form-label" for="password">Kata laluan</label>
            <div class="input-group input-group-merge">
              <input type="password" id="password" class="form-control" name="password" required
                autocomplete="current-password" />
              <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
            </div>
          </div>
          <div class="my-8">
            <div class="form-check mb-0 ms-2">
              <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1" />
              <label class="form-check-label" for="remember"> Ingat saya </label>
            </div>
          </div>
          <button class="btn btn-primary d-grid w-100" type="submit">Log masuk</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

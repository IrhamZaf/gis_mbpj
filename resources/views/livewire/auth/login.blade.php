@php
$configData = Helper::appClasses();
$customizerHidden = 'customizer-hide';
@endphp

<div>
  <div class="authentication-wrapper authentication-cover">
    <a href="{{ url('/') }}" class="auth-cover-brand d-flex align-items-center gap-2">
      <span class="app-brand-logo demo">@include('_partials.macros', ["width" => 25, "height" => 22])</span>
      <span class="app-brand-text demo text-heading fw-bold">{{ config('variables.templateName') }}</span>
    </a>
    <div class="authentication-inner row m-0">
      <div class="d-none d-lg-flex col-lg-8 p-0">
        <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
          <img src="{{ asset('assets/img/illustrations/auth-login-illustration-' . $configData['style'] . '.png') }}"
            alt="auth-login" class="my-5 auth-illustration"
            data-app-light-img="illustrations/auth-login-illustration-light.png"
            data-app-dark-img="illustrations/auth-login-illustration-dark.png" />
          <img src="{{ asset('assets/img/illustrations/bg-shape-image-' . $configData['style'] . '.png') }}"
            alt="auth-bg" class="platform-bg"
            data-app-light-img="illustrations/bg-shape-image-light.png"
            data-app-dark-img="illustrations/bg-shape-image-dark.png" />
        </div>
      </div>
      <div class="d-flex col-12 col-lg-4 align-items-center authentication-bg p-sm-12 p-6">
        <div class="w-px-400 mx-auto mt-sm-12 mt-8">
          <h4 class="mb-1">Selamat datang ke {{ config('variables.templateName') }}! 👋</h4>
          <p class="mb-6">Sila log masuk ke akaun anda</p>

          <form wire:submit="login" class="mb-6">
            <div class="mb-6">
              <label for="login-email" class="form-label">E-mel</label>
              <input wire:model="email" type="email" class="form-control @error('email') is-invalid @enderror" id="login-email" placeholder="Masukkan e-mel anda" autofocus />
              @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
            <div class="mb-6 form-password-toggle">
              <label class="form-label" for="login-password">Kata Laluan</label>
              <div class="input-group input-group-merge">
                <input wire:model="password" type="password" id="login-password" class="form-control @error('password') is-invalid @enderror" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
                @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
              </div>
            </div>
            <div class="my-8">
              <div class="form-check mb-0 ms-2">
                <input wire:model="remember" class="form-check-input" type="checkbox" id="remember-me" />
                <label class="form-check-label" for="remember-me">Ingat Saya</label>
              </div>
            </div>
            <button class="btn btn-primary d-grid w-100" type="submit">
              <span wire:loading.remove>Log Masuk</span>
              <span wire:loading>Sila tunggu...</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

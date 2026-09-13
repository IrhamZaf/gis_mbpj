@php
$configData = Helper::appClasses();
$customizerHidden = 'customizer-hide';
@endphp

<div>
  <div class="authentication-wrapper authentication-cover">
    <div class="position-absolute top-0 end-0 p-4" style="z-index:10;">
      <div class="dropdown">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
          <i class="ti tabler-language me-1"></i>{{ app()->getLocale() === 'ms' ? __('app.malay') : __('app.english') }}
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}" href="{{ route('lang.switch', 'en') }}">{{ __('app.english') }}</a></li>
          <li><a class="dropdown-item {{ app()->getLocale() === 'ms' ? 'active' : '' }}" href="{{ route('lang.switch', 'ms') }}">{{ __('app.malay') }}</a></li>
        </ul>
      </div>
    </div>
    <a href="{{ url('/') }}" class="auth-cover-brand d-flex align-items-center gap-2">
      <span class="app-brand-logo demo">@include('_partials.macros', ["width" => 72, "height" => 72])</span>
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
          <div class="text-center mb-5">
            <img src="{{ asset('assets/img/branding/mbsj-logo.png') }}"
              alt="MBSJ"
              width="96"
              height="96"
              class="mb-3"
              style="object-fit:contain;display:inline-block;background:#fff;border-radius:50%;padding:6px;" />
          </div>
          <h4 class="mb-1">{{ __('app.welcome', ['app' => config('variables.templateName')]) }}</h4>
          <p class="mb-6">{{ __('app.login_subtitle') }}</p>

          <form wire:submit="login" class="mb-6">
            <div class="mb-6">
              <label for="login-email" class="form-label">{{ __('app.email') }}</label>
              <input wire:model="email" type="email" class="form-control @error('email') is-invalid @enderror" id="login-email" placeholder="{{ __('app.email') }}" autofocus />
              @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
            <div class="mb-6 form-password-toggle">
              <label class="form-label" for="login-password">{{ __('app.password') }}</label>
              <div class="input-group input-group-merge">
                <input wire:model="password" type="password" id="login-password" class="form-control @error('password') is-invalid @enderror" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
                @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
              </div>
            </div>
            <div class="my-8">
              <div class="form-check mb-0 ms-2">
                <input wire:model="remember" class="form-check-input" type="checkbox" id="remember-me" />
                <label class="form-check-label" for="remember-me">{{ __('app.remember_me') }}</label>
              </div>
            </div>
            <button class="btn btn-primary d-grid w-100" type="submit">
              <span wire:loading.remove>{{ __('app.sign_in') }}</span>
              <span wire:loading>{{ __('app.please_wait') }}</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

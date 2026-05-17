{{-- component/footer.blade.php — PLASBid-style footer using Vuexy styling --}}
@php
  $containerFooter =
    isset($configData['contentLayout']) && $configData['contentLayout'] === 'compact'
      ? 'container-xxl'
      : 'container-fluid';
@endphp

<!-- Footer -->
<footer class="content-footer footer bg-footer-theme">
    <div class="{{ $containerFooter }}">
        <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
            <div class="text-body">
                &copy;
                <script>document.write(new Date().getFullYear())</script>
                {{ config('variables.templateName') }}. Hak cipta terpelihara.
            </div>
            <div class="d-none d-lg-inline-block">
                <span class="text-body-secondary">Dikuasakan oleh
                    <a href="{{ config('variables.creatorUrl') }}" target="_blank" class="footer-link">{{ config('variables.creatorName') }}</a>
                </span>
            </div>
        </div>
    </div>
</footer>
<!-- / Footer -->

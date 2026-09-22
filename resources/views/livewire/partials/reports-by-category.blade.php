{{-- Shared "Reports by Category" progress list --}}
@php
  $reportsByCategory = collect($reportsByCategory ?? []);
  $catTotal = max(1, (int) $reportsByCategory->sum('reports_count'));
  $showEmpty = $reportsByCategory->sum('reports_count') === 0;
@endphp

<div class="card border-0 shadow-sm h-100">
  <div class="card-header d-flex justify-content-between align-items-center border-bottom">
    <h6 class="mb-0 fw-semibold">
      <i class="ti tabler-category me-2 text-success"></i>{{ $title ?? __('app.reports_by_category') }}
    </h6>
    @isset($headerAction)
      {!! $headerAction !!}
    @endisset
  </div>
  <div class="card-body">
    @if ($showEmpty)
      <div class="text-center text-muted py-5">
        <i class="ti tabler-folder-off icon-40px d-block mb-2 text-muted"></i>
        <p class="mb-0">{{ __('app.no_chart_data') }}</p>
      </div>
    @else
      @foreach ($reportsByCategory as $cat)
        @php
          $count = (int) ($cat['reports_count'] ?? $cat->reports_count ?? 0);
          $name = $cat['name'] ?? $cat->name ?? '—';
          $color = $cat['color'] ?? '#6c757d';
          $pct = (int) round(($count / $catTotal) * 100);
        @endphp
        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <div class="d-flex align-items-center gap-2">
              <span style="width:10px;height:10px;border-radius:50%;background:{{ $color }};display:inline-block;"></span>
              <span class="fw-medium small">{{ $name }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="fw-semibold small">{{ $count }}</span>
              <span class="text-muted small" style="min-width:36px;text-align:right;">{{ $pct }}%</span>
            </div>
          </div>
          <div class="progress" style="height:7px;border-radius:4px;">
            <div class="progress-bar" role="progressbar"
              style="width:{{ max($count ? 4 : 0, $pct) }}%;border-radius:4px;background:{{ $color }};"
              aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
          </div>
        </div>
      @endforeach
    @endif
  </div>
</div>

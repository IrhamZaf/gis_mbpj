{{-- Shared all-units overview for role main dashboards --}}
@php
  $unitCards = $unitCards ?? [];
  $grandTotal = $grandTotal ?? collect($unitCards)->sum('total');
@endphp

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
  <div>
    <h6 class="mb-0 fw-semibold">{{ __('app.units_overview') }}</h6>
    <div class="small text-muted">{{ __('app.units_overview_hint') }}</div>
  </div>
  <span class="badge bg-label-secondary">{{ number_format($grandTotal) }} {{ __('app.total_reports') }}</span>
</div>

<div class="row g-4 mb-4">
  @foreach ($unitCards as $card)
    @php
      $u = $card['unit'];
      $t = $card['theme'];
    @endphp
    <div class="col-md-6 col-xl-3">
      <div class="card border-0 shadow-sm h-100" style="border-top:4px solid {{ $t['color'] }} !important;">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="d-flex align-items-center gap-2">
              <span class="rounded-circle d-inline-flex align-items-center justify-content-center"
                style="width:40px;height:40px;background:{{ $t['soft'] }};color:{{ $t['color'] }};">
                <i class="ti {{ $card['icon'] }}"></i>
              </span>
              <div>
                <div class="fw-semibold">{{ $u->name }}</div>
                <div class="small text-muted">{{ $u->code }}</div>
              </div>
            </div>
            @if ($card['isOwn'] ?? false)
              <span class="badge text-white" style="background:{{ $t['color'] }};">{{ __('app.my_unit') }}</span>
            @elseif ($card['isReadOnly'] ?? false)
              <span class="badge bg-label-secondary"><i class="ti tabler-lock me-1"></i>{{ __('app.read_only_badge') }}</span>
            @endif
          </div>

          <div class="fw-bold fs-3 lh-1 mb-1" style="color:{{ $t['color'] }};">{{ number_format($card['total']) }}</div>
          <div class="small text-muted mb-3">{{ __('app.total_reports') }}</div>

          <div class="d-flex flex-column gap-2 mb-3">
            <a href="{{ $card['sinkholeUrl'] }}" class="d-flex justify-content-between small text-decoration-none text-body">
              <span><i class="ti tabler-alert-triangle me-1 text-info"></i>{{ __('app.sinkhole') }}</span>
              <strong>{{ $card['sinkhole'] }}</strong>
            </a>
            <a href="{{ $card['cerunUrl'] }}" class="d-flex justify-content-between small text-decoration-none text-body">
              <span><i class="ti tabler-mountain me-1 text-danger"></i>{{ __('app.cerun') }}</span>
              <strong>{{ $card['cerun'] }}</strong>
            </a>
            <a href="{{ $card['boreholeUrl'] }}" class="d-flex justify-content-between small text-decoration-none text-body">
              <span><i class="ti tabler-layers-intersect me-1 text-secondary"></i>{{ __('app.borehole') }}</span>
              <strong>{{ $card['borehole'] }}</strong>
            </a>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <div class="rounded p-2 text-center" style="background:rgba(255,193,7,.12);">
                <div class="fw-bold">{{ $card['pending'] }}</div>
                <div class="small text-muted" style="font-size:11px;">{{ __('app.pending') }}</div>
              </div>
            </div>
            <div class="col-6">
              <div class="rounded p-2 text-center" style="background:rgba(25,135,84,.12);">
                <div class="fw-bold">{{ $card['completed'] }}</div>
                <div class="small text-muted" style="font-size:11px;">{{ __('app.completed') }}</div>
              </div>
            </div>
          </div>

          <a href="{{ $card['dashboardUrl'] }}" class="btn btn-sm w-100"
            style="background:{{ $t['color'] }};border-color:{{ $t['color'] }};color:#fff;">
            <i class="ti tabler-smart-home me-1"></i>{{ __('app.open_dashboard') }}
          </a>
        </div>
      </div>
    </div>
  @endforeach
</div>

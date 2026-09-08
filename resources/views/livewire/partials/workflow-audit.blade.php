{{-- Immutable audit trail --}}
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header border-bottom">
    <h6 class="mb-0"><i class="ti tabler-history me-2"></i>Audit Trail</h6>
  </div>
  <div class="card-body p-0">
    <ul class="list-group list-group-flush">
      @forelse ($report->workflowHistories as $h)
        <li class="list-group-item">
          <div class="d-flex justify-content-between gap-2">
            <div>
              <div class="fw-medium small">{{ $h->user->name ?? 'Sistem' }}
                <span class="badge bg-label-secondary ms-1">{{ $h->role }}</span>
              </div>
              <div class="small text-body">{{ $h->remarks ?? $h->action }}</div>
              @if ($h->from_status || $h->to_status)
                <div class="small text-muted">{{ $h->from_status ?? '—' }} → {{ $h->to_status ?? '—' }}</div>
              @endif
            </div>
            <div class="text-end small text-muted text-nowrap">
              {{ $h->created_at?->format('d/m/Y H:i') }}
              @if ($h->ip_address)
                <div>{{ $h->ip_address }}</div>
              @endif
            </div>
          </div>
        </li>
      @empty
        <li class="list-group-item text-muted text-center py-4">Tiada rekod audit lagi.</li>
      @endforelse
    </ul>
  </div>
</div>

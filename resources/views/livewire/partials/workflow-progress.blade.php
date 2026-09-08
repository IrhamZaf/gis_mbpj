{{-- Workflow progress stepper --}}
@php $steps = $report->workflowSteps(); @endphp
@if ($report->workflow_status)
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <div class="d-flex flex-wrap justify-content-between gap-2">
      @foreach ($steps as $i => $step)
        <div class="text-center flex-fill" style="min-width:100px;">
          <div class="mx-auto mb-1 rounded-circle d-flex align-items-center justify-content-center
            {{ $step['state'] === 'done' ? 'bg-success text-white' : ($step['state'] === 'current' ? 'bg-primary text-white' : ($step['state'] === 'returned' ? 'bg-danger text-white' : 'bg-label-secondary')) }}"
            style="width:28px;height:28px;font-size:12px;font-weight:600;">
            {{ $i + 1 }}
          </div>
          <div class="small {{ in_array($step['state'], ['current','returned'], true) ? 'fw-semibold' : 'text-muted' }}" style="font-size:11px;">
            {{ $step['label'] }}
          </div>
        </div>
        @if (!$loop->last)
          <div class="d-none d-md-flex align-items-center text-muted px-1">›</div>
        @endif
      @endforeach
    </div>
    @if (in_array($report->workflow_status, ['engineer_returned', 'director_rejected'], true))
      <div class="alert alert-danger mt-3 mb-0 py-2 small">
        Status semasa: <strong>{{ $report->workflow_status_label }}</strong>
        @if ($report->review_note)
          — {{ $report->review_note }}
        @endif
      </div>
    @endif
  </div>
</div>
@endif

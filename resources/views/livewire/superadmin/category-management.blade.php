<div>
  @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible mb-4">
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      {{ session('message') }}
    </div>
  @endif

  @if (session()->has('error'))
    <div class="alert alert-danger alert-dismissible mb-4">
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      {{ session('error') }}
    </div>
  @endif

  <div class="card border-0 shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between border-bottom">
      <h5 class="mb-0">{{ __('app.category_management') }}</h5>
      <button wire:click="openCreate" class="btn btn-primary btn-sm">
        <i class="ti tabler-plus me-1"></i>{{ __('app.add_category') }}
      </button>
    </div>
    <div class="card-body">
      <div class="row mb-4 g-3">
        <div class="col-md-5">
          <input
            wire:model.live.debounce.300ms="search"
            type="text"
            class="form-control"
            placeholder="{{ __('app.search_categories') }}"
          />
        </div>
        <div class="col-md-4">
          <select wire:model.live="filterUnit" class="form-select">
            <option value="">{{ __('app.all_units') }}</option>
            <option value="global">{{ __('app.global_category') }}</option>
            @foreach ($units as $unit)
              <option value="{{ $unit->id }}">{{ $unit->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <select wire:model.live="filterStatus" class="form-select">
            <option value="">{{ __('app.all_statuses') }}</option>
            <option value="active">{{ __('app.active') }}</option>
            <option value="inactive">{{ __('app.inactive') }}</option>
          </select>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>{{ __('app.category_name') }}</th>
              <th>{{ __('app.code') }}</th>
              <th>{{ __('app.unit') }}</th>
              <th>{{ __('app.status') }}</th>
              <th>{{ __('app.reports_count') }}</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse ($categories as $cat)
              <tr>
                <td>
                  <div class="fw-medium">{{ $cat->display_name }}</div>
                  @if ($cat->description)
                    <div class="small text-muted">{{ $cat->description }}</div>
                  @endif
                </td>
                <td><code>{{ $cat->code ?? '—' }}</code></td>
                <td>{{ $cat->unit->name ?? __('app.global') }}</td>
                <td>
                  @if ($cat->isActive())
                    <span class="badge bg-label-success">{{ __('app.active') }}</span>
                  @else
                    <span class="badge bg-label-secondary">{{ __('app.inactive') }}</span>
                  @endif
                </td>
                <td><span class="badge bg-label-primary">{{ $cat->reports_count }}</span></td>
                <td class="text-end text-nowrap">
                  <button
                    wire:click="openEdit({{ $cat->id }})"
                    class="btn btn-sm btn-icon btn-text-secondary"
                    title="{{ __('app.edit') }}"
                  >
                    <i class="ti tabler-pencil"></i>
                  </button>
                  <button
                    wire:click="delete({{ $cat->id }})"
                    wire:confirm="{{ __('app.confirm_delete_category') }}"
                    class="btn btn-sm btn-icon btn-text-danger"
                    title="{{ __('app.delete') }}"
                    @disabled($cat->reports_count > 0)
                  >
                    <i class="ti tabler-trash"></i>
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center py-4">{{ __('app.no_categories') }}</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
        <div class="small text-muted">
          {{ __('app.showing', [
            'from' => $categories->firstItem() ?? 0,
            'to' => $categories->lastItem() ?? 0,
            'total' => $categories->total(),
          ]) }}
        </div>
        <div>
          {{ $categories->links() }}
        </div>
      </div>
    </div>
  </div>

  @if ($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ $editingId ? __('app.edit_category') : __('app.add_category') }}
            </h5>
            <button wire:click="$set('showModal', false)" type="button" class="btn-close"></button>
          </div>
          <form wire:submit="save">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">{{ __('app.category_name') }}</label>
                <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" />
                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
              </div>
              <div class="mb-3">
                <label class="form-label">{{ __('app.code') }}</label>
                <input wire:model="code" type="text" class="form-control @error('code') is-invalid @enderror" placeholder="SINKHOLE" />
                @error('code')<span class="invalid-feedback">{{ $message }}</span>@enderror
              </div>
              <div class="mb-3">
                <label class="form-label">{{ __('app.unit') }}</label>
                <select wire:model="unit_id" class="form-select @error('unit_id') is-invalid @enderror">
                  <option value="">{{ __('app.global_category') }}</option>
                  @foreach ($units as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->code }})</option>
                  @endforeach
                </select>
                @error('unit_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
              </div>
              <div class="mb-3">
                <label class="form-label">{{ __('app.status') }}</label>
                <select wire:model="status" class="form-select">
                  <option value="active">{{ __('app.active') }}</option>
                  <option value="inactive">{{ __('app.inactive') }}</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">{{ __('app.description') }}</label>
                <textarea wire:model="description" class="form-control" rows="3"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" wire:click="$set('showModal', false)" class="btn btn-secondary">{{ __('app.cancel') }}</button>
              <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif
</div>

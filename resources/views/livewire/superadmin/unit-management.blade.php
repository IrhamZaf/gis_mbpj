<div>
  @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible mb-4">
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('message') }}
    </div>
  @endif
  @if (session()->has('error'))
    <div class="alert alert-danger alert-dismissible mb-4">
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('error') }}
    </div>
  @endif

  <div class="card border-0 shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between border-bottom">
      <div>
        <h5 class="mb-0">Pengurusan Unit</h5>
        <small class="text-muted">Unit / jabatan Engineering MBSJ</small>
      </div>
      <button wire:click="openCreate" class="btn btn-primary btn-sm">
        <i class="ti tabler-plus me-1"></i>Tambah Unit
      </button>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Unit</th>
              <th>Kod</th>
              <th>Parent</th>
              <th>Status</th>
              <th class="text-end">Pengguna</th>
              <th class="text-end">Laporan</th>
              <th class="text-end">Tindakan</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($units as $unit)
              <tr>
                <td>
                  <div class="fw-medium">{{ $unit->name }}</div>
                  @if ($unit->description)
                    <div class="small text-muted text-truncate" style="max-width:280px;">{{ $unit->description }}</div>
                  @endif
                </td>
                <td><code class="bg-light px-2 py-1 rounded">{{ $unit->code }}</code></td>
                <td>{{ $unit->parent->name ?? '—' }}</td>
                <td>{!! $unit->status_badge !!}</td>
                <td class="text-end"><span class="badge bg-label-primary">{{ $unit->users_count }}</span></td>
                <td class="text-end"><span class="badge bg-label-info">{{ $unit->reports_count }}</span></td>
                <td class="text-end text-nowrap">
                  <button wire:click="toggleStatus({{ $unit->id }})" class="btn btn-sm btn-icon btn-text-secondary" title="Tukar status">
                    <i class="ti tabler-toggle-{{ $unit->status === 'active' ? 'right' : 'left' }}"></i>
                  </button>
                  <button wire:click="openEdit({{ $unit->id }})" class="btn btn-sm btn-icon btn-text-secondary" title="Edit">
                    <i class="ti tabler-pencil"></i>
                  </button>
                  <button wire:click="delete({{ $unit->id }})" wire:confirm="Padam unit ini?" class="btn btn-sm btn-icon btn-text-danger" title="Padam">
                    <i class="ti tabler-trash"></i>
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center text-muted py-5">Tiada unit lagi.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  @if ($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $editingId ? 'Edit' : 'Tambah' }} Unit</h5>
            <button wire:click="$set('showModal', false)" type="button" class="btn-close"></button>
          </div>
          <form wire:submit="save">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Nama Unit</label>
                <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" />
                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
              </div>
              <div class="mb-3">
                <label class="form-label">Kod</label>
                <input wire:model="code" type="text" class="form-control @error('code') is-invalid @enderror" placeholder="cth: JLN" />
                @error('code')<span class="invalid-feedback">{{ $message }}</span>@enderror
              </div>
              <div class="mb-3">
                <label class="form-label">Parent Unit (pilihan)</label>
                <select wire:model="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                  <option value="">— Tiada —</option>
                  @foreach ($parentUnits as $p)
                    @if (!$editingId || $p->id !== $editingId)
                      <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endif
                  @endforeach
                </select>
                @error('parent_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
              </div>
              <div class="mb-3">
                <label class="form-label">Status</label>
                <select wire:model="status" class="form-select">
                  <option value="active">Aktif</option>
                  <option value="inactive">Nyahaktif</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Susunan</label>
                <input wire:model="sort_order" type="number" min="0" class="form-control" />
              </div>
              <div class="mb-3">
                <label class="form-label">Keterangan</label>
                <textarea wire:model="description" class="form-control" rows="3"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" wire:click="$set('showModal', false)" class="btn btn-secondary">Batal</button>
              <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif
</div>

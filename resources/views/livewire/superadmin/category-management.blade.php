<div>
  @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible mb-4"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('message') }}</div>
  @endif

  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="mb-0">Kategori Laporan</h5>
      <button wire:click="openCreate" class="btn btn-primary btn-sm"><i class="ti tabler-plus me-1"></i>Tambah Kategori</button>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>Nama</th>
              <th>Kod</th>
              <th>Unit</th>
              <th>Status</th>
              <th>Bilangan</th>
              <th>Tindakan</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($categories as $cat)
            <tr>
              <td>{{ $cat->id }}</td>
              <td>
                <strong>{{ $cat->name }}</strong>
                <div class="small text-muted">{{ $cat->description ?? '' }}</div>
              </td>
              <td><code>{{ $cat->code ?? '-' }}</code></td>
              <td>{{ $cat->unit->name ?? 'Global' }}</td>
              <td>
                @if (($cat->status ?? 'active') === 'active')
                  <span class="badge bg-label-success">Aktif</span>
                @else
                  <span class="badge bg-label-secondary">Nyahaktif</span>
                @endif
              </td>
              <td><span class="badge bg-label-primary">{{ $cat->reports_count }}</span></td>
              <td>
                <button wire:click="openEdit({{ $cat->id }})" class="btn btn-sm btn-icon btn-text-secondary"><i class="ti tabler-pencil"></i></button>
                <button wire:click="delete({{ $cat->id }})" wire:confirm="Padam kategori ini?" class="btn btn-sm btn-icon btn-text-danger"><i class="ti tabler-trash"></i></button>
              </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-4">Tiada kategori.</td></tr>
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
        <div class="modal-header"><h5 class="modal-title">{{ $editingId ? 'Edit' : 'Tambah' }} Kategori</h5><button wire:click="$set('showModal', false)" type="button" class="btn-close"></button></div>
        <form wire:submit="save">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Nama Kategori</label>
              <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" />
              @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label">Kod</label>
              <input wire:model="code" type="text" class="form-control" placeholder="SINKHOLE" />
            </div>
            <div class="mb-3">
              <label class="form-label">Unit</label>
              <select wire:model="unit_id" class="form-select">
                <option value="">Global (tiada unit)</option>
                @foreach ($units as $u)
                  <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->code }})</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Status</label>
              <select wire:model="status" class="form-select">
                <option value="active">Aktif</option>
                <option value="inactive">Nyahaktif</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Keterangan</label>
              <textarea wire:model="description" class="form-control" rows="3"></textarea>
            </div>
          </div>
          <div class="modal-footer"><button type="button" wire:click="$set('showModal', false)" class="btn btn-secondary">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
        </form>
      </div>
    </div>
  </div>
  @endif
</div>

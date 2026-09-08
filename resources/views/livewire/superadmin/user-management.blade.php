<div>
  @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible mb-4"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>{{ session('message') }}</div>
  @endif

  <div class="card border-0 shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between border-bottom">
      <h5 class="mb-0">Pengurusan Pengguna</h5>
      <button wire:click="openCreate" class="btn btn-primary btn-sm"><i class="ti tabler-plus me-1"></i>Tambah Pengguna</button>
    </div>
    <div class="card-body">
      <div class="row mb-4 g-3">
        <div class="col-md-5"><input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Cari nama atau e-mel..." /></div>
        <div class="col-md-3">
          <select wire:model.live="filterRole" class="form-select">
            <option value="">Semua Role</option>
            <option value="superadmin">Superadmin</option>
            <option value="surveyor">Surveyor</option>
            <option value="engineer">Engineer</option>
            <option value="ta">TA</option>
            <option value="director">Pengarah</option>
          </select>
        </div>
        <div class="col-md-4">
          <select wire:model.live="filterUnit" class="form-select">
            <option value="">Semua Unit</option>
            @foreach ($units as $unit)
              <option value="{{ $unit->id }}">{{ $unit->name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Nama</th>
              <th>E-mel</th>
              <th>Role</th>
              <th>Unit</th>
              <th>Status</th>
              <th>Daftar</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse ($users as $u)
            <tr>
              <td>
                <div class="fw-medium">{{ $u->name }}</div>
                <div class="small text-muted">{{ $u->phone ?? '—' }}</div>
              </td>
              <td>{{ $u->email }}</td>
              <td><span class="badge bg-label-{{ $u->role === 'superadmin' ? 'danger' : ($u->role === 'surveyor' ? 'primary' : 'info') }}">{{ $u->role_label }}</span></td>
              <td>{{ $u->unit->name ?? '—' }}</td>
              <td>{!! $u->status_badge !!}</td>
              <td class="small text-muted text-nowrap">{{ $u->created_at->format('d/m/Y') }}</td>
              <td class="text-end text-nowrap">
                <button wire:click="openEdit({{ $u->id }})" class="btn btn-sm btn-icon btn-text-secondary"><i class="ti tabler-pencil"></i></button>
                <button wire:click="delete({{ $u->id }})" wire:confirm="Adakah anda pasti?" class="btn btn-sm btn-icon btn-text-danger"><i class="ti tabler-trash"></i></button>
              </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-4">Tiada pengguna dijumpai.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-3">{{ $users->links() }}</div>
    </div>
  </div>

  @if ($showModal)
  <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">{{ $editingId ? 'Edit' : 'Tambah' }} Pengguna</h5><button wire:click="$set('showModal', false)" type="button" class="btn-close"></button></div>
        <form wire:submit="save">
          <div class="modal-body">
            <div class="mb-3"><label class="form-label">Nama</label><input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" />@error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
            <div class="mb-3"><label class="form-label">E-mel</label><input wire:model="email" type="email" class="form-control @error('email') is-invalid @enderror" />@error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
            <div class="mb-3"><label class="form-label">Telefon</label><input wire:model="phone" type="text" class="form-control" /></div>
            <div class="mb-3"><label class="form-label">Role</label>
              <select wire:model.live="role" class="form-select">
                <option value="superadmin">Superadmin</option>
                <option value="surveyor">Surveyor</option>
                <option value="engineer">Engineer</option>
                <option value="ta">TA</option>
                <option value="director">Pengarah</option>
              </select>
            </div>
            @if (! in_array($role, ['superadmin', 'director'], true))
              <div class="mb-3">
                <label class="form-label">Unit <span class="text-danger">*</span></label>
                <select wire:model="unit_id" class="form-select @error('unit_id') is-invalid @enderror">
                  <option value="">— Pilih unit —</option>
                  @foreach ($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                  @endforeach
                </select>
                @error('unit_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
              </div>
            @endif
            <div class="mb-3"><label class="form-label">Status</label>
              <select wire:model="status" class="form-select">
                <option value="active">Aktif</option>
                <option value="inactive">Nyahaktif</option>
              </select>
            </div>
            <div class="mb-3"><label class="form-label">Kata Laluan {{ $editingId ? '(kosongkan jika tidak ubah)' : '' }}</label><input wire:model="password" type="password" class="form-control @error('password') is-invalid @enderror" />@error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
          </div>
          <div class="modal-footer"><button type="button" wire:click="$set('showModal', false)" class="btn btn-secondary">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
        </form>
      </div>
    </div>
  </div>
  @endif
</div>

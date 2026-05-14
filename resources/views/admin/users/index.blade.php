@extends('layouts/layoutMaster')

@section('title', 'Pengguna')

@section('content')
<div class="d-flex justify-content-between mb-4">
  <h4 class="mb-0">Pengguna sistem</h4>
  <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Tambah</a>
</div>
<div class="card">
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Nama</th>
          <th>E-mel</th>
          <th>Peranan</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($users as $u)
        <tr>
          <td>{{ $u->name }}</td>
          <td>{{ $u->email }}</td>
          <td>{{ $u->role }}</td>
          <td class="text-end">
            <a class="btn btn-sm btn-text-secondary" href="{{ route('admin.users.edit', $u) }}">Edit</a>
            @if($u->id !== auth()->id())
            <form action="{{ route('admin.users.destroy', $u) }}" method="POST" class="d-inline" onsubmit="return confirm('Padam pengguna?');">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-sm btn-text-danger">Padam</button>
            </form>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="card-body">{{ $users->links() }}</div>
</div>
@endsection

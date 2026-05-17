<?php

namespace App\Livewire\Superadmin;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.master')]
#[Title('Pengurusan Pengguna')]
class UserManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterRole = '';

    // Form fields
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $role = 'engineer';
    public string $password = '';

    protected function rules(): array
    {
        $emailRule = $this->editingId
            ? "required|email|unique:users,email,{$this->editingId}"
            : 'required|email|unique:users,email';

        return [
            'name'  => 'required|min:3',
            'email' => $emailRule,
            'phone' => 'nullable|string|max:20',
            'role'  => 'required|in:superadmin,surveyor,engineer',
            'password' => $this->editingId ? 'nullable|min:6' : 'required|min:6',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterRole()
    {
        $this->resetPage();
    }

    public function openCreate()
    {
        $this->reset(['editingId', 'name', 'email', 'phone', 'role', 'password']);
        $this->role = 'engineer';
        $this->showModal = true;
    }

    public function openEdit(int $id)
    {
        $user = User::findOrFail($id);
        $this->editingId = $user->id;
        $this->name      = $user->name;
        $this->email     = $user->email;
        $this->phone     = $user->phone ?? '';
        $this->role      = $user->role;
        $this->password  = '';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name'  => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'role'  => $this->role,
        ];

        if ($this->password) {
            $data['password'] = bcrypt($this->password);
        }

        if ($this->editingId) {
            User::findOrFail($this->editingId)->update($data);
            session()->flash('message', 'Pengguna berjaya dikemaskini.');
        } else {
            User::create($data);
            session()->flash('message', 'Pengguna berjaya dicipta.');
        }

        $this->showModal = false;
        $this->reset(['editingId', 'name', 'email', 'phone', 'role', 'password']);
    }

    public function delete(int $id)
    {
        User::findOrFail($id)->delete();
        session()->flash('message', 'Pengguna berjaya dipadam.');
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->when($this->filterRole, fn($q) => $q->where('role', $this->filterRole))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.superadmin.user-management', ['users' => $users]);
    }
}

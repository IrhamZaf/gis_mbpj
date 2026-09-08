<?php

namespace App\Livewire\Superadmin;

use App\Models\Unit;
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
    public string $filterUnit = '';

    public bool $showModal = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $role = 'engineer';
    public string $unit_id = '';
    public string $status = 'active';
    public string $password = '';

    protected function rules(): array
    {
        $emailRule = $this->editingId
            ? "required|email|unique:users,email,{$this->editingId}"
            : 'required|email|unique:users,email';

        $unitRule = in_array($this->role, ['surveyor', 'engineer', 'ta'], true)
            ? 'required|exists:units,id'
            : 'nullable|exists:units,id';

        return [
            'name'     => 'required|min:3',
            'email'    => $emailRule,
            'phone'    => 'nullable|string|max:20',
            'role'     => 'required|in:superadmin,surveyor,engineer,ta,director',
            'unit_id'  => $unitRule,
            'status'   => 'required|in:active,inactive',
            'password' => $this->editingId ? 'nullable|min:6' : 'required|min:6',
        ];
    }

    protected array $messages = [
        'unit_id.required' => 'Surveyor, Engineer dan TA mesti ditetapkan kepada satu unit.',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterRole(): void
    {
        $this->resetPage();
    }

    public function updatingFilterUnit(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'email', 'phone', 'unit_id', 'password']);
        $this->role = 'engineer';
        $this->status = 'active';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingId = $user->id;
        $this->name      = $user->name;
        $this->email     = $user->email;
        $this->phone     = $user->phone ?? '';
        $this->role      = $user->role;
        $this->unit_id   = $user->unit_id ? (string) $user->unit_id : '';
        $this->status    = $user->status ?? 'active';
        $this->password  = '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'    => $this->name,
            'email'   => $this->email,
            'phone'   => $this->phone ?: null,
            'role'    => $this->role,
            'status'  => $this->status,
            'unit_id' => in_array($this->role, ['superadmin', 'director'], true)
                ? null
                : ($this->unit_id !== '' ? (int) $this->unit_id : null),
        ];

        if ($this->password) {
            $data['password'] = $this->password;
        }

        if ($this->editingId) {
            User::findOrFail($this->editingId)->update($data);
            session()->flash('message', 'Pengguna berjaya dikemaskini.');
        } else {
            User::create($data);
            session()->flash('message', 'Pengguna berjaya dicipta.');
        }

        $this->showModal = false;
        $this->reset(['editingId', 'name', 'email', 'phone', 'role', 'unit_id', 'status', 'password']);
    }

    public function delete(int $id): void
    {
        User::findOrFail($id)->delete();
        session()->flash('message', 'Pengguna berjaya dipadam.');
    }

    public function render()
    {
        $users = User::with('unit')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->when($this->filterRole, fn ($q) => $q->where('role', $this->filterRole))
            ->when($this->filterUnit, fn ($q) => $q->where('unit_id', $this->filterUnit))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.superadmin.user-management', [
            'users' => $users,
            'units' => Unit::active()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }
}

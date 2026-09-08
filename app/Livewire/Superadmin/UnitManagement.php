<?php

namespace App\Livewire\Superadmin;

use App\Models\Unit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.master')]
#[Title('Pengurusan Unit')]
class UnitManagement extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $code = '';
    public string $description = '';
    public string $status = 'active';
    public ?string $parent_id = '';
    public int $sort_order = 0;

    protected function rules(): array
    {
        $codeRule = $this->editingId
            ? "required|alpha_dash|max:20|unique:units,code,{$this->editingId}"
            : 'required|alpha_dash|max:20|unique:units,code';

        return [
            'name'        => 'required|min:2|max:100',
            'code'        => $codeRule,
            'description' => 'nullable|string|max:1000',
            'status'      => 'required|in:active,inactive',
            'parent_id'   => 'nullable|exists:units,id',
            'sort_order'  => 'nullable|integer|min:0',
        ];
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'code', 'description', 'parent_id', 'sort_order']);
        $this->status = 'active';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $unit = Unit::findOrFail($id);
        $this->editingId   = $unit->id;
        $this->name        = $unit->name;
        $this->code        = $unit->code;
        $this->description = $unit->description ?? '';
        $this->status      = $unit->status;
        $this->parent_id   = $unit->parent_id ? (string) $unit->parent_id : '';
        $this->sort_order  = (int) $unit->sort_order;
        $this->showModal   = true;
    }

    public function save(): void
    {
        $this->validate();

        $parentId = $this->parent_id !== '' ? (int) $this->parent_id : null;
        if ($this->editingId && $parentId === $this->editingId) {
            $this->addError('parent_id', 'Unit tidak boleh menjadi parent kepada dirinya sendiri.');

            return;
        }

        $data = [
            'name'        => $this->name,
            'code'        => strtoupper($this->code),
            'description' => $this->description ?: null,
            'status'      => $this->status,
            'parent_id'   => $parentId,
            'sort_order'  => $this->sort_order,
        ];

        if ($this->editingId) {
            Unit::findOrFail($this->editingId)->update($data);
            session()->flash('message', 'Unit berjaya dikemaskini.');
        } else {
            Unit::create($data);
            session()->flash('message', 'Unit berjaya dicipta.');
        }

        $this->showModal = false;
    }

    public function toggleStatus(int $id): void
    {
        $unit = Unit::findOrFail($id);
        $unit->update([
            'status' => $unit->status === 'active' ? 'inactive' : 'active',
        ]);
        session()->flash('message', 'Status unit dikemaskini.');
    }

    public function delete(int $id): void
    {
        $unit = Unit::withCount(['users', 'reports'])->findOrFail($id);

        if ($unit->users_count > 0 || $unit->reports_count > 0) {
            session()->flash('error', 'Unit tidak boleh dipadam kerana masih mempunyai pengguna atau laporan.');

            return;
        }

        if ($unit->children()->exists()) {
            session()->flash('error', 'Unit tidak boleh dipadam kerana masih mempunyai sub-unit.');

            return;
        }

        $unit->delete();
        session()->flash('message', 'Unit berjaya dipadam.');
    }

    public function render()
    {
        return view('livewire.superadmin.unit-management', [
            'units'       => Unit::with('parent')
                ->withCount(['users', 'reports'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'parentUnits' => Unit::orderBy('name')->get(),
        ]);
    }
}

<?php

namespace App\Livewire\Superadmin;

use App\Models\ReportCategory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.master')]
#[Title('Kategori Laporan')]
class CategoryManagement extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $description = '';

    protected array $rules = [
        'name'        => 'required|min:2',
        'description' => 'nullable|string',
    ];

    public function openCreate()
    {
        $this->reset(['editingId', 'name', 'description']);
        $this->showModal = true;
    }

    public function openEdit(int $id)
    {
        $cat = ReportCategory::findOrFail($id);
        $this->editingId   = $cat->id;
        $this->name        = $cat->name;
        $this->description = $cat->description ?? '';
        $this->showModal   = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editingId) {
            ReportCategory::findOrFail($this->editingId)->update([
                'name'        => $this->name,
                'description' => $this->description ?: null,
            ]);
            session()->flash('message', 'Kategori berjaya dikemaskini.');
        } else {
            ReportCategory::create([
                'name'        => $this->name,
                'description' => $this->description ?: null,
            ]);
            session()->flash('message', 'Kategori berjaya dicipta.');
        }

        $this->showModal = false;
    }

    public function delete(int $id)
    {
        ReportCategory::findOrFail($id)->delete();
        session()->flash('message', 'Kategori berjaya dipadam.');
    }

    public function render()
    {
        return view('livewire.superadmin.category-management', [
            'categories' => ReportCategory::withCount('reports')->orderBy('name')->get(),
        ]);
    }
}

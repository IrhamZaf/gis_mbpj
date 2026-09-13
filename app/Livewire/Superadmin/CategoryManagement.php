<?php

namespace App\Livewire\Superadmin;

use App\Models\ReportCategory;
use App\Models\Unit;
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
    public string $code = '';
    public string $unit_id = '';
    public string $status = 'active';

    protected function rules(): array
    {
        return [
            'name' => 'required|min:2',
            'description' => 'nullable|string',
            'code' => 'nullable|string|max:50',
            'unit_id' => 'nullable|exists:units,id',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function openCreate()
    {
        $this->reset(['editingId', 'name', 'description', 'code', 'unit_id']);
        $this->status = 'active';
        $this->showModal = true;
    }

    public function openEdit(int $id)
    {
        $cat = ReportCategory::findOrFail($id);
        $this->editingId = $cat->id;
        $this->name = $cat->name;
        $this->description = $cat->description ?? '';
        $this->code = $cat->code ?? '';
        $this->unit_id = $cat->unit_id ? (string) $cat->unit_id : '';
        $this->status = $cat->status ?? 'active';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'code' => $this->code !== '' ? strtoupper($this->code) : null,
            'unit_id' => $this->unit_id !== '' ? (int) $this->unit_id : null,
            'status' => $this->status,
        ];

        if ($this->editingId) {
            ReportCategory::findOrFail($this->editingId)->update($data);
            session()->flash('message', 'Kategori berjaya dikemaskini.');
        } else {
            ReportCategory::create($data);
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
            'categories' => ReportCategory::with(['unit'])->withCount('reports')->orderBy('name')->get(),
            'units' => Unit::active()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }
}

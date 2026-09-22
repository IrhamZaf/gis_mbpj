<?php

namespace App\Livewire\Superadmin;

use App\Models\ReportCategory;
use App\Models\Unit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.master')]
#[Title('Kategori Laporan')]
class CategoryManagement extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';

    public string $filterUnit = '';

    public string $filterStatus = '';

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
            'name' => 'required|min:2|max:255',
            'description' => 'nullable|string|max:1000',
            'code' => 'nullable|string|max:50',
            'unit_id' => 'nullable|exists:units,id',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterUnit(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'description', 'code', 'unit_id']);
        $this->status = 'active';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
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

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description !== '' ? $this->description : null,
            'code' => $this->code !== '' ? strtoupper(trim($this->code)) : null,
            'unit_id' => $this->unit_id !== '' ? (int) $this->unit_id : null,
            'status' => $this->status,
        ];

        if ($this->editingId) {
            ReportCategory::findOrFail($this->editingId)->update($data);
            session()->flash('message', __('app.category_updated'));
        } else {
            ReportCategory::create($data);
            session()->flash('message', __('app.category_created'));
        }

        $this->showModal = false;
        $this->reset(['editingId', 'name', 'description', 'code', 'unit_id', 'status']);
        $this->status = 'active';
    }

    public function delete(int $id): void
    {
        $cat = ReportCategory::withCount('reports')->findOrFail($id);

        if ($cat->reports_count > 0) {
            session()->flash('error', __('app.category_has_reports'));

            return;
        }

        $cat->delete();
        session()->flash('message', __('app.category_deleted'));
    }

    public function render()
    {
        $categories = ReportCategory::query()
            ->with(['unit'])
            ->withCount('reports')
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('code', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterUnit === 'global', fn ($q) => $q->whereNull('unit_id'))
            ->when($this->filterUnit !== '' && $this->filterUnit !== 'global', fn ($q) => $q->where('unit_id', $this->filterUnit))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.superadmin.category-management', [
            'categories' => $categories,
            'units' => Unit::active()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }
}

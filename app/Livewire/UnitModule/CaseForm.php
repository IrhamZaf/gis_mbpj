<?php

namespace App\Livewire\UnitModule;

use App\Models\AttachmentType;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\Unit;
use App\Services\Attachments\TechnicalAttachmentService;
use App\Services\Workflow\ReportWorkflowService;
use App\Support\SurveyVendor;
use App\Support\UnitModule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.master')]
class CaseForm extends Component
{
    use WithFileUploads;

    public string $unitCode = 'SAL-CERUN';

    public string $categoryCode = '';

    public ?int $reportId = null;

    public string $title = '';

    public string $description = '';

    public string $location_name = '';

    public string $address = '';

    public string $vendor_name = '';

    public ?float $latitude = null;

    public ?float $longitude = null;

    public ?float $gps_accuracy = null;

    public ?array $gis_data = null;

    /** @var array<int, mixed> */
    public array $uploads = [];

    /** @var array<int, mixed> Free-form files (no typed category slots) */
    public array $looseFiles = [];

    public function mount(?Report $report = null, ?string $categoryCode = null, ?string $unitCode = null): void
    {
        $routeUnit = request()->route()?->parameter('unitCode')
            ?? request()->route()?->defaults['unitCode']
            ?? null;

        $this->unitCode = $unitCode ?: (is_string($routeUnit) && $routeUnit !== '' ? $routeUnit : 'SAL-CERUN');
        $categoryCode = $categoryCode ?: '';

        // Resolve unit from route name if still default and report not loaded
        foreach (UnitModule::UNIT_SLUGS as $slug => $code) {
            $routeName = request()->route()?->getName() ?? '';
            if (str_starts_with($routeName, $slug.'.')) {
                $this->unitCode = $code;
                break;
            }
        }

        // Edit route: /cases/{report}/edit — Livewire injects Report
        if ($report && $report->exists) {
            $this->authorize('update', $report);
            $report->loadMissing(['category', 'unit']);
            $this->reportId = $report->id;
            $this->title = $report->title;
            $this->description = $report->description ?? '';
            $this->location_name = $report->location_name ?? '';
            $this->address = $report->address ?? '';
            $this->vendor_name = $report->vendor_name ?: SurveyVendor::name();
            $this->latitude = $report->latitude ? (float) $report->latitude : null;
            $this->longitude = $report->longitude ? (float) $report->longitude : null;
            $this->gps_accuracy = $report->gps_accuracy ? (float) $report->gps_accuracy : null;
            $this->gis_data = $report->gis_data;
            $this->categoryCode = UnitModule::normalizeCategoryCode($report->category?->code) ?? ($categoryCode ?: '');
            if ($report->unit?->code) {
                $this->unitCode = $report->unit->code;
            }

            return;
        }

        // Create: consultant / superadmin may write any unit
        if (! $report || ! $report->exists) {
            $user = Auth::user();
            $targetUnitId = Unit::where('code', $this->unitCode)->value('id');
            if (! $user || (! $user->isSuperadmin() && ! $user->canWriteUnit($targetUnitId))) {
                abort(403, __('app.consultant_unit_denied'));
            }
        }

        $this->categoryCode = UnitModule::normalizeCategoryCode($categoryCode) ?? $categoryCode;
        $this->vendor_name = SurveyVendor::name();
    }

    #[On('report-coordinates-updated')]
    public function setCoordinates(float $latitude, float $longitude, ?string $label = null): void
    {
        $this->latitude = round($latitude, 7);
        $this->longitude = round($longitude, 7);
        if ($label && trim($this->location_name) === '') {
            $this->location_name = mb_substr(trim($label), 0, 255);
        }
    }

    #[On('report-gis-data-updated')]
    public function setGisData(?array $data = null): void
    {
        $this->gis_data = $data;
    }

    public function saveDraft(): void
    {
        $report = $this->persist(false);
        session()->flash('message', __('app.case_saved_draft'));
        $this->redirect(UnitModule::caseShowRoute($this->unitCode, $report), navigate: false);
    }

    public function saveAndSubmit(): void
    {
        $report = $this->persist(false);
        $this->uploadPendingFiles($report);
        $report->refresh();

        $isDraft = $report->status === 'draft' && $report->workflow_status === null;

        if (! $isDraft) {
            session()->flash('message', __('app.case_saved', ['message' => '']));
            $this->redirect(UnitModule::caseShowRoute($this->unitCode, $report), navigate: false);

            return;
        }

        try {
            app(ReportWorkflowService::class)->submitReport($report, Auth::user());
            session()->flash('message', __('app.case_submitted'));
        } catch (\Throwable $e) {
            session()->flash('message', __('app.case_saved', ['message' => $e->getMessage()]));
            $this->redirect(UnitModule::caseShowRoute($this->unitCode, $report), navigate: false);

            return;
        }

        $this->redirect(UnitModule::caseShowRoute($this->unitCode, $report->fresh()), navigate: false);
    }

    public function uploadType(int $typeId): void
    {
        // Typed checklist (Cerun/Borehole/LiDAR/…) removed — use free-form upload instead.
        $this->uploadLoose();
    }

    public function uploadLoose(): void
    {
        $report = $this->persist(false);
        if (empty($this->looseFiles)) {
            $this->addError('looseFiles', __('app.select_file'));

            return;
        }

        $service = app(TechnicalAttachmentService::class);
        $count = 0;
        foreach ($this->looseFiles as $file) {
            if (! $file) {
                continue;
            }
            $service->uploadLoose($report, $file, Auth::user());
            $count++;
        }
        $this->looseFiles = [];
        session()->flash('message', __('app.uploaded_ok', ['name' => $count.' file(s)']));
    }

    protected function persist(bool $submit): Report
    {
        $unit = Unit::where('code', $this->unitCode)->firstOrFail();
        $category = ReportCategory::active()
            ->forUnit($unit->id)
            ->whereIn('code', array_filter([
                $this->categoryCode,
                $this->categoryCode === 'CERUN' ? 'CERUN_RUNTUH' : null,
            ]))
            ->firstOrFail();
        $user = Auth::user();

        if (! $user->isReportCreator() && ! $user->isSuperadmin()) {
            abort(403);
        }

        if ($user->isConsultant() && ! $user->canWriteUnit($unit->id)) {
            abort(403, __('app.consultant_unit_denied'));
        }

        $this->categoryCode = UnitModule::normalizeCategoryCode($category->code) ?? $category->code;

        $this->validate([
            'title' => 'required|string|min:5|max:255',
            'description' => 'required|string|min:10',
            'location_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'vendor_name' => 'nullable|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'categoryCode' => ['required', Rule::exists('report_categories', 'code')->where('unit_id', $unit->id)],
        ]);

        // Enforce category ↔ unit integrity
        if ((int) $category->unit_id !== (int) $unit->id) {
            abort(422, __('app.category_unit_mismatch'));
        }
        if ($this->reportId) {
            $report = Report::findOrFail($this->reportId);
            $this->authorize('update', $report);
            $report->update([
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'title' => $this->title,
                'description' => $this->description,
                'location_name' => $this->location_name ?: null,
                'address' => $this->address ?: null,
                'vendor_name' => $this->vendor_name ?: SurveyVendor::name(),
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'gps_accuracy' => $this->gps_accuracy,
                'gis_data' => $this->gis_data,
            ]);
        } else {
            $report = Report::create([
                'category_id' => $category->id,
                'user_id' => $user->id,
                'unit_id' => $unit->id,
                'title' => $this->title,
                'description' => $this->description,
                'location_name' => $this->location_name ?: null,
                'address' => $this->address ?: null,
                'vendor_name' => $this->vendor_name ?: SurveyVendor::name(),
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'gps_accuracy' => $this->gps_accuracy,
                'gis_data' => $this->gis_data,
                'status' => 'draft',
                'workflow_status' => null,
            ]);
            $this->reportId = $report->id;
        }

        $this->uploadPendingFiles($report);

        return $report->fresh(['category.attachmentTypes']);
    }

    protected function uploadPendingFiles(Report $report): void
    {
        $service = app(TechnicalAttachmentService::class);

        foreach ($this->looseFiles as $file) {
            if (! $file) {
                continue;
            }
            $service->uploadLoose($report, $file, Auth::user());
        }
        $this->looseFiles = [];

        // Legacy typed uploads (if any remain in session)
        foreach ($this->uploads as $typeId => $file) {
            if (! $file) {
                continue;
            }
            $type = AttachmentType::find($typeId);
            if (! $type) {
                continue;
            }
            $service->upload($report, $type, $file, Auth::user());
        }
        $this->uploads = [];
    }

    public function updatedUnitCode(string $value): void
    {
        if ($this->reportId) {
            return;
        }

        $user = Auth::user();
        $unitId = Unit::where('code', $value)->value('id');
        if (! $user || (! $user->isSuperadmin() && ! $user->canWriteUnit($unitId))) {
            $this->addError('unitCode', __('app.consultant_unit_denied'));

            return;
        }

        $this->resetErrorBag('unitCode');
    }

    public function render()
    {
        $user = Auth::user();
        $unit = Unit::where('code', $this->unitCode)->firstOrFail();
        $category = ReportCategory::with('attachmentTypes')
            ->active()
            ->forUnit($unit->id)
            ->whereIn('code', array_filter([
                $this->categoryCode,
                $this->categoryCode === 'CERUN' ? 'CERUN_RUNTUH' : null,
            ]))
            ->firstOrFail();

        $report = $this->reportId
            ? Report::with(['attachments' => fn ($q) => $q->where('is_current', true)])->find($this->reportId)
            : null;

        $selectableUnits = UnitModule::navUnits()->filter(
            fn (Unit $u) => $user?->isSuperadmin() || $user?->canWriteUnit($u->id)
        )->values();

        $canPickUnit = ! $report && $selectableUnits->count() > 1;

        return view('livewire.unit-module.case-form', [
            'unit' => $unit,
            'category' => $category,
            'report' => $report,
            'listUrl' => UnitModule::categoryRoute($unit->code, $category->code),
            'selectableUnits' => $selectableUnits,
            'canPickUnit' => $canPickUnit,
        ])->title(($report ? __('app.edit') : __('app.create')).' '.$category->display_name);
    }
}

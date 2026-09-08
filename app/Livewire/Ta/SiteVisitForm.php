<?php

namespace App\Livewire\Ta;

use App\Models\Report;
use App\Models\SiteVisit;
use App\Models\SiteVisitPhoto;
use App\Services\Workflow\ReportWorkflowService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.master')]
#[Title('Borang Lawatan Tapak')]
class SiteVisitForm extends Component
{
    use WithFileUploads;

    public Report $report;
    public SiteVisit $visit;

    public string $file_number = '';
    public string $reference = 'MBSJ.SPB.PT.PPP(KEJ)-01.RK(01)';
    public string $visit_date = '';
    public string $visit_time = '';
    public ?float $latitude = null;
    public ?float $longitude = null;
    public ?float $gps_accuracy = null;
    public string $laporan_pj_pjk = '';
    public string $visit_notes = '';
    public string $ta_designation = '';
    public string $ta_signature = '';

    public $photos = [];
    public array $photo_captions = [];

    public function mount(Report $report): void
    {
        $user = Auth::user();
        $this->authorize('view', $report);

        if (! in_array($report->workflow_status, ['pending_site_visit', 'site_visit_in_progress', 'engineer_returned', 'pending_engineer_verification'], true)) {
            abort(403);
        }

        if (in_array($report->workflow_status, ['pending_site_visit', 'engineer_returned'], true)
            && $user->can('startSiteVisit', $report)) {
            app(ReportWorkflowService::class)->startSiteVisit($report, $user);
            $report->refresh();
        }

        $this->report = $report->load(['user', 'unit', 'category', 'attachments', 'workflowHistories.user']);
        $this->visit = $report->siteVisit()->firstOrFail();

        if ($this->visit->isSubmitted() && $report->workflow_status === 'pending_engineer_verification') {
            // read-only after submit unless returned
        } elseif ($this->visit->ta_user_id !== $user->id && $user->isTa()) {
            // allow other TA of same unit to view
        }

        $this->fillFromVisit();
    }

    private function fillFromVisit(): void
    {
        $v = $this->visit;
        $this->file_number = $v->file_number ?: ($this->report->file_number ?? '');
        $this->reference = $v->reference ?: 'MBSJ.SPB.PT.PPP(KEJ)-01.RK(01)';
        $this->visit_date = optional($v->visit_date)->format('Y-m-d') ?: now()->format('Y-m-d');
        $this->visit_time = $v->visit_time ? substr((string) $v->visit_time, 0, 5) : now()->format('H:i');
        $this->latitude = $v->latitude ? (float) $v->latitude : null;
        $this->longitude = $v->longitude ? (float) $v->longitude : null;
        $this->gps_accuracy = $v->gps_accuracy ? (float) $v->gps_accuracy : null;
        $this->laporan_pj_pjk = $v->laporan_pj_pjk ?? '';
        $this->visit_notes = $v->visit_notes ?? '';
        $this->ta_designation = $v->ta_designation ?: Auth::user()->default_designation;
        $this->ta_signature = $v->ta_signature ?: Auth::user()->name;
    }

    public function setGps(float $lat, float $lng, ?float $accuracy = null): void
    {
        $this->latitude = round($lat, 7);
        $this->longitude = round($lng, 7);
        $this->gps_accuracy = $accuracy;
    }

    public function removePhoto(int $id): void
    {
        if ($this->visit->isSubmitted() && $this->report->workflow_status !== 'engineer_returned') {
            return;
        }
        $photo = SiteVisitPhoto::where('site_visit_id', $this->visit->id)->findOrFail($id);
        Storage::disk('public')->delete($photo->file_path);
        $photo->delete();
    }

    public function saveDraft(): void
    {
        $this->authorize('manageSiteVisit', $this->report);
        $this->persistPhotos();
        app(ReportWorkflowService::class)->saveSiteVisitDraft($this->visit, Auth::user(), $this->payload());
        $this->visit->refresh();
        session()->flash('message', 'Draf lawatan tapak disimpan.');
    }

    public function submit(): void
    {
        $this->authorize('manageSiteVisit', $this->report);
        $this->validate([
            'visit_date'     => 'required|date',
            'visit_time'     => 'required',
            'laporan_pj_pjk' => 'required|string|min:20',
            'latitude'       => 'nullable|numeric',
            'longitude'      => 'nullable|numeric',
        ], [
            'laporan_pj_pjk.required' => 'Laporan PJ/PJK wajib diisi.',
            'laporan_pj_pjk.min'      => 'Laporan PJ/PJK terlalu singkat.',
        ]);

        $this->persistPhotos();
        app(ReportWorkflowService::class)->submitSiteVisit($this->visit, Auth::user(), $this->payload());
        session()->flash('message', 'Laporan lawatan tapak dihantar untuk pengesahan Engineer.');
        $this->redirect(route('ta.reports'), navigate: false);
    }

    private function payload(): array
    {
        return [
            'file_number'    => $this->file_number,
            'reference'      => $this->reference,
            'visit_date'     => $this->visit_date,
            'visit_time'     => $this->visit_time,
            'latitude'       => $this->latitude,
            'longitude'      => $this->longitude,
            'gps_accuracy'   => $this->gps_accuracy,
            'laporan_pj_pjk' => $this->laporan_pj_pjk,
            'visit_notes'    => $this->visit_notes ?: null,
            'ta_designation' => $this->ta_designation,
            'ta_signature'   => $this->ta_signature ?: Auth::user()->name,
        ];
    }

    private function persistPhotos(): void
    {
        foreach ($this->photos as $i => $file) {
            if (! $file) {
                continue;
            }
            $path = $file->store('site-visits/' . $this->visit->id, 'public');
            SiteVisitPhoto::create([
                'site_visit_id' => $this->visit->id,
                'user_id'       => Auth::id(),
                'file_name'     => $file->getClientOriginalName(),
                'file_path'     => $path,
                'file_type'     => $file->getMimeType(),
                'file_size'     => $file->getSize(),
                'caption'       => $this->photo_captions[$i] ?? null,
                'latitude'      => $this->latitude,
                'longitude'     => $this->longitude,
                'taken_at'      => now(),
            ]);
        }
        $this->photos = [];
        $this->photo_captions = [];
    }

    public function getDistanceKmProperty(): ?float
    {
        if (! $this->latitude || ! $this->longitude || ! $this->report->latitude || ! $this->report->longitude) {
            return null;
        }

        return round($this->haversine(
            (float) $this->report->latitude,
            (float) $this->report->longitude,
            (float) $this->latitude,
            (float) $this->longitude
        ), 3);
    }

    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $earth * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function render()
    {
        $canEdit = Auth::user()->can('manageSiteVisit', $this->report)
            || ($this->report->workflow_status === 'engineer_returned' && Auth::user()->isTa());

        return view('livewire.ta.site-visit-form', [
            'savedPhotos' => $this->visit->photos()->latest()->get(),
            'canEdit'     => $canEdit && (! $this->visit->isSubmitted() || $this->report->workflow_status === 'engineer_returned'),
            'distanceKm'  => $this->distanceKm,
        ]);
    }
}

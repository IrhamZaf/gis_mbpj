<?php

namespace App\Livewire\Surveyor;

use App\Livewire\Concerns\StoresSurveyAttachments;
use App\Models\Report;
use App\Models\ReportAttachment;
use App\Models\ReportCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.master')]
#[Title('Edit Laporan')]
class ReportEdit extends Component
{
    use StoresSurveyAttachments;
    use WithFileUploads;

    public Report $report;
    public $category_id = '';
    public string $title = '';
    public string $description = '';
    public string $location_name = '';
    public ?float $latitude = null;
    public ?float $longitude = null;
    public ?array $gis_data = null;
    public array $attachments = []; // For NEW uploads

    public function mount(Report $report)
    {
        if ($report->user_id !== Auth::id()) {
            abort(403);
        }

        $this->report        = $report;
        $this->category_id   = $report->category_id;
        $this->title         = $report->title;
        $this->description   = $report->description ?? '';
        $this->location_name = $report->location_name ?? '';
        $this->latitude      = $report->latitude;
        $this->longitude     = $report->longitude;
        $this->gis_data      = $report->gis_data;

        $this->dispatch('init-report-map', latitude: $this->latitude, longitude: $this->longitude, locationLabel: $this->location_name, surveyLayers: $this->existingSurveyLayers());
    }

    protected function rules(): array
    {
        return [
            'category_id'   => ['required', Rule::exists('report_categories', 'id')],
            'title'         => 'required|string|min:5|max:255',
            'description'   => 'required|string|min:10',
            'location_name' => 'nullable|string|max:255',
            'latitude'      => 'nullable|numeric|between:-90,90',
            'longitude'     => 'nullable|numeric|between:-180,180',
            'attachments.*' => 'nullable|file|max:20480',
        ];
    }

    protected array $messages = [
        'category_id.required' => 'Sila pilih kategori laporan.',
        'category_id.exists'   => 'Kategori laporan tidak sah.',
        'title.required'       => 'Sila masukkan tajuk laporan.',
        'title.min'            => 'Tajuk mestilah sekurang-kurangnya 5 aksara.',
        'description.required' => 'Sila masukkan keterangan.',
        'description.min'      => 'Keterangan mestilah sekurang-kurangnya 10 aksara.',
        'attachments.*.max'    => 'Saiz fail maksimum ialah 20 MB.',
        'attachments.*.file'   => 'Muat naik fail tidak sah.',
    ];

    #[On('report-coordinates-updated')]
    public function setCoordinates(float $latitude, float $longitude, ?string $label = null): void
    {
        $this->latitude  = round($latitude, 7);
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

    public function deleteAttachment(int $id)
    {
        $attachment = ReportAttachment::where('report_id', $this->report->id)->findOrFail($id);
        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();
    }

    public function removeAttachment(int $index): void
    {
        array_splice($this->attachments, $index, 1);
    }

    public function saveDraft()
    {
        $this->store('draft');
    }

    public function submit()
    {
        $this->store('submitted');
    }

    private function existingSurveyLayers(): array
    {
        return $this->report->attachments()
            ->whereIn('document_type', ['survey_3d', 'survey_2d'])
            ->where('parse_status', 'ok')
            ->get()
            ->map(fn ($a) => [
                'file_name'     => $a->file_name,
                'document_type' => $a->document_type,
                'parse_status'  => $a->parse_status,
                'parse_message' => $a->parse_message,
                'parsed_data'   => $a->parsed_data,
            ])
            ->values()
            ->all();
    }

    private function store(string $status): void
    {
        $this->validate();

        try {
            [$lat, $lng] = $this->resolvedReportAnchor($this->latitude, $this->longitude);

            $this->report->update([
                'category_id'   => (int) $this->category_id,
                'title'         => $this->title,
                'description'   => $this->description,
                'status'        => $status,
                'latitude'      => $lat,
                'longitude'     => $lng,
                'location_name' => $this->location_name ?: null,
                'gis_data'      => $this->gis_data,
                'submitted_at'  => $status === 'submitted' ? now() : null,
            ]);

            if (!empty($this->attachments)) {
                $this->storeAttachments($this->report, $this->attachments, $lat, $lng);
            }

            session()->flash('message', $status === 'submitted' ? 'Laporan berjaya dikemaskini.' : 'Draf berjaya dikemaskini.');

            $this->redirect(route('surveyor.reports'), navigate: false);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Report update failed', [
                'report_id' => $this->report->id,
                'message'   => $e->getMessage(),
            ]);
            $this->addError('submit', 'Gagal menyimpan laporan. Sila cuba lagi.');
        }
    }

    public function render()
    {
        return view('livewire.surveyor.report-edit', [
            'categories'       => ReportCategory::orderBy('name')->get(),
            'savedAttachments' => $this->report->attachments()->get(),
        ]);
    }
}

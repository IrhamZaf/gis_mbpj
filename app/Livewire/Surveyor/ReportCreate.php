<?php

namespace App\Livewire\Surveyor;

use App\Livewire\Concerns\StoresSurveyAttachments;
use App\Models\Report;
use App\Models\ReportCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.master')]
#[Title('Cipta Laporan')]
class ReportCreate extends Component
{
    use StoresSurveyAttachments;
    use WithFileUploads;

    // ── Form fields ─────────────────────────────────────
    public $category_id = '';
    public string $title = '';
    public string $description = '';
    public string $location_name = '';
    public ?float $latitude = null;
    public ?float $longitude = null;
    public ?array $gis_data = null;
    public array $attachments = [];

    // ── Validation ──────────────────────────────────────
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

    // ── Map coordinate events (from child ReportMapPicker) ──
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

    // ── File management ─────────────────────────────────
    public function removeAttachment(int $index): void
    {
        array_splice($this->attachments, $index, 1);
    }

    // ── Actions ─────────────────────────────────────────
    public function saveDraft(): void
    {
        $this->store('draft');
    }

    public function submit(): void
    {
        $this->store('submitted');
    }

    private function store(string $status): void
    {
        $this->validate();

        try {
            // Default to MBPJ centre if no map coordinates set
            [$lat, $lng] = $this->resolvedReportAnchor($this->latitude, $this->longitude);

            $report = Report::create([
                'category_id'   => (int) $this->category_id,
                'user_id'       => Auth::id(),
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
                $this->storeAttachments($report, $this->attachments, $lat, $lng);
            }

            session()->flash('message', $status === 'submitted'
                ? 'Laporan berjaya dihantar.'
                : 'Draf laporan berjaya disimpan.');

            $this->redirect(route('surveyor.reports'), navigate: false);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Report save failed', [
                'status'  => $status,
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);
            $this->addError('submit', 'Gagal menyimpan laporan. Sila cuba lagi.');
        }
    }

    // ── Render ───────────────────────────────────────────
    public function render()
    {
        return view('livewire.surveyor.report-create', [
            'categories' => ReportCategory::orderBy('name')->get(),
        ]);
    }
}

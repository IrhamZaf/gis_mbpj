<?php

namespace App\Http\Controllers\gis;

use App\Http\Controllers\Concerns\ServesSurveyUploadFiles;
use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\IncidentReview;
use App\Models\IncidentTimeline;
use App\Models\SurveyData;
use App\Models\SurveyUpload;
use App\Models\User;
use App\Notifications\AdditionalSurveyRequestedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EngineerController extends Controller
{
    use ServesSurveyUploadFiles;

    public function index(Request $request): View
    {
        $activeTab = $request->string('tab', 'pending')->toString();
        if (! in_array($activeTab, ['pending', 'approved', 'files'])) {
            $activeTab = 'pending';
        }

        // Always load pending count for badge
        $pendingIncidents = Incident::query()
            ->whereIn('status', ['baru_dilaporkan', 'dalam_siasatan', 'tindakan_diperlukan'])
            ->orderByDesc('date_reported')
            ->limit(100)
            ->get();

        $approvedIncidents = collect();
        $uploads = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 40);
        $bareSurveys = collect();
        $qSearch = '';

        if ($activeTab === 'approved') {
            $approvedIncidents = Incident::query()
                ->whereHas('reviews', fn ($q) => $q->where('is_approved', true))
                ->orderByDesc('date_reported')
                ->limit(100)
                ->get();
        }

        if ($activeTab === 'files') {
            $qSearch = trim((string) $request->input('q', ''));

            $uploads = SurveyUpload::query()
                ->with(['survey.surveyor', 'survey.incident', 'uploader'])
                ->when($qSearch !== '', function ($q) use ($qSearch) {
                    $like = '%'.$qSearch.'%';
                    $q->where(function ($w) use ($like) {
                        $w->where('original_name', 'like', $like)
                            ->orWhereHas('survey.incident', function ($iq) use ($like) {
                                $iq->where('incident_number', 'like', $like);
                            })
                            ->orWhereHas('survey.surveyor', function ($uq) use ($like) {
                                $uq->where('name', 'like', $like)->orWhere('email', 'like', $like);
                            });
                    });
                })
                ->orderByDesc('created_at')
                ->paginate(40)
                ->withQueryString();

            $bareSurveys = SurveyData::query()
                ->with(['surveyor', 'incident'])
                ->whereDoesntHave('uploads')
                ->when($qSearch !== '', function ($q) use ($qSearch) {
                    $like = '%'.$qSearch.'%';
                    $q->where(function ($w) use ($like) {
                        $w->where('vendor_name', 'like', $like)
                            ->orWhere('surveyor_name', 'like', $like)
                            ->orWhere('survey_type', 'like', $like)
                            ->orWhere('notes', 'like', $like)
                            ->orWhere('technical_notes', 'like', $like)
                            ->orWhereHas('incident', function ($iq) use ($like) {
                                $iq->where('incident_number', 'like', $like);
                            })
                            ->orWhereHas('surveyor', function ($uq) use ($like) {
                                $uq->where('name', 'like', $like)->orWhere('email', 'like', $like);
                            });
                    });
                })
                ->orderByDesc('survey_date')
                ->orderByDesc('id')
                ->limit(50)
                ->get();
        }

        return view('engineer.index', compact('activeTab', 'pendingIncidents', 'approvedIncidents', 'uploads', 'bareSurveys', 'qSearch'));
    }

    public function pending(): View
    {
        $incidents = Incident::query()
            ->whereIn('status', ['baru_dilaporkan', 'dalam_siasatan', 'tindakan_diperlukan'])
            ->orderByDesc('date_reported')
            ->limit(100)
            ->get();

        return view('engineer.pending', compact('incidents'));
    }

    /**
     * Senarai semua fail laporan yang dimuat naik oleh surveyor (tujuan utama jurutera).
     */
    public function surveyFiles(Request $request): View
    {
        $qSearch = trim((string) $request->input('q', ''));

        $uploads = SurveyUpload::query()
            ->with(['survey.surveyor', 'survey.incident', 'uploader'])
            ->when($qSearch !== '', function ($q) use ($qSearch) {
                $like = '%'.$qSearch.'%';
                $q->where(function ($w) use ($like) {
                    $w->where('original_name', 'like', $like)
                        ->orWhereHas('survey.incident', function ($iq) use ($like) {
                            $iq->where('incident_number', 'like', $like);
                        })
                        ->orWhereHas('survey.surveyor', function ($uq) use ($like) {
                            $uq->where('name', 'like', $like)->orWhere('email', 'like', $like);
                        });
                });
            })
            ->orderByDesc('created_at')
            ->paginate(40)
            ->withQueryString();

        $bareSurveys = SurveyData::query()
            ->with(['surveyor', 'incident'])
            ->whereDoesntHave('uploads')
            ->when($qSearch !== '', function ($q) use ($qSearch) {
                $like = '%'.$qSearch.'%';
                $q->where(function ($w) use ($like) {
                    $w->where('vendor_name', 'like', $like)
                        ->orWhere('surveyor_name', 'like', $like)
                        ->orWhere('survey_type', 'like', $like)
                        ->orWhere('notes', 'like', $like)
                        ->orWhere('technical_notes', 'like', $like)
                        ->orWhereHas('incident', function ($iq) use ($like) {
                            $iq->where('incident_number', 'like', $like);
                        })
                        ->orWhereHas('surveyor', function ($uq) use ($like) {
                            $uq->where('name', 'like', $like)->orWhere('email', 'like', $like);
                        });
                });
            })
            ->orderByDesc('survey_date')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('engineer.survey-files', compact('uploads', 'qSearch', 'bareSurveys'));
    }

    public function approved(): View
    {
        $incidents = Incident::query()
            ->whereHas('reviews', fn ($q) => $q->where('is_approved', true))
            ->orderByDesc('date_reported')
            ->limit(100)
            ->get();

        return view('engineer.approved', compact('incidents'));
    }

    public function review(Incident $incident): View
    {
        $incident->load(['media', 'timeline.performer', 'reviews.engineer', 'surveys.uploads', 'surveys.surveyor']);

        return view('engineer.review', compact('incident'));
    }

    public function approve(Request $request, Incident $incident): RedirectResponse
    {
        $data = $request->validate([
            'risk_assessment' => ['required', 'string'],
            'recommendation' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        IncidentReview::query()->create([
            'incident_id' => $incident->id,
            'engineer_id' => $request->user()->id,
            'risk_assessment' => $data['risk_assessment'],
            'recommendation' => $data['recommendation'] ?? null,
            'is_approved' => true,
            'notes' => $data['notes'] ?? null,
        ]);

        $old = $incident->status;
        $incident->update(['status' => 'dalam_pemantauan']);

        IncidentTimeline::query()->create([
            'incident_id' => $incident->id,
            'action' => 'approved',
            'description' => 'Disahkan oleh jurutera.',
            'performed_by' => $request->user()->id,
            'status_from' => $old,
            'status_to' => $incident->status,
        ]);

        return redirect()->route('engineer.index')->with('success', 'Insiden diluluskan.');
    }

    public function reject(Request $request, Incident $incident): RedirectResponse
    {
        $data = $request->validate([
            'notes' => ['required', 'string'],
        ]);

        IncidentReview::query()->create([
            'incident_id' => $incident->id,
            'engineer_id' => $request->user()->id,
            'risk_assessment' => '',
            'recommendation' => null,
            'is_approved' => false,
            'notes' => $data['notes'],
        ]);

        $old = $incident->status;
        $incident->update(['status' => 'dalam_siasatan']);

        IncidentTimeline::query()->create([
            'incident_id' => $incident->id,
            'action' => 'rejected',
            'description' => 'Ditolak / perlu semakan semula.',
            'performed_by' => $request->user()->id,
            'status_from' => $old,
            'status_to' => $incident->status,
        ]);

        return redirect()->route('engineer.index')->with('success', 'Maklum balas direkodkan.');
    }

    public function downloadSurveyUpload(Request $request, SurveyUpload $upload)
    {
        if (! $request->user()->isEngineer() && ! $request->user()->isAdmin()) {
            abort(403);
        }
        if (! Storage::disk('public')->exists($upload->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download($upload->file_path, $upload->original_name ?: basename($upload->file_path));
    }

    /**
     * Papar PDF / imej / TXT terus dalam pelayar (tab baharu).
     */
    public function viewSurveyUpload(Request $request, SurveyUpload $upload)
    {
        if (! $request->user()->isEngineer() && ! $request->user()->isAdmin()) {
            abort(403);
        }

        return $this->surveyUploadInlineResponse($upload);
    }

    public function approveSurvey(Request $request, SurveyData $survey): RedirectResponse
    {
        if (! $request->user()->isEngineer() && ! $request->user()->isAdmin()) {
            abort(403);
        }
        $survey->load('incident');
        $incident = $survey->incident;
        if (! $incident) {
            return redirect()->back()->withErrors(['error' => 'Survey tiada insiden berkaitan.']);
        }

        $survey->update(['review_status' => SurveyData::REVIEW_APPROVED]);

        IncidentTimeline::query()->create([
            'incident_id' => $incident->id,
            'action' => 'survey_surveyor_approved',
            'description' => 'Semakan hantaran surveyor (versi '.$survey->version.') diluluskan.',
            'performed_by' => $request->user()->id,
            'status_from' => $incident->status,
            'status_to' => $incident->status,
        ]);

        return redirect()->route('engineer.review', $incident)->with('success', 'Hantaran surveyor diluluskan.');
    }

    public function rejectSurvey(Request $request, SurveyData $survey): RedirectResponse
    {
        if (! $request->user()->isEngineer() && ! $request->user()->isAdmin()) {
            abort(403);
        }
        $data = $request->validate([
            'survey_reject_notes' => ['required', 'string', 'max:2000'],
        ]);

        $survey->load('incident');
        $incident = $survey->incident;
        if (! $incident) {
            return redirect()->back()->withErrors(['error' => 'Survey tiada insiden berkaitan.']);
        }

        $survey->update(['review_status' => SurveyData::REVIEW_REJECTED]);

        IncidentTimeline::query()->create([
            'incident_id' => $incident->id,
            'action' => 'survey_surveyor_rejected',
            'description' => 'Hantaran surveyor (v'.$survey->version.') ditolak: '.$data['survey_reject_notes'],
            'performed_by' => $request->user()->id,
            'status_from' => $incident->status,
            'status_to' => $incident->status,
        ]);

        return redirect()->route('engineer.review', $incident)->with('success', 'Hantaran surveyor ditolak.');
    }

    public function requestAdditionalSurvey(Request $request, Incident $incident): RedirectResponse
    {
        if (! $request->user()->isEngineer() && ! $request->user()->isAdmin()) {
            abort(403);
        }
        $data = $request->validate([
            'additional_survey_message' => ['required', 'string', 'max:2000'],
        ]);

        IncidentTimeline::query()->create([
            'incident_id' => $incident->id,
            'action' => 'survey_additional_requested',
            'description' => $data['additional_survey_message'],
            'performed_by' => $request->user()->id,
            'status_from' => $incident->status,
            'status_to' => $incident->status,
        ]);

        $notifyUserIds = SurveyData::query()
            ->where('incident_id', $incident->id)
            ->pluck('surveyor_id')
            ->unique()
            ->filter();

        foreach (User::query()->whereIn('id', $notifyUserIds)->get() as $recipient) {
            if ($recipient->isVendor() || $recipient->isSurveyor()) {
                $recipient->notify(new AdditionalSurveyRequestedNotification($incident, $data['additional_survey_message']));
            }
        }

        return redirect()->route('engineer.review', $incident)->with('success', 'Permintaan survey tambahan direkodkan dan pemberitahuan dihantar kepada surveyor berkaitan.');
    }
}

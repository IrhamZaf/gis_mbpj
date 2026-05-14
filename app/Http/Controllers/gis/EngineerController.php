<?php

namespace App\Http\Controllers\gis;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\IncidentReview;
use App\Models\IncidentTimeline;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EngineerController extends Controller
{
    public function pending(): View
    {
        $incidents = Incident::query()
            ->whereIn('status', ['baru_dilaporkan', 'dalam_siasatan', 'tindakan_diperlukan'])
            ->orderByDesc('date_reported')
            ->limit(100)
            ->get();

        return view('engineer.pending', compact('incidents'));
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
        $incident->load(['media', 'timeline.performer', 'reviews.engineer', 'surveys']);

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

        return redirect()->route('engineer.pending')->with('success', 'Insiden diluluskan.');
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

        return redirect()->route('engineer.pending')->with('success', 'Maklum balas direkodkan.');
    }
}

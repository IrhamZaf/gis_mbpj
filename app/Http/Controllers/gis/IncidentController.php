<?php

namespace App\Http\Controllers\gis;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\IncidentMedia;
use App\Models\IncidentTimeline;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class IncidentController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->string('category')->toString();

        return view('incidents.index', compact('category'));
    }

    public function create(): View
    {
        $this->authorizeSurveyorOrAdmin();
        $engineers = User::query()->whereIn('role', [User::ROLE_ENGINEER, User::ROLE_ADMIN])->orderBy('name')->get();

        return view('incidents.create', compact('engineers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeSurveyorOrAdmin();
        $validated = $request->validate([
            'category' => ['required', 'in:sinkhole,slope'],
            'date_reported' => ['required', 'date'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'address' => ['nullable', 'string', 'max:500'],
            'risk_level' => ['required', 'in:selamat,pemantauan,kritikal'],
            'status' => ['required', 'in:baru_dilaporkan,dalam_siasatan,dalam_pemantauan,tindakan_diperlukan,selesai'],
            'description' => ['nullable', 'string'],
            'assigned_engineer' => ['nullable', 'exists:users,id'],
            'images.*' => ['nullable', 'file', 'image', 'max:10240'],
            'videos.*' => ['nullable', 'file', 'mimes:mp4,webm', 'max:51200'],
            'reports.*' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $incident = new Incident($validated);
        $incident->reported_by = $request->user()->id;
        $incident->save();

        $this->storeMediaBatch($request, $incident, 'images', 'image', 'during');
        $this->storeMediaBatch($request, $incident, 'videos', 'video', 'during');
        $this->storeMediaBatch($request, $incident, 'reports', 'pdf', 'during');

        IncidentTimeline::query()->create([
            'incident_id' => $incident->id,
            'action' => 'created',
            'description' => 'Laporan insiden dicipta.',
            'performed_by' => $request->user()->id,
            'status_from' => null,
            'status_to' => $incident->status,
        ]);

        return redirect()->route('incidents.show', $incident)->with('success', 'Insiden berjaya direkodkan.');
    }

    public function show(Incident $incident): View
    {
        $incident->load(['media', 'timeline.performer', 'reviews.engineer', 'surveys.surveyor', 'reporter', 'engineer']);

        return view('incidents.show', compact('incident'));
    }

    public function edit(Incident $incident): View
    {
        $this->authorizeEdit($incident);
        $engineers = User::query()->whereIn('role', [User::ROLE_ENGINEER, User::ROLE_ADMIN])->orderBy('name')->get();

        return view('incidents.edit', compact('incident', 'engineers'));
    }

    public function update(Request $request, Incident $incident): RedirectResponse
    {
        $this->authorizeEdit($incident);
        $oldStatus = $incident->status;

        $validated = $request->validate([
            'category' => ['required', 'in:sinkhole,slope'],
            'date_reported' => ['required', 'date'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'address' => ['nullable', 'string', 'max:500'],
            'risk_level' => ['required', 'in:selamat,pemantauan,kritikal'],
            'status' => ['required', 'in:baru_dilaporkan,dalam_siasatan,dalam_pemantauan,tindakan_diperlukan,selesai'],
            'description' => ['nullable', 'string'],
            'assigned_engineer' => ['nullable', 'exists:users,id'],
            'images.*' => ['nullable', 'file', 'image', 'max:10240'],
            'videos.*' => ['nullable', 'file', 'mimes:mp4,webm', 'max:51200'],
            'reports.*' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $incident->update($validated);

        $this->storeMediaBatch($request, $incident, 'images', 'image', 'during');
        $this->storeMediaBatch($request, $incident, 'videos', 'video', 'during');
        $this->storeMediaBatch($request, $incident, 'reports', 'pdf', 'during');

        if ($oldStatus !== $incident->status) {
            IncidentTimeline::query()->create([
                'incident_id' => $incident->id,
                'action' => 'status_change',
                'description' => 'Status dikemas kini.',
                'performed_by' => $request->user()->id,
                'status_from' => $oldStatus,
                'status_to' => $incident->status,
            ]);
        }

        return redirect()->route('incidents.show', $incident)->with('success', 'Insiden dikemas kini.');
    }

    public function destroy(Incident $incident): RedirectResponse
    {
        $user = request()->user();
        if (! $user->isAdmin() && $incident->reported_by !== $user->id) {
            abort(403);
        }
        foreach ($incident->media as $m) {
            Storage::disk('public')->delete($m->file_path);
        }
        $incident->delete();

        return redirect()->route('incidents.index')->with('success', 'Insiden dipadam.');
    }

    public function dataTable(Request $request): JsonResponse
    {
        $q = Incident::query()->with('reporter');

        if ($request->filled('category')) {
            $q->where('category', $request->string('category'));
        }

        $data = $q->orderByDesc('date_reported')->limit(500)->get()->map(function (Incident $i) {
            return [
                'id' => $i->id,
                'incident_number' => $i->incident_number,
                'category' => $i->category,
                'date_reported' => $i->date_reported->format('Y-m-d'),
                'risk_level' => $i->risk_level,
                'status' => $i->status,
                'address' => $i->address,
            ];
        });

        return response()->json(['data' => $data]);
    }

    protected function authorizeEdit(Incident $incident): void
    {
        $user = request()->user();
        if ($user->isAdmin()) {
            return;
        }
        if ($incident->reported_by === $user->id && $user->isSurveyor()) {
            return;
        }
        abort(403);
    }

    protected function authorizeSurveyorOrAdmin(): void
    {
        $user = request()->user();
        if ($user->isAdmin() || $user->isSurveyor()) {
            return;
        }
        abort(403);
    }

    protected function storeMediaBatch(Request $request, Incident $incident, string $input, string $type, string $phase): void
    {
        if (! $request->hasFile($input)) {
            return;
        }
        $folder = "incidents/{$incident->id}/{$input}";
        foreach ($request->file($input) as $file) {
            if (! $file) {
                continue;
            }
            $path = $file->store($folder, 'public');
            IncidentMedia::query()->create([
                'incident_id' => $incident->id,
                'type' => $type,
                'file_path' => $path,
                'caption' => $file->getClientOriginalName(),
                'upload_phase' => $phase,
            ]);
        }
    }
}

<?php

namespace App\Http\Controllers\gis;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\SurveyData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SurveyController extends Controller
{
    public function index(): View
    {
        $surveys = SurveyData::query()->with(['incident', 'surveyor'])->orderByDesc('survey_date')->limit(200)->get();

        return view('survey.index', compact('surveys'));
    }

    public function upload(): View
    {
        $incidents = Incident::query()->orderByDesc('date_reported')->limit(200)->get();

        return view('survey.upload', compact('incidents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'incident_id' => ['nullable', 'exists:incidents,id'],
            'survey_date' => ['required', 'date'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string'],
            'geojson_file' => ['nullable', 'file', 'mimes:json,geojson,txt', 'max:10240'],
            'kml_file' => ['nullable', 'file', 'mimes:kml,kmz,xml', 'max:10240'],
            'shape_zip' => ['nullable', 'file', 'mimes:zip', 'max:51200'],
            'drone_image' => ['nullable', 'file', 'image', 'max:20480'],
            'pdf_report' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $gps = null;
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $gps = [
                'lat' => (float) $request->input('latitude'),
                'lng' => (float) $request->input('longitude'),
            ];
        }

        $geojson = null;
        if ($request->hasFile('geojson_file')) {
            $raw = file_get_contents($request->file('geojson_file')->getRealPath());
            $geojson = json_decode($raw, true);
            if (! is_array($geojson)) {
                return back()->withErrors(['geojson_file' => 'Fail GeoJSON tidak sah.'])->withInput();
            }
        } elseif ($request->hasFile('kml_file')) {
            $raw = file_get_contents($request->file('kml_file')->getRealPath());
            $geojson = $this->kmlToGeoJsonFeatures($raw);
        }

        $storedPath = null;
        if ($request->hasFile('shape_zip')) {
            $storedPath = $request->file('shape_zip')->store('surveys/shape', 'public');
        }
        if ($request->hasFile('drone_image')) {
            $storedPath = $request->file('drone_image')->store('surveys/drone', 'public');
        }
        if ($request->hasFile('pdf_report')) {
            $storedPath = $request->file('pdf_report')->store('surveys/pdf', 'public');
        }

        SurveyData::query()->create([
            'incident_id' => $validated['incident_id'] ?? null,
            'surveyor_id' => $request->user()->id,
            'survey_date' => $validated['survey_date'],
            'gps_coordinates' => $gps,
            'geojson_data' => $geojson,
            'notes' => $validated['notes'] ?? null,
            'original_filename' => $storedPath,
        ]);

        return redirect()->route('survey.index')->with('success', 'Data survey berjaya dimuat naik.');
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function kmlToGeoJsonFeatures(string $kml): ?array
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($kml);
        if ($xml === false) {
            return ['type' => 'FeatureCollection', 'features' => []];
        }
        $xml->registerXPathNamespace('kml', 'http://www.opengis.net/kml/2.2');
        $coords = (string) ($xml->xpath('//kml:coordinates')[0] ?? '');
        $coords = trim(preg_replace('/\s+/', ' ', $coords));
        if ($coords === '') {
            return ['type' => 'FeatureCollection', 'features' => []];
        }
        $points = [];
        foreach (explode(' ', $coords) as $triplet) {
            $parts = explode(',', $triplet);
            if (count($parts) >= 2) {
                $points[] = [(float) $parts[0], (float) $parts[1]];
            }
        }
        if (count($points) === 0) {
            return ['type' => 'FeatureCollection', 'features' => []];
        }
        $geometry = count($points) === 1
            ? ['type' => 'Point', 'coordinates' => $points[0]]
            : ['type' => 'LineString', 'coordinates' => $points];

        return [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'geometry' => $geometry,
                    'properties' => ['source' => 'kml'],
                ],
            ],
        ];
    }
}

<?php

namespace App\Http\Controllers\gis;

use App\Http\Controllers\Controller;
use App\Models\GisLayer;
use App\Models\Incident;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MapController extends Controller
{
    public function index(): View
    {
        $layers = GisLayer::query()->where('is_active', true)->orderBy('name')->get();

        return view('map', compact('layers'));
    }

    public function layers(): View
    {
        $layers = GisLayer::query()->where('is_active', true)->orderBy('name')->get();

        return view('layers', compact('layers'));
    }

    public function incidentsGeoJson(Request $request): JsonResponse
    {
        $query = Incident::query()->with(['media', 'reporter']);

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }
        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->string('risk_level'));
        }

        $features = $query->get()->map(function (Incident $incident) {
            return [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float) $incident->longitude, (float) $incident->latitude],
                ],
                'properties' => [
                    'id' => $incident->id,
                    'incident_number' => $incident->incident_number,
                    'category' => $incident->category,
                    'risk_level' => $incident->risk_level,
                    'status' => $incident->status,
                    'address' => $incident->address,
                    'heat' => $incident->heatIntensity(),
                ],
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    public function layerGeoJson(GisLayer $layer): JsonResponse
    {
        if (! $layer->is_active) {
            abort(404);
        }
        $data = $layer->geojson_data;
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        if (! is_array($data)) {
            return response()->json(['type' => 'FeatureCollection', 'features' => []]);
        }

        return response()->json($data);
    }
}

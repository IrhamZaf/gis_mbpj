<?php

namespace App\Http\Controllers\gis;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\SurveyData;
use App\Models\SurveyUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;
use ZipArchive;

class SurveyController extends Controller
{
    public function index(Request $request): View
    {
        $q = SurveyData::query()->with(['incident', 'surveyor', 'uploads']);

        if ($request->user()->isVendor()) {
            $q->where('surveyor_id', $request->user()->id);
        }

        $surveys = $q->orderByDesc('survey_date')->orderByDesc('id')->limit(200)->get();

        // Data for inline upload form
        $incidents = Incident::query()->orderByDesc('date_reported')->limit(200)->get();

        $parent = null;
        if ($request->filled('parent')) {
            $parent = SurveyData::query()->find($request->integer('parent'));
            if ($parent && $parent->surveyor_id !== $request->user()->id && ! $request->user()->isAdmin()) {
                $parent = null;
            }
        }

        $prefillIncidentId = $request->integer('incident_id') ?: null;

        return view('survey.index', compact('surveys', 'incidents', 'parent', 'prefillIncidentId'));
    }

    public function upload(Request $request): View
    {
        $incidents = Incident::query()->orderByDesc('date_reported')->limit(200)->get();

        $parent = null;
        if ($request->filled('parent')) {
            $parent = SurveyData::query()->find($request->integer('parent'));
            if ($parent && $parent->surveyor_id !== $request->user()->id && ! $request->user()->isAdmin()) {
                $parent = null;
            }
        }

        $prefillIncidentId = $request->integer('incident_id') ?: null;

        return view('survey.upload', compact('incidents', 'parent', 'prefillIncidentId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'incident_id' => ['nullable', 'exists:incidents,id'],
            'parent_survey_id' => ['nullable', 'exists:survey_data,id'],
            'survey_date' => ['required', 'date'],
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'surveyor_name' => ['nullable', 'string', 'max:255'],
            'survey_type' => ['nullable', 'string', 'max:120'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'coordinate_crs' => ['nullable', 'string', 'max:64'],
            'notes' => ['nullable', 'string'],
            'technical_notes' => ['nullable', 'string'],
            'geojson_file' => ['nullable', 'file', 'mimes:json,geojson', 'max:10240'],
            'survey_readings_txt' => ['nullable', 'file', 'mimes:txt', 'max:10240'],
            'excel_files' => ['nullable', 'array'],
            'excel_files.*' => ['file', 'mimes:xlsx,xls', 'max:51200'],
            'kml_file' => ['nullable', 'file', 'mimes:kml,kmz,xml', 'max:10240'],
            'shape_zip' => ['nullable', 'file', 'mimes:zip', 'max:51200'],
            'geotiff' => ['nullable', 'file', 'mimes:tif,tiff', 'max:102400'],
            'contour_file' => ['nullable', 'file', 'max:51200'],
            'drone_image' => ['nullable', 'file', 'image', 'max:20480'],
            'drone_video' => ['nullable', 'file', 'mimes:mp4,webm,mov', 'max:102400'],
            'photo_before' => ['nullable', 'file', 'image', 'max:20480'],
            'photo_after' => ['nullable', 'file', 'image', 'max:20480'],
            'pdf_report' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'pdf_geotech' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'pdf_inspection' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $parent = null;
        if (! empty($validated['parent_survey_id'])) {
            $parent = SurveyData::query()->find($validated['parent_survey_id']);
            if ($parent && $parent->surveyor_id !== $request->user()->id && ! $request->user()->isAdmin()) {
                return back()->withErrors(['parent_survey_id' => 'Versi induk tidak sah.'])->withInput();
            }
        }

        $version = $parent ? $parent->version + 1 : 1;
        $incidentId = $validated['incident_id'] ?? $parent?->incident_id;

        $gps = null;
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $gps = [
                'lat' => (float) $request->input('latitude'),
                'lng' => (float) $request->input('longitude'),
            ];
        }

        $converted = null;
        if ($gps !== null) {
            $converted = [
                'wgs84' => [
                    'lat' => $gps['lat'],
                    'lng' => $gps['lng'],
                    'crs' => 'EPSG:4326',
                ],
                'source' => 'form_gps',
                'input_crs_note' => $validated['coordinate_crs'] ?? 'EPSG:4326',
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
            $raw = $this->readKmlOrKmzContents($request->file('kml_file'));
            $geojson = $this->kmlToGeoJsonFeatures($raw);
        }

        $categories = [];

        $survey = SurveyData::query()->create([
            'incident_id' => $incidentId,
            'surveyor_id' => $request->user()->id,
            'vendor_name' => $validated['vendor_name'] ?? null,
            'surveyor_name' => $validated['surveyor_name'] ?? null,
            'survey_date' => $validated['survey_date'],
            'survey_type' => $validated['survey_type'] ?? null,
            'gps_coordinates' => $gps,
            'geojson_data' => $geojson,
            'gis_metadata' => null,
            'converted_coordinates' => $converted,
            'notes' => $validated['notes'] ?? null,
            'technical_notes' => $validated['technical_notes'] ?? null,
            'original_filename' => null,
            'review_status' => SurveyData::REVIEW_PENDING,
            'version' => $version,
            'parent_survey_id' => $parent?->id,
        ]);

        $uid = $request->user()->id;
        $firstName = null;

        if ($request->hasFile('geojson_file')) {
            $this->appendUpload($survey, $request->file('geojson_file'), SurveyUpload::CAT_GIS_GEOJSON, 'gis', $version, $uid);
            $categories[] = SurveyUpload::CAT_GIS_GEOJSON;
            $firstName ??= $request->file('geojson_file')->getClientOriginalName();
        }
        if ($request->hasFile('kml_file')) {
            $this->appendUpload($survey, $request->file('kml_file'), SurveyUpload::CAT_GIS_KML, 'gis', $version, $uid);
            $categories[] = SurveyUpload::CAT_GIS_KML;
            $firstName ??= $request->file('kml_file')->getClientOriginalName();
        }
        if ($request->hasFile('shape_zip')) {
            $this->appendUpload($survey, $request->file('shape_zip'), SurveyUpload::CAT_GIS_SHP, 'gis', $version, $uid);
            $categories[] = SurveyUpload::CAT_GIS_SHP;
            $firstName ??= $request->file('shape_zip')->getClientOriginalName();
        }
        if ($request->hasFile('geotiff')) {
            $this->appendUpload($survey, $request->file('geotiff'), SurveyUpload::CAT_GIS_GEOTIFF, 'gis', $version, $uid);
            $categories[] = SurveyUpload::CAT_GIS_GEOTIFF;
            $firstName ??= $request->file('geotiff')->getClientOriginalName();
        }
        if ($request->hasFile('contour_file')) {
            $this->appendUpload($survey, $request->file('contour_file'), SurveyUpload::CAT_GIS_CONTOUR, 'gis', $version, $uid);
            $categories[] = SurveyUpload::CAT_GIS_CONTOUR;
            $firstName ??= $request->file('contour_file')->getClientOriginalName();
        }
        if ($request->hasFile('pdf_report')) {
            $this->appendUpload($survey, $request->file('pdf_report'), SurveyUpload::CAT_DOC_PDF_SURVEY, 'docs', $version, $uid);
            $categories[] = SurveyUpload::CAT_DOC_PDF_SURVEY;
            $firstName ??= $request->file('pdf_report')->getClientOriginalName();
        }
        if ($request->hasFile('pdf_geotech')) {
            $this->appendUpload($survey, $request->file('pdf_geotech'), SurveyUpload::CAT_DOC_PDF_GEOTECH, 'docs', $version, $uid);
            $categories[] = SurveyUpload::CAT_DOC_PDF_GEOTECH;
            $firstName ??= $request->file('pdf_geotech')->getClientOriginalName();
        }
        if ($request->hasFile('survey_readings_txt')) {
            $this->appendUpload($survey, $request->file('survey_readings_txt'), SurveyUpload::CAT_DOC_SURVEY_TXT_READINGS, 'docs', $version, $uid);
            $categories[] = SurveyUpload::CAT_DOC_SURVEY_TXT_READINGS;
            $firstName ??= $request->file('survey_readings_txt')->getClientOriginalName();
        }
        $excelUploaded = $request->file('excel_files');
        if ($excelUploaded) {
            $excelList = is_array($excelUploaded) ? $excelUploaded : [$excelUploaded];
            foreach ($excelList as $xfile) {
                if (! $xfile instanceof UploadedFile || ! $xfile->isValid()) {
                    continue;
                }
                $this->appendUpload($survey, $xfile, SurveyUpload::CAT_DOC_EXCEL_ANALYTICS, 'docs', $version, $uid);
                $categories[] = SurveyUpload::CAT_DOC_EXCEL_ANALYTICS;
                $firstName ??= $xfile->getClientOriginalName();
            }
        }
        if ($request->hasFile('pdf_inspection')) {
            $this->appendUpload($survey, $request->file('pdf_inspection'), SurveyUpload::CAT_DOC_INSPECTION, 'docs', $version, $uid);
            $categories[] = SurveyUpload::CAT_DOC_INSPECTION;
            $firstName ??= $request->file('pdf_inspection')->getClientOriginalName();
        }
        if ($request->hasFile('drone_image')) {
            $this->appendUpload($survey, $request->file('drone_image'), SurveyUpload::CAT_MEDIA_DRONE_IMAGE, 'media', $version, $uid);
            $categories[] = SurveyUpload::CAT_MEDIA_DRONE_IMAGE;
            $firstName ??= $request->file('drone_image')->getClientOriginalName();
        }
        if ($request->hasFile('drone_video')) {
            $this->appendUpload($survey, $request->file('drone_video'), SurveyUpload::CAT_MEDIA_DRONE_VIDEO, 'media', $version, $uid);
            $categories[] = SurveyUpload::CAT_MEDIA_DRONE_VIDEO;
            $firstName ??= $request->file('drone_video')->getClientOriginalName();
        }
        if ($request->hasFile('photo_before')) {
            $this->appendUpload($survey, $request->file('photo_before'), SurveyUpload::CAT_MEDIA_BEFORE, 'media', $version, $uid);
            $categories[] = SurveyUpload::CAT_MEDIA_BEFORE;
            $firstName ??= $request->file('photo_before')->getClientOriginalName();
        }
        if ($request->hasFile('photo_after')) {
            $this->appendUpload($survey, $request->file('photo_after'), SurveyUpload::CAT_MEDIA_AFTER, 'media', $version, $uid);
            $categories[] = SurveyUpload::CAT_MEDIA_AFTER;
            $firstName ??= $request->file('photo_after')->getClientOriginalName();
        }

        $survey->update([
            'gis_metadata' => [
                'uploaded_at' => now()->toIso8601String(),
                'crs_assumed' => $validated['coordinate_crs'] ?? 'EPSG:4326',
                'file_categories' => array_values(array_unique($categories)),
                'upload_count' => $survey->uploads()->count(),
            ],
            'original_filename' => $firstName,
        ]);

        return redirect()->route('survey.index')->with('success', 'Data survey (versi '.$version.') berjaya dimuat naik.');
    }

    protected function appendUpload(SurveyData $survey, UploadedFile $file, string $category, string $storageSubdir, int $version, int $userId): void
    {
        $path = $file->store('surveys/'.$storageSubdir, 'public');
        SurveyUpload::query()->create([
            'survey_data_id' => $survey->id,
            'category' => $category,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'version' => $version,
            'uploaded_by' => $userId,
        ]);
    }

    protected function readKmlOrKmzContents(UploadedFile $file): string
    {
        $raw = '';
        $ext = strtolower($file->getClientOriginalExtension());
        if ($ext === 'kmz') {
            $zip = new ZipArchive;
            if ($zip->open($file->getRealPath()) === true) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $name = $zip->getNameIndex($i);
                    if ($name && str_ends_with(strtolower($name), '.kml')) {
                        $inner = $zip->getFromIndex($i);
                        $zip->close();

                        return $inner !== false ? (string) $inner : '';
                    }
                }
                $zip->close();
            }

            return '';
        }

        return (string) file_get_contents($file->getRealPath());
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

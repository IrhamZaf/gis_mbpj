<?php

namespace App\Models;

use App\Support\SurveyUploadBrowserPreview;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyUpload extends Model
{
    public const CAT_GIS_GEOJSON = 'gis_geojson';

    public const CAT_GIS_SHP = 'gis_shp';

    public const CAT_GIS_KML = 'gis_kml';

    public const CAT_GIS_GEOTIFF = 'gis_geotiff';

    public const CAT_GIS_CONTOUR = 'gis_contour';

    public const CAT_DOC_PDF_SURVEY = 'doc_pdf_survey';

    public const CAT_DOC_PDF_GEOTECH = 'doc_pdf_geotech';

    /** Data bacaan survey (teks ringkas / CSV-style / log bacaan alat). */
    public const CAT_DOC_SURVEY_TXT_READINGS = 'doc_survey_txt_readings';

    /** Hamparan / analisis (Excel). */
    public const CAT_DOC_EXCEL_ANALYTICS = 'doc_excel_analytics';

    public const CAT_DOC_INSPECTION = 'doc_inspection_pdf';

    public const CAT_MEDIA_DRONE_IMAGE = 'media_drone_image';

    public const CAT_MEDIA_DRONE_VIDEO = 'media_drone_video';

    public const CAT_MEDIA_BEFORE = 'media_photo_before';

    public const CAT_MEDIA_AFTER = 'media_photo_after';

    protected $fillable = [
        'survey_data_id',
        'category',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'meta',
        'version',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(SurveyData::class, 'survey_data_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** PDF, imej, TXT — boleh dibuka dalam tab pelayar (inline). */
    public function isPreviewableInBrowser(): bool
    {
        return SurveyUploadBrowserPreview::isPreviewable($this);
    }

    public function labelMs(): string
    {
        return match ($this->category) {
            self::CAT_GIS_GEOJSON => 'GeoJSON',
            self::CAT_GIS_SHP => 'Shapefile (SHP)',
            self::CAT_GIS_KML => 'KML/KMZ',
            self::CAT_GIS_GEOTIFF => 'GeoTIFF',
            self::CAT_GIS_CONTOUR => 'Data kontur',
            self::CAT_DOC_PDF_SURVEY => 'Laporan survey PDF',
            self::CAT_DOC_PDF_GEOTECH => 'Laporan geoteknik',
            self::CAT_DOC_SURVEY_TXT_READINGS => 'TXT data bacaan survey',
            self::CAT_DOC_EXCEL_ANALYTICS => 'Excel data analisis (.xlsx / .xls)',
            self::CAT_DOC_INSPECTION => 'Laporan pemeriksaan tapak',
            self::CAT_MEDIA_DRONE_IMAGE => 'Imej drone',
            self::CAT_MEDIA_DRONE_VIDEO => 'Video drone',
            self::CAT_MEDIA_BEFORE => 'Gambar sebelum',
            self::CAT_MEDIA_AFTER => 'Gambar selepas',
            default => $this->category,
        };
    }
}

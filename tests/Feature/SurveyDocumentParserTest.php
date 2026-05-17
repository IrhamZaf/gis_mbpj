<?php

namespace Tests\Feature;

use App\Services\Survey\LocalToWgs84Transformer;
use App\Services\Survey\Survey2dTxtParser;
use App\Services\Survey\Survey3dCsvParser;
use App\Services\Survey\SurveyDocumentClassifier;
use App\Services\Survey\SurveyReportMetadataExtractor;
use Tests\TestCase;

class SurveyDocumentParserTest extends TestCase
{
    public function test_classifier_detects_three_types(): void
    {
        $c = new SurveyDocumentClassifier();

        $this->assertSame('survey_3d', $c->classify('CN1 ATC5A 3DILAPXYZ_ASCII_CSV.csv'));
        $this->assertSame('survey_2d', $c->classify('CN1 ATC5A 2DILAPXYZ_ASCII_TXT.txt'));
        $this->assertSame('survey_1d', $c->classify('CN1 ATC5A 1DILAPXYZ_GRAPH & ANALYSIS REPORT2.pdf'));
        $this->assertSame('other', $c->classify('photo.jpg'));
    }

    public function test_classifier_detects_from_file_content(): void
    {
        $c = new SurveyDocumentClassifier();
        $csv = file_get_contents(base_path('tests/fixtures/survey/cn1-3d.csv'));
        $txt = file_get_contents(base_path('tests/fixtures/survey/cn1-2d.txt'));

        $this->assertSame('survey_3d', $c->classify('points.csv', null, $csv));
        $this->assertSame('survey_2d', $c->classify('monitoring.txt', null, $txt));
        $this->assertSame('survey_2d', $c->classify('data.csv', null, $txt));
        $this->assertSame('survey_1d', $c->classify('report.pdf', null, '%PDF-1.4'));
        $this->assertSame('survey_3d', $c->detectFromContent($csv));
        $this->assertSame('survey_2d', $c->detectFromContent($txt));
    }

    public function test_parse_3d_csv_fixture(): void
    {
        $content = file_get_contents(base_path('tests/fixtures/survey/cn1-3d.csv'));
        $parsed = (new Survey3dCsvParser())->parse($content);

        $this->assertSame('3d', $parsed['type']);
        $this->assertGreaterThan(200, count($parsed['points']));
        $this->assertArrayHasKey('xb', $parsed['points'][0]);
        $this->assertArrayHasKey('zb', $parsed['points'][0]);
    }

    public function test_parse_2d_txt_fixture(): void
    {
        $content = file_get_contents(base_path('tests/fixtures/survey/cn1-2d.txt'));
        $parsed = (new Survey2dTxtParser())->parse($content);

        $this->assertSame('2d', $parsed['type']);
        $this->assertContains(1, $parsed['days']);
        $this->assertGreaterThan(200, count($parsed['records']));
        $this->assertSame('P1', $parsed['records'][0]['point']);
    }

    public function test_metadata_extractor_reads_mbpj_filename(): void
    {
        $extractor = new SurveyReportMetadataExtractor(new SurveyDocumentClassifier());
        $meta = $extractor->extract('CN1 ATC5A 3DILAPXYZ_ASCII_CSV.csv');

        $this->assertSame('CN1', $meta['site_code']);
        $this->assertSame('ATC5A', $meta['place_name']);
        $this->assertStringContainsString('ATC5A', $meta['title']);
        $this->assertStringNotContainsString('CN1 ATC5A', $meta['title']);
        $this->assertSame('ATC5A', $meta['location_name']);
        $this->assertStringContainsString('lokasi ATC5A', $meta['description']);
        $this->assertStringContainsString('projek CN1', $meta['description']);
        $this->assertSame('cerun-tanah-runtuh', $meta['category_slug']);
    }

    public function test_category_slug_from_cn_and_sh_site_codes(): void
    {
        $extractor = new SurveyReportMetadataExtractor(new SurveyDocumentClassifier());

        $this->assertSame('cerun-tanah-runtuh', $extractor->categorySlugFromSiteCode('CN1'));
        $this->assertSame('cerun-tanah-runtuh', $extractor->categorySlugFromSiteCode('cn2'));
        $this->assertSame('sinkhole', $extractor->categorySlugFromSiteCode('SH1'));
        $this->assertSame('sinkhole', $extractor->categorySlugFromSiteCode('sh3'));
        $this->assertNull($extractor->categorySlugFromSiteCode('XX1'));

        $shMeta = $extractor->extract('SH1 ATC5B 2DILAPXYZ_ASCII_TXT.txt');
        $this->assertSame('sinkhole', $shMeta['category_slug']);
        $this->assertSame('SH1', $shMeta['site_code']);
        $this->assertSame('ATC5B', $shMeta['place_name']);
        $this->assertSame('ATC5B', $shMeta['location_name']);
    }

    public function test_metadata_extractor_reads_preamble_labels(): void
    {
        $extractor = new SurveyReportMetadataExtractor(new SurveyDocumentClassifier());
        $content = "Tajuk Laporan: Sinkhole Jalan SS2\n"
            . "Keterangan: Retakan di permukaan jalan selepas hujan.\n"
            . "Nama Lokasi: Persimpangan SS2/24\n"
            . "Xb,Yb,Zb\n"
            . "1,2,3\n";

        $meta = $extractor->extract('points.csv', $content);

        $this->assertSame('Sinkhole Jalan SS2', $meta['title']);
        $this->assertStringContainsString('Retakan', $meta['description']);
        $this->assertSame('Persimpangan SS2/24', $meta['location_name']);
    }

    public function test_transformer_places_points_near_anchor(): void
    {
        $content = file_get_contents(base_path('tests/fixtures/survey/cn1-3d.csv'));
        $parsed = (new Survey3dCsvParser())->parse($content);
        $anchorLat = 3.1073;
        $anchorLng = 101.6067;

        $local = array_map(fn ($p) => ['xb' => $p['xb'], 'yb' => $p['yb'], 'id' => $p['id']], array_slice($parsed['points'], 0, 50));
        $geo = (new LocalToWgs84Transformer())->transform($local, $anchorLat, $anchorLng);

        foreach ($geo['points'] as $p) {
            $this->assertEqualsWithDelta($anchorLat, $p['lat'], 0.05);
            $this->assertEqualsWithDelta($anchorLng, $p['lng'], 0.05);
        }
    }
}

<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\Unit;
use App\Models\User;
use App\Support\UnitModule;
use Database\Seeders\UnitCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class UnitModuleStructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_unit_category_seeder_creates_twelve_categories(): void
    {
        $this->seed(UnitCategorySeeder::class);

        $this->assertSame(4, Unit::active()->whereIn('code', array_values(UnitModule::UNIT_SLUGS))->count());

        foreach (UnitModule::UNIT_SLUGS as $unitCode) {
            $unit = Unit::where('code', $unitCode)->first();
            $this->assertNotNull($unit);

            foreach (UnitModule::CATEGORY_CODES as $catCode) {
                $this->assertTrue(
                    ReportCategory::where('unit_id', $unit->id)->where('code', $catCode)->exists(),
                    "Missing {$catCode} for {$unitCode}"
                );
            }
        }

        $this->assertSame(
            12,
            ReportCategory::whereNotNull('unit_id')->whereIn('code', UnitModule::CATEGORY_CODES)->count()
        );
    }

    public function test_named_routes_exist_for_all_unit_categories(): void
    {
        foreach (UnitModule::UNIT_SLUGS as $slug => $code) {
            $this->assertTrue(Route::has($slug.'.dashboard'), "Missing {$slug}.dashboard");
            foreach (UnitModule::CATEGORY_SLUGS as $catSlug => $catCode) {
                $this->assertTrue(Route::has($slug.'.'.$catSlug), "Missing {$slug}.{$catSlug}");
            }
        }
    }

    public function test_report_rejects_mismatched_unit_and_category(): void
    {
        $this->seed(UnitCategorySeeder::class);

        $jalan = Unit::where('code', 'JLN')->firstOrFail();
        $saliran = Unit::where('code', 'SAL-CERUN')->firstOrFail();
        $saliranSinkhole = ReportCategory::where('unit_id', $saliran->id)->where('code', 'SINKHOLE')->firstOrFail();

        $user = User::create([
            'name' => 'Mismatch Consultant',
            'email' => 'mismatch@example.com',
            'password' => bcrypt('password'),
            'role' => 'consultant',
            'unit_id' => null,
            'status' => 'active',
        ]);

        $this->expectException(\InvalidArgumentException::class);

        Report::create([
            'category_id' => $saliranSinkhole->id,
            'user_id' => $user->id,
            'unit_id' => $jalan->id,
            'title' => 'Mismatch test report title',
            'description' => 'Description long enough for validation rules.',
            'status' => 'draft',
            'latitude' => 3.1,
            'longitude' => 101.5,
        ]);
    }
}

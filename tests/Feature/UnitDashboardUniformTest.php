<?php

namespace Tests\Feature;

use App\Livewire\UnitModule\UnitDashboard;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\Unit;
use App\Models\User;
use App\Support\UnitModule;
use Database\Seeders\UnitCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UnitDashboardUniformTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_unit_dashboards_share_same_sections_and_scope_data(): void
    {
        $this->seed(UnitCategorySeeder::class);

        $saliran = Unit::where('code', 'SAL-CERUN')->firstOrFail();
        $jalan = Unit::where('code', 'JLN')->firstOrFail();
        $salCat = ReportCategory::where('unit_id', $saliran->id)->where('code', 'SINKHOLE')->firstOrFail();
        $jlnCat = ReportCategory::where('unit_id', $jalan->id)->where('code', 'SINKHOLE')->firstOrFail();

        $consultant = User::create([
            'name' => 'Dash Consultant',
            'email' => 'dash-consultant@example.com',
            'password' => bcrypt('password'),
            'role' => 'consultant',
            'unit_id' => null,
            'status' => 'active',
        ]);

        Report::create([
            'category_id' => $salCat->id,
            'user_id' => $consultant->id,
            'unit_id' => $saliran->id,
            'title' => 'Saliran only report title here',
            'description' => 'Description long enough for dashboard test.',
            'status' => 'submitted',
            'workflow_status' => 'pending_site_visit',
            'latitude' => 3.1,
            'longitude' => 101.5,
            'submitted_at' => now(),
        ]);

        $other = User::create([
            'name' => 'Jalan Consultant',
            'email' => 'dash-jln@example.com',
            'password' => bcrypt('password'),
            'role' => 'consultant',
            'unit_id' => null,
            'status' => 'active',
        ]);

        Report::create([
            'category_id' => $jlnCat->id,
            'user_id' => $other->id,
            'unit_id' => $jalan->id,
            'title' => 'Jalan only report title here',
            'description' => 'Description long enough for dashboard test.',
            'status' => 'submitted',
            'workflow_status' => 'pending_site_visit',
            'latitude' => 3.2,
            'longitude' => 101.6,
            'submitted_at' => now(),
        ]);

        foreach (UnitModule::UNIT_SLUGS as $slug => $code) {
            $this->actingAs($consultant)
                ->get(route($slug.'.dashboard'))
                ->assertOk()
                ->assertSee(__('app.total_reports'))
                ->assertSee(__('app.report_by_category'))
                ->assertSee(__('app.report_status'))
                ->assertSee(__('app.monthly_report_trend'))
                ->assertSee(__('app.site_visit'))
                ->assertSee(__('app.report_location'))
                ->assertSee(__('app.recent_reports'))
                ->assertSee(__('app.quick_actions'));
        }

        Livewire::actingAs($consultant)
            ->test(UnitDashboard::class, ['unitCode' => 'SAL-CERUN'])
            ->assertSee('Dash Consultant')
            ->assertDontSee('Jalan Consultant');

        Livewire::actingAs($consultant)
            ->test(UnitDashboard::class, ['unitCode' => 'JLN'])
            ->assertSee('Jalan Consultant')
            ->assertDontSee('Dash Consultant')
            ->assertDontSee(__('app.read_only_badge'));
    }
}

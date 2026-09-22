<?php

namespace Tests\Feature;

use App\Livewire\Director\Dashboard as DirectorDashboard;
use App\Livewire\Engineer\Dashboard as EngineerDashboard;
use App\Livewire\Superadmin\Dashboard as SuperadminDashboard;
use App\Livewire\Surveyor\Dashboard as ConsultantDashboard;
use App\Livewire\Ta\Dashboard as TaDashboard;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\Unit;
use App\Models\User;
use App\Support\ReportsByCategory;
use App\Support\UnitModule;
use Database\Seeders\UnitCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportsByCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_summarize_aggregates_across_unit_scoped_category_rows(): void
    {
        $this->seed(UnitCategorySeeder::class);

        $consultant = User::create([
            'name' => 'Cat Consultant',
            'email' => 'cat-consultant@example.com',
            'password' => bcrypt('password'),
            'role' => 'consultant',
            'unit_id' => null,
            'status' => 'active',
        ]);

        foreach (Unit::active()->get() as $unit) {
            $sinkhole = ReportCategory::where('unit_id', $unit->id)->where('code', 'SINKHOLE')->firstOrFail();
            Report::create([
                'category_id' => $sinkhole->id,
                'user_id' => $consultant->id,
                'unit_id' => $unit->id,
                'title' => "Sinkhole {$unit->code}",
                'description' => 'Description long enough for category aggregation test.',
                'status' => 'submitted',
                'workflow_status' => 'pending_site_visit',
                'submitted_at' => now(),
            ]);
        }

        $summary = ReportsByCategory::summarize(excludeDrafts: true);

        $this->assertCount(count(UnitModule::CATEGORY_CODES), $summary);
        $this->assertSame(
            Unit::active()->count(),
            (int) $summary->firstWhere('code', 'SINKHOLE')['reports_count']
        );
        $this->assertSame(0, (int) $summary->firstWhere('code', 'CERUN')['reports_count']);
        $this->assertSame(0, (int) $summary->firstWhere('code', 'BOREHOLE')['reports_count']);
    }

    public function test_role_dashboards_show_aggregated_reports_by_category(): void
    {
        $this->seed(UnitCategorySeeder::class);

        $unit = Unit::where('code', 'SAL-CERUN')->firstOrFail();
        $sinkhole = ReportCategory::where('unit_id', $unit->id)->where('code', 'SINKHOLE')->firstOrFail();

        $consultant = User::create([
            'name' => 'Dash Consultant',
            'email' => 'dash-cat@example.com',
            'password' => bcrypt('password'),
            'role' => 'consultant',
            'unit_id' => null,
            'status' => 'active',
        ]);

        Report::create([
            'category_id' => $sinkhole->id,
            'user_id' => $consultant->id,
            'unit_id' => $unit->id,
            'title' => 'Category dashboard report',
            'description' => 'Description long enough for dashboard category widget.',
            'status' => 'submitted',
            'workflow_status' => 'pending_site_visit',
            'submitted_at' => now(),
        ]);

        $engineer = User::create([
            'name' => 'Dash Engineer',
            'email' => 'dash-eng@example.com',
            'password' => bcrypt('password'),
            'role' => 'engineer',
            'unit_id' => $unit->id,
            'status' => 'active',
        ]);

        $ta = User::create([
            'name' => 'Dash TA',
            'email' => 'dash-ta@example.com',
            'password' => bcrypt('password'),
            'role' => 'ta',
            'unit_id' => $unit->id,
            'status' => 'active',
        ]);

        $director = User::create([
            'name' => 'Dash Director',
            'email' => 'dash-dir@example.com',
            'password' => bcrypt('password'),
            'role' => 'director',
            'unit_id' => null,
            'status' => 'active',
        ]);

        $superadmin = User::create([
            'name' => 'Dash Super',
            'email' => 'dash-super@example.com',
            'password' => bcrypt('password'),
            'role' => 'superadmin',
            'unit_id' => null,
            'status' => 'active',
        ]);

        foreach ([
            [ConsultantDashboard::class, $consultant],
            [EngineerDashboard::class, $engineer],
            [TaDashboard::class, $ta],
            [DirectorDashboard::class, $director],
            [SuperadminDashboard::class, $superadmin],
        ] as [$component, $user]) {
            Livewire::actingAs($user)
                ->test($component)
                ->assertOk()
                ->assertSee(__('app.sinkhole'))
                ->assertDontSee('Saliran & Cerun — Sinkhole');
        }
    }
}

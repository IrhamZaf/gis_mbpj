<?php

namespace Tests\Feature;

use App\Livewire\UnitModule\CaseForm;
use App\Livewire\UnitModule\CaseList;
use App\Livewire\UnitModule\CaseShow;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\Unit;
use App\Models\User;
use App\Policies\ReportPolicy;
use Database\Seeders\UnitCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CrossUnitAccessTest extends TestCase
{
    use RefreshDatabase;

    private Unit $saliran;

    private Unit $jalan;

    private ReportCategory $saliranSinkhole;

    private ReportCategory $jalanSinkhole;

    private User $consultantA;

    private User $consultantB;

    private Report $saliranReport;

    private Report $jalanReport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UnitCategorySeeder::class);

        $this->saliran = Unit::where('code', 'SAL-CERUN')->firstOrFail();
        $this->jalan = Unit::where('code', 'JLN')->firstOrFail();
        $this->saliranSinkhole = ReportCategory::where('unit_id', $this->saliran->id)->where('code', 'SINKHOLE')->firstOrFail();
        $this->jalanSinkhole = ReportCategory::where('unit_id', $this->jalan->id)->where('code', 'SINKHOLE')->firstOrFail();

        $this->consultantA = User::create([
            'name' => 'Consultant A',
            'email' => 'consultant-a@example.com',
            'password' => bcrypt('password'),
            'role' => 'consultant',
            'unit_id' => null,
            'status' => 'active',
        ]);

        $this->consultantB = User::create([
            'name' => 'Consultant B',
            'email' => 'consultant-b@example.com',
            'password' => bcrypt('password'),
            'role' => 'consultant',
            'unit_id' => null,
            'status' => 'active',
        ]);

        $this->saliranReport = Report::create([
            'category_id' => $this->saliranSinkhole->id,
            'user_id' => $this->consultantA->id,
            'unit_id' => $this->saliran->id,
            'title' => 'Sinkhole Saliran submitted case',
            'description' => 'Description long enough for the report.',
            'status' => 'submitted',
            'workflow_status' => 'pending_site_visit',
            'latitude' => 3.1,
            'longitude' => 101.5,
            'submitted_at' => now(),
        ]);

        $this->jalanReport = Report::create([
            'category_id' => $this->jalanSinkhole->id,
            'user_id' => $this->consultantB->id,
            'unit_id' => $this->jalan->id,
            'title' => 'Sinkhole Jalan submitted case',
            'description' => 'Description long enough for the report.',
            'status' => 'submitted',
            'workflow_status' => 'pending_site_visit',
            'latitude' => 3.2,
            'longitude' => 101.6,
            'submitted_at' => now(),
        ]);
    }

    public function test_policy_allows_cross_unit_view_but_not_update_others(): void
    {
        $policy = new ReportPolicy;

        $this->assertTrue($policy->view($this->consultantA, $this->jalanReport));
        $this->assertFalse($policy->update($this->consultantA, $this->jalanReport));
        $this->assertFalse($policy->uploadAttachment($this->consultantA, $this->jalanReport));
        $this->assertFalse($policy->startSiteVisit($this->consultantA, $this->jalanReport));

        $draft = Report::create([
            'category_id' => $this->jalanSinkhole->id,
            'user_id' => $this->consultantA->id,
            'unit_id' => $this->jalan->id,
            'title' => 'Own draft report title here',
            'description' => 'Description long enough for the report.',
            'status' => 'draft',
            'latitude' => 3.1,
            'longitude' => 101.5,
        ]);

        $this->assertTrue($policy->update($this->consultantA, $draft));
        $this->assertFalse($policy->update($this->consultantB, $draft));
        $this->assertFalse($policy->view($this->consultantB, $draft));

        // Owner may edit own report while still Pending Site Visit
        $this->assertTrue($policy->update($this->consultantB, $this->jalanReport));
        $this->assertFalse($policy->update($this->consultantA, $this->jalanReport));
    }

    public function test_consultant_can_open_any_unit_listing_with_write_access(): void
    {
        $this->actingAs($this->consultantA)
            ->get(route('jalan.sinkhole'))
            ->assertOk();

        Livewire::actingAs($this->consultantA)
            ->withQueryParams([])
            ->test(CaseList::class, ['unitCode' => 'JLN', 'categoryCode' => 'SINKHOLE'])
            ->assertSet('unitCode', 'JLN')
            ->assertSee('Sinkhole Jalan submitted case')
            ->assertDontSee(__('app.read_only_badge'));
    }

    public function test_consultant_can_view_others_case_but_not_edit(): void
    {
        $this->actingAs($this->consultantA)
            ->get(route('jalan.cases.show', $this->jalanReport))
            ->assertOk();

        Livewire::actingAs($this->consultantA)
            ->test(CaseShow::class, ['report' => $this->jalanReport])
            ->assertSee('Sinkhole Jalan submitted case')
            ->assertSet('report.id', $this->jalanReport->id);

        $this->actingAs($this->consultantA)
            ->get(route('jalan.cases.edit', $this->jalanReport))
            ->assertForbidden();
    }

    public function test_consultant_can_create_case_for_any_unit(): void
    {
        $this->actingAs($this->consultantA)
            ->get(route('jalan.cases.create', ['categoryCode' => 'SINKHOLE']))
            ->assertOk();

        Livewire::actingAs($this->consultantA)
            ->test(CaseForm::class, ['unitCode' => 'JLN', 'categoryCode' => 'SINKHOLE'])
            ->assertOk();
    }
}

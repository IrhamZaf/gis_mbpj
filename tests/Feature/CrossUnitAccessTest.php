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

    private User $surveyorSaliran;

    private User $surveyorJalan;

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

        $this->surveyorSaliran = User::create([
            'name' => 'Surveyor Saliran',
            'email' => 'sv-sal@example.com',
            'password' => bcrypt('password'),
            'role' => 'surveyor',
            'unit_id' => $this->saliran->id,
            'status' => 'active',
        ]);

        $this->surveyorJalan = User::create([
            'name' => 'Surveyor Jalan',
            'email' => 'sv-jln@example.com',
            'password' => bcrypt('password'),
            'role' => 'surveyor',
            'unit_id' => $this->jalan->id,
            'status' => 'active',
        ]);

        $this->saliranReport = Report::create([
            'category_id' => $this->saliranSinkhole->id,
            'user_id' => $this->surveyorSaliran->id,
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
            'user_id' => $this->surveyorJalan->id,
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

    public function test_policy_allows_cross_unit_view_but_not_update(): void
    {
        $policy = new ReportPolicy;

        $this->assertTrue($policy->view($this->surveyorSaliran, $this->jalanReport));
        $this->assertFalse($policy->update($this->surveyorSaliran, $this->jalanReport));
        $this->assertFalse($policy->uploadAttachment($this->surveyorSaliran, $this->jalanReport));
        $this->assertFalse($policy->startSiteVisit($this->surveyorSaliran, $this->jalanReport));

        $draft = Report::create([
            'category_id' => $this->saliranSinkhole->id,
            'user_id' => $this->surveyorSaliran->id,
            'unit_id' => $this->saliran->id,
            'title' => 'Own draft report title here',
            'description' => 'Description long enough for the report.',
            'status' => 'draft',
            'latitude' => 3.1,
            'longitude' => 101.5,
        ]);

        $this->assertTrue($policy->update($this->surveyorSaliran, $draft));
        $this->assertFalse($policy->update($this->surveyorJalan, $draft));
        $this->assertFalse($policy->view($this->surveyorJalan, $draft));
    }

    public function test_saliran_surveyor_can_open_jalan_listing_read_only(): void
    {
        $this->actingAs($this->surveyorSaliran)
            ->get(route('jalan.sinkhole'))
            ->assertOk();

        Livewire::actingAs($this->surveyorSaliran)
            ->withQueryParams([])
            ->test(CaseList::class, ['unitCode' => 'JLN', 'categoryCode' => 'SINKHOLE'])
            ->assertSet('unitCode', 'JLN')
            ->assertSee('Sinkhole Jalan submitted case')
            ->assertSee(__('app.read_only_badge'));
    }

    public function test_saliran_surveyor_can_view_jalan_case_but_not_edit_route(): void
    {
        $this->actingAs($this->surveyorSaliran)
            ->get(route('jalan.cases.show', $this->jalanReport))
            ->assertOk();

        Livewire::actingAs($this->surveyorSaliran)
            ->test(CaseShow::class, ['report' => $this->jalanReport])
            ->assertSee(__('app.read_only_title'))
            ->assertDontSee(__('app.edit'), false);

        $this->actingAs($this->surveyorSaliran)
            ->get(route('jalan.cases.edit', $this->jalanReport))
            ->assertForbidden();
    }

    public function test_cannot_create_case_for_other_unit(): void
    {
        $this->actingAs($this->surveyorSaliran)
            ->get(route('jalan.cases.create', ['categoryCode' => 'SINKHOLE']))
            ->assertForbidden();

        Livewire::actingAs($this->surveyorSaliran)
            ->test(CaseForm::class, ['unitCode' => 'JLN', 'categoryCode' => 'SINKHOLE'])
            ->assertForbidden();
    }
}

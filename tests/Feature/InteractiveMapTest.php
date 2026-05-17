<?php

namespace Tests\Feature;

use App\Livewire\Shared\InteractiveMap;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InteractiveMapTest extends TestCase
{
    use RefreshDatabase;

    private function createReport(User $user, array $overrides = []): Report
    {
        $category = ReportCategory::create(['name' => 'Sinkhole', 'slug' => 'sinkhole']);

        return Report::create(array_merge([
            'category_id'   => $category->id,
            'user_id'       => $user->id,
            'title'         => 'Test Laporan',
            'description'   => 'Keterangan ujian laporan.',
            'status'        => 'submitted',
            'latitude'      => 3.1073,
            'longitude'     => 101.6067,
            'submitted_at'  => now(),
        ], $overrides));
    }

    public function test_map_routes_require_auth(): void
    {
        $this->get(route('surveyor.map'))->assertRedirect(route('login'));
    }

    public function test_each_role_can_access_map_page(): void
    {
        $surveyor = User::factory()->create(['role' => 'surveyor']);
        $engineer = User::factory()->create(['role' => 'engineer']);
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $this->actingAs($surveyor)->get(route('surveyor.map'))->assertOk();
        $this->actingAs($engineer)->get(route('engineer.map'))->assertOk();
        $this->actingAs($superadmin)->get(route('superadmin.map'))->assertOk();
    }

    public function test_surveyor_sees_only_own_markers(): void
    {
        $surveyor = User::factory()->create(['role' => 'surveyor']);
        $other = User::factory()->create(['role' => 'surveyor']);

        $this->createReport($surveyor, ['title' => 'Milik Saya']);
        $this->createReport($other, ['title' => 'Orang Lain', 'latitude' => 3.2, 'longitude' => 101.7]);

        $markers = Livewire::actingAs($surveyor)
            ->test(InteractiveMap::class)
            ->get('markers');

        $this->assertCount(1, $markers);
        $this->assertSame('Milik Saya', $markers[0]['title']);
    }

    public function test_engineer_sees_only_submitted_markers(): void
    {
        $surveyor = User::factory()->create(['role' => 'surveyor']);
        $engineer = User::factory()->create(['role' => 'engineer']);

        $this->createReport($surveyor, ['status' => 'submitted', 'title' => 'Dihantar']);
        $this->createReport($surveyor, [
            'status'      => 'draft',
            'title'       => 'Draf',
            'submitted_at' => null,
        ]);

        $markers = Livewire::actingAs($engineer)
            ->test(InteractiveMap::class)
            ->get('markers');

        $this->assertCount(1, $markers);
        $this->assertSame('Dihantar', $markers[0]['title']);
    }

    public function test_superadmin_sees_all_markers(): void
    {
        $surveyor = User::factory()->create(['role' => 'surveyor']);
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $this->createReport($surveyor, ['title' => 'Laporan A']);
        $this->createReport($surveyor, ['title' => 'Laporan B', 'latitude' => 3.11, 'longitude' => 101.61]);

        $markers = Livewire::actingAs($superadmin)
            ->test(InteractiveMap::class)
            ->get('markers');

        $this->assertCount(2, $markers);
    }

    public function test_reports_without_coordinates_are_excluded(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $surveyor = User::factory()->create(['role' => 'surveyor']);

        $this->createReport($surveyor);
        $this->createReport($surveyor, [
            'title'     => 'Tiada Koordinat',
            'latitude'  => null,
            'longitude' => null,
        ]);

        $markers = Livewire::actingAs($superadmin)
            ->test(InteractiveMap::class)
            ->get('markers');

        $this->assertCount(1, $markers);
    }
}

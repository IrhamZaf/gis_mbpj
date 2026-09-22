<?php

namespace Tests\Feature;

use App\Livewire\Surveyor\ReportCreate;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_consultant_can_submit_report(): void
    {
        $this->seed(\Database\Seeders\UnitCategorySeeder::class);

        $unit = \App\Models\Unit::where('code', 'JLN')->firstOrFail();
        $category = \App\Models\ReportCategory::where('unit_id', $unit->id)->where('code', 'SINKHOLE')->firstOrFail();

        $consultant = User::create([
            'name'     => 'Consultant Test',
            'email'    => 'consultant-test@example.com',
            'password' => bcrypt('password'),
            'role'     => 'consultant',
            'unit_id'  => null,
            'status'   => 'active',
        ]);

        Livewire::actingAs($consultant)
            ->test(ReportCreate::class)
            ->set('unit_id', (string) $unit->id)
            ->set('category_id', (string) $category->id)
            ->set('title', 'Laporan ujian sinkhole')
            ->set('description', 'Keterangan laporan ujian yang mencukupi panjang.')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('consultant.reports'));

        $this->assertDatabaseHas('reports', [
            'user_id'     => $consultant->id,
            'category_id' => $category->id,
            'unit_id'     => $unit->id,
            'title'       => 'Laporan ujian sinkhole',
            'status'      => 'submitted',
        ]);
    }
}

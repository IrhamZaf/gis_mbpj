<?php

namespace App\Console\Commands;

use App\Models\Incident;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateIncidentCodesCommand extends Command
{
    protected $signature = 'mbpj:migrate-incident-codes
                            {--dry-run : Papar pemetaan tanpa mengemas kini pangkalan}';

    protected $description = 'Tukar semua nombor insiden sedia ada kepada format CN1, CN2 (cerun) dan SH1, SH2 (sinkhole) berdasarkan urutan id rekod';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $sinkhole = Incident::query()->where('category', Incident::CATEGORY_SINKHOLE)->orderBy('id')->get();
        $slope = Incident::query()->where('category', Incident::CATEGORY_SLOPE)->orderBy('id')->get();

        $plan = [];
        $n = 1;
        foreach ($slope as $inc) {
            $plan[] = ['id' => $inc->id, 'old' => $inc->incident_number, 'new' => Incident::CODE_PREFIX_SLOPE.$n, 'category' => 'slope'];
            $n++;
        }
        $n = 1;
        foreach ($sinkhole as $inc) {
            $plan[] = ['id' => $inc->id, 'old' => $inc->incident_number, 'new' => Incident::CODE_PREFIX_SINKHOLE.$n, 'category' => 'sinkhole'];
            $n++;
        }

        usort($plan, fn ($a, $b) => $a['id'] <=> $b['id']);

        $this->table(['id', 'kategori', 'lama', 'baharu'], array_map(fn ($r) => [
            $r['id'], $r['category'], $r['old'], $r['new'],
        ], $plan));

        if ($dry) {
            $this->warn('Dry-run: tiada perubahan disimpan. Laksana semula tanpa --dry-run untuk tulis pangkalan.');

            return self::SUCCESS;
        }

        if ($plan === []) {
            $this->info('Tiada insiden untuk dikemas kini.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($plan): void {
            foreach ($plan as $row) {
                Incident::query()->whereKey($row['id'])->update([
                    'incident_number' => '__MBPJ_TMP__'.$row['id'],
                ]);
            }
            foreach ($plan as $row) {
                Incident::query()->whereKey($row['id'])->update([
                    'incident_number' => $row['new'],
                ]);
            }
        });

        $this->info('Kod insiden dikemas kini: '.count($plan).' rekod.');

        return self::SUCCESS;
    }
}

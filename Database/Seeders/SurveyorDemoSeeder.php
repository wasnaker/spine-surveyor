<?php

declare(strict_types=1);

namespace Modules\Surveyor\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Surveyor\Models\Branch;
use Modules\Surveyor\Models\Surveyor;
use Modules\Region\Models\Province;
use Modules\Vat\Services\VatService;

/**
 * SurveyorDemoSeeder — data demo untuk situs wasnaker.lan (public demo).
 *
 * Isi: 10 surveyor HO + 20 branch (2 branch per surveyor).
 * - HO dan branch-nya share NPWP (branch tidak punya NPWP sendiri).
 * - 5 provinsi (dari data Region yang sudah di-seed), 2 HO per provinsi.
 *
 * Idempotent: firstOrCreate by code. Demo periodik: hapus + re-seed.
 * Jalankan: php artisan db:seed --class="Modules\\Surveyor\\Database\\Seeders\\SurveyorDemoSeeder"
 */
class SurveyorDemoSeeder extends Seeder
{
    /** 5 provinsi referensi (dari 38 existing di RegionSeeder). */
    private const PROVINCES = [
        '31' => 'DKI Jakarta',
        '32' => 'Jawa Barat',
        '33' => 'Jawa Tengah',
        '34' => 'DI Yogyakarta',
        '35' => 'Jawa Timur',
    ];

    /** 10 nama surveyor (2 per provinsi). */
    private const SURVEYOR_NAMES = [
        'Alpha', 'Antariksa',
        'Biru', 'Bintang',
        'Citra', 'Cendana',
        'Damar', 'Dewi',
        'Elang', 'Estu',
    ];

    /** 2 jenis cabang per HO. */
    private const BRANCH_KINDS = ['Cabang', 'Plant'];

    public function run(): void
    {
        $vat = app(VatService::class);

        $provinces = Province::whereIn('code', array_keys(self::PROVINCES))
            ->pluck('id', 'code');
        if ($provinces->count() < count(self::PROVINCES)) {
            $this->command?->warn('Provinsi belum lengkap — jalankan RegionSeeder dulu.');
            return;
        }

        $provCodes = array_keys(self::PROVINCES);

        foreach (self::SURVEYOR_NAMES as $idx => $name) {
            $hoProvCode = $provCodes[$idx % 5];
            $hoProvId = $provinces[$hoProvCode];
            $hoProvName = self::PROVINCES[$hoProvCode];

            $code = strtoupper($name);
            $suffix = str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT);
            $npwpHo = sprintf('%02d.%s.%s.%s-%03d.%03d', $idx + 1, $suffix, $suffix, $suffix, $idx + 1, $idx + 1);

            $hoVat = $vat->findOrCreate($npwpHo, "PT {$name}");

            $surveyor = Surveyor::firstOrCreate(
                ['code' => $code],
                [
                    'name'      => "PT {$name}",
                    'email'     => strtolower("{$name}@mandala.demo"),
                    'phone'     => sprintf('%02d-555%04d', $hoProvId, $idx + 1),
                    'vat_id'    => $hoVat->id,
                    'is_active' => true,
                ]
            );

            // 2 branch di provinsi beda dari HO, share NPWP HO.
            foreach (self::BRANCH_KINDS as $bIdx => $bKind) {
                $branchProvCode = $provCodes[($idx + 1 + $bIdx) % 5];
                if ($branchProvCode === $hoProvCode) {
                    $branchProvCode = $provCodes[($idx + 1 + $bIdx + 1) % 5];
                }
                $branchProvName = self::PROVINCES[$branchProvCode];
                $branchCode = $code[0] . str_pad((string) ($bIdx + 1), 2, '0', STR_PAD_LEFT);

                Branch::firstOrCreate(
                    ['surveyor_id' => $surveyor->id, 'code' => $branchCode],
                    [
                        'name'      => "{$bKind} {$branchProvName}",
                        'address'   => "Jl. Contoh No. {$idx}{$bIdx}, {$branchProvName}",
                        'phone'     => sprintf('%02d-666%02d%02d', $provinces[$branchProvCode], $idx + 1, $bIdx + 1),
                        'vat_id'    => $hoVat->id,
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command?->info(sprintf(
            'Demo data siap: %d surveyors, %d branches, %d NPWP.',
            Surveyor::count(),
            Branch::count(),
            \Modules\Vat\Models\Vat::count()
        ));
    }
}

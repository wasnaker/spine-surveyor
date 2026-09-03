<?php

declare(strict_types=1);

namespace Modules\Customer\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Customer\Models\Branch;
use Modules\Customer\Models\Customer;
use Modules\Region\Models\Province;
use Modules\Vat\Services\VatService;

/**
 * CustomerDemoSeeder — data demo untuk situs wasnaker.lan (public demo).
 *
 * Isi: 20 customer + 60 branch (3 branch per customer, masing-masing di
 * provinsi beda — TIDAK di provinsi HO customer tsb). 10 provinsi
 * referensi (sudah ter-seed via RegionSeeder).
 *
 * Idempotent: firstOrCreate by code. Demo periodik: hapus + re-seed.
 * Jalankan: php artisan db:seed --class="Modules\\Customer\\Database\\Seeders\\CustomerDemoSeeder"
 *
 * Catatan:
 *  - 1 customer punya 1 NPWP HO; tiap branch punya NPWP sendiri (sesuai
 *    domain: 1 NPWP = 1 row global, banyak customer/branch boleh punya
 *    NPWP beda, atau beberapa branch boleh share NPWP kalau dari sisi
 *    pajak memang 1 entitas — tapi untuk demo, tiap entity dapat NPWP
 *    unik supaya Vat table juga berisi data).
 *  - Nama customer fiktif generik (PT/CV Alpha s/d PT/CV Tango) supaya
 *    tidak salah dianggap entitas riil.
 */
class CustomerDemoSeeder extends Seeder
{
    /** 10 provinsi referensi (dari 38 existing di RegionSeeder). */
    private const PROVINCES = [
        '31' => 'DKI Jakarta',
        '32' => 'Jawa Barat',
        '33' => 'Jawa Tengah',
        '34' => 'DI Yogyakarta',
        '35' => 'Jawa Timur',
        '36' => 'Banten',
        '51' => 'Bali',
        '12' => 'Sumatera Utara',
        '14' => 'Riau',
        '73' => 'Sulawesi Selatan',
    ];

    /** 20 nama code customer (2 per provinsi, urut round-robin). */
    private const CUSTOMER_NAMES = [
        'Alpha', 'Antariksa', 'Biru', 'Bintang', 'Citra', 'Cendana',
        'Damar', 'Dewi', 'Elang', 'Estu', 'Fajar', 'Flores',
        'Gajah', 'Gita', 'Harum', 'Hijau', 'Intan', 'Indah',
        'Jaya', 'Jingga',
    ];

    /** 3 nama cabang generik (per customer dapat 3, masing-masing di provinsi beda). */
    private const BRANCH_NAMES = ['Cabang', 'Plant', 'Site'];

    public function run(): void
    {
        $vat = app(VatService::class);

        $provinces = Province::whereIn('code', array_keys(self::PROVINCES))
            ->pluck('id', 'code');
        if ($provinces->count() < count(self::PROVINCES)) {
            $this->command?->warn('Provinsi belum lengkap — jalankan RegionSeeder dulu.');
            return;
        }

        $provCodes = array_keys(self::PROVINCES); // [31, 32, 33, 34, 35, 36, 51, 12, 14, 73]

        foreach (self::CUSTOMER_NAMES as $idx => $name) {
            $hoProvCode  = $provCodes[$idx % count($provCodes)];
            $hoProvId    = $provinces[$hoProvCode];
            $hoProvName  = self::PROVINCES[$hoProvCode];

            $code = strtoupper($name);
            $suffix = str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT);
            $npwpHo = sprintf('%02d.%s.%s.%s-%03d.%03d', $idx + 1, $suffix, $suffix, $suffix, $idx + 1, $idx + 1);

            $hoVat = $vat->findOrCreate($npwpHo, "PT {$name}");

            $customer = Customer::firstOrCreate(
                ['code' => $code],
                [
                    'name'      => "PT {$name}",
                    'email'     => strtolower("{$name}@mandala.demo"),
                    'phone'     => sprintf('%02d-555%04d', $hoProvId, $idx + 1),
                    'vat_id'    => $hoVat->id,
                    'is_active' => true,
                ]
            );

            // 3 branch di 3 provinsi lain (selain HO). Round-robin dari idx+1.
            foreach (self::BRANCH_NAMES as $bIdx => $bKind) {
                $branchProvCode = $provCodes[($idx + 1 + $bIdx) % count($provCodes)];
                if ($branchProvCode === $hoProvCode) {
                    // Jaga-jaga: skip kalau sama dengan HO (round-robin tidak
                    // akan hit ini karena modulo != untuk idx 0, tapi defensive).
                    $branchProvCode = $provCodes[($idx + 1 + $bIdx + 1) % count($provCodes)];
                }
                $branchProvName = self::PROVINCES[$branchProvCode];
                $branchCode = $code[0] . str_pad((string) ($bIdx + 1), 2, '0', STR_PAD_LEFT);
                $npwpBranch = sprintf('%02d.%s.%s.%s-%03d.%03d',
                    $idx + 1, $suffix, $suffix, $suffix,
                    $bIdx + 3, $idx + 1
                );
                $branchVat = $vat->findOrCreate($npwpBranch, "PT {$name} - {$bKind} {$branchProvName}");

                Branch::firstOrCreate(
                    ['customer_id' => $customer->id, 'code' => $branchCode],
                    [
                        'name'      => "{$bKind} {$branchProvName}",
                        'address'   => "Jl. Contoh No. {$idx}{$bIdx}, {$branchProvName}",
                        'phone'     => sprintf('%02d-666%02d%02d', $provinces[$branchProvCode], $idx + 1, $bIdx + 1),
                        'vat_id'    => $branchVat->id,
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command?->info(sprintf(
            'Demo data siap: %d customers, %d branches, %d NPWP.',
            Customer::count(),
            Branch::count(),
            \Modules\Vat\Models\Vat::count()
        ));
    }
}

<?php

declare(strict_types=1);

namespace Modules\Surveyor\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Modules\Surveyor\Models\Surveyor;
use Modules\Region\Models\Province;
use Modules\Region\Models\Regency;
use Modules\Vat\Services\VatService;

/**
 * SurveyorDemoSeeder — data demo untuk situs wasnaker.lan (public demo).
 *
 * Isi: 10 surveyor HO + 20 branch (2 branch per HO).
 *   - Brand disimpan sebagai row di tabel surveyors dengan type='branch' dan parent=ID HO.
 *   - Branch share NPWP dengan HO (NPWP branch = NPWP HO).
 *   - 5 provinsi (dari 38 yang sudah di-seed via RegionSeeder).
 *
 * Idempotent: firstOrCreate by code + parent. Demo periodik: hapus + re-seed.
 * Jalankan: php artisan db:seed --class="Modules\\Surveyor\\Database\\Seeders\\SurveyorDemoSeeder"
 *
 * Catatan:
 *   - 1 surveyor HO punya 1 NPWP HO; tiap branch pakai NPWP yang sama dengan HO.
 *   - Nama surveyor fiktif generik (PT/CV Alpha s/d PT/CV Tango).
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

    /** 10 nama code surveyor (2 per provinsi). */
    private const SURVEYOR_NAMES = [
        'Alpha', 'Antariksa',
        'Biru', 'Bintang',
        'Citra', 'Cendana',
        'Damar', 'Dewi',
        'Elang', 'Estu',
    ];

    /** 2 jenis cabang per HO. */
    private const BRANCH_KINDS = ['Cabang', 'Plant'];

    /**
     * 10 perusahaan mitra (branch kerjasama, NPWP sendiri — bukan NPWP HO).
     * Branch ini menjual jasa atas nama surveyor, tapi tagihan pakai NPWP
     * sendiri. 1 partner utk 1 surveyor HO (urut sesuai SURVEYOR_NAMES).
     * Vat dibuat via VatService (1 NPWP = 1 row global).
     */
    private const PARTNER_VATS = [
        ['npwp' => '31.825.001.5-045.000', 'name' => 'PT Jaya Mitra Survey Indonesia'],
        ['npwp' => '32.746.203.1-425.000', 'name' => 'PT Citra Bangun Mandiri Sejahtera'],
        ['npwp' => '33.512.908.4-513.000', 'name' => 'PT Karya Utama Teknik Nusantara'],
        ['npwp' => '34.234.567.8-542.000', 'name' => 'PT Adhi Karya Surveyor Pratama'],
        ['npwp' => '35.908.112.6-652.000', 'name' => 'PT Sinar Bumi Konsultan Indonesia'],
        ['npwp' => '36.654.321.9-405.000', 'name' => 'PT Mitra Solusi Bangun Perkasa'],
        ['npwp' => '51.445.789.2-905.000', 'name' => 'PT Bali Survey Utama Lestari'],
        ['npwp' => '12.390.214.7-124.000', 'name' => 'PT Sumatera Jasa Ukur Sejahtera'],
        ['npwp' => '14.780.345.3-322.000', 'name' => 'PT Riau Andalan Survey Mandiri'],
        ['npwp' => '73.256.890.8-908.000', 'name' => 'PT Sulawesi Mitra Survey Bersama'],
    ];

    /** Kode provinsi dari 2 digit awal NPWP (id wilayah). */
    private const PARTNER_PROVINCES = [
        '31', '32', '33', '34', '35', '36', '51', '12', '14', '73',
    ];

    public function run(): void
    {
        $vat = app(VatService::class);

        $provinces = Province::whereIn('code', array_keys(self::PROVINCES))
            ->orWhereIn('code', self::PARTNER_PROVINCES)
            ->pluck('id', 'code');
        if ($provinces->count() < count(self::PROVINCES)) {
            $this->command?->warn('Provinsi belum lengkap — jalankan RegionSeeder dulu.');
            return;
        }

        $provCodes = array_keys(self::PROVINCES);

        // admin: 1 user per entity (HO + branch). Email unik per code.
        $adminPass = 'adminpass';
        $makeAdmin = function (string $code, string $name, int $salt) use ($adminPass): int {
            $email = strtolower("srv.{$code}.{$salt}@wasnaker.lan");
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'      => "Admin {$name}",
                    'password' => Hash::make($adminPass),
                    'is_active' => true,
                ]
            );

            return $user->id;
        };

        // pilih 1 regency per provinsi (stabil utk demo)
        $regencyByProvince = Regency::select('province_id', 'id')
            ->get()
            ->groupBy('province_id')
            ->map(fn ($r) => $r->first()->id);

        foreach (self::SURVEYOR_NAMES as $idx => $name) {
            $hoProvCode  = $provCodes[$idx % 5];
            $hoProvId    = $provinces[$hoProvCode];
            $hoProvName  = self::PROVINCES[$hoProvCode];

            $code = strtoupper($name);
            $suffix = str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT);
            $npwpHo = sprintf('%02d.%s.%s.%s-%03d.%03d', $idx + 1, $suffix, $suffix, $suffix, $idx + 1, $idx + 1);

            $hoVat = $vat->findOrCreate($npwpHo, "PT {$name}");

            $surveyor = Surveyor::firstOrCreate(
                ['code' => $code, 'parent_id' => null],
                [
                    'name'      => "PT {$name}",
                    'email'     => strtolower("{$name}@surveyor.demo"),
                    'phone'     => sprintf('%02d-555%04d', $hoProvId, $idx + 1),
                    'address'   => "Jl. Surveyor No. 1, {$hoProvName}",
                    'vat_id'    => $hoVat->id,
                    'is_active' => true,
                    'type'      => 'surveyor',
                ]
            );
            $surveyor->update([
                'admin_id'     => $makeAdmin($code, $surveyor->name, $idx),
                'province_id'  => $hoProvId,
                'regency_id'   => $regencyByProvince[$hoProvId] ?? null,
            ]);

            // 2 branch di 2 provinsi lain (selain HO). Round-robin dari idx+1.
            foreach (self::BRANCH_KINDS as $bIdx => $bKind) {
                $branchProvCode = $provCodes[($idx + 1 + $bIdx) % 5];
                if ($branchProvCode === $hoProvCode) {
                    $branchProvCode = $provCodes[($idx + 1 + $bIdx + 1) % 5];
                }
                $branchProvName = self::PROVINCES[$branchProvCode];
                $branchCode = $code[0] . str_pad((string) ($bIdx + 1), 2, '0', STR_PAD_LEFT);

                $branch = Surveyor::firstOrCreate(
                    ['code' => $branchCode, 'parent_id' => $surveyor->id],
                    [
                        'name'      => "{$bKind} {$branchProvName}",
                        'phone'     => sprintf('%02d-666%02d%02d', $provinces[$branchProvCode], $idx + 1, $bIdx + 1),
                        'address'   => "Jl. Surveyor No. {$idx}{$bIdx}, {$branchProvName}",
                        'vat_id'    => $hoVat->id,
                        'is_active' => true,
                        'type'      => 'branch',
                    ]
                );
                $branchProvId = $provinces[$branchProvCode];
                $branch->update([
                    'admin_id'     => $makeAdmin("{$code}{$bIdx}", $branch->name, $idx + $bIdx),
                    'province_id'  => $branchProvId,
                    'regency_id'   => $regencyByProvince[$branchProvId] ?? null,
                ]);
            }

            // Branch mitra (ke-3): perusahaan lain bekerjasama dgn surveyor.
            // NPWP sendiri (bukan NPWP HO); menjual atas nama surveyor,
            // tagihan pakai NPWP sendiri. 1 partner utk surveyor idx ini.
            if (isset(self::PARTNER_VATS[$idx])) {
                $partner      = self::PARTNER_VATS[$idx];
                $partnerVat   = $vat->findOrCreate($partner['npwp'], $partner['name']);
                $partnerProv  = $provinces[self::PARTNER_PROVINCES[$idx]];
                $partnerProvName = Province::find($partnerProv)?->name ?? 'Indonesia';
                $partnerCode  = $code[0] . '03';

                $mitra = Surveyor::firstOrCreate(
                    ['code' => $partnerCode, 'parent_id' => $surveyor->id],
                    [
                        'name'      => $partner['name'],
                        'phone'     => sprintf('%02d-777%02d', $partnerProv, $idx + 1),
                        'address'   => "Jl. Mitra No. {$idx}, {$partnerProvName}",
                        'vat_id'    => $partnerVat->id,
                        'is_active' => true,
                        'type'      => 'branch',
                    ]
                );
                $mitra->update([
                    'admin_id'     => $makeAdmin("{$code}M", $mitra->name, 100 + $idx),
                    'province_id'  => $partnerProv,
                    'regency_id'   => $regencyByProvince[$partnerProv] ?? null,
                ]);
            }
        }

        $hoCount    = Surveyor::where('type', 'surveyor')->count();
        $branchCount = Surveyor::where('type', 'branch')->count();

        $this->command?->info(sprintf(
            'Demo data siap: %d HO, %d branch, %d NPWP dalam surveyors.',
            $hoCount,
            $branchCount,
            \Modules\Vat\Models\Vat::count()
        ));
    }
}

<?php

declare(strict_types=1);

namespace Modules\Surveyor\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Vat\Models\Vat;
use Modules\Region\Models\Province;
use Modules\Region\Models\Regency;
use App\Models\User;
use Spine\Traits\HasLifecycleHooks;

/**
 * Surveyor — entity utama modul Surveyor.
 *
 * - type: 'surveyor' (HO utama) atau 'branch' (cabang milik HO surveyor lain).
 * - parent_id: FK ke surveyors.id — jika type='branch', ini induknya.
 * - branches: semua cabang milik surveyor ini (type='branch', parent_id=ini.id).
 * - vat:     NPWP HO (belongsTo vats, nullable). 1 NPWP = 1 row global;
 *            banyak surveyor bisa share row Vat yang sama via FK id.
 */
class Surveyor extends Model
{
    use HasLifecycleHooks;
    use HasUlids;
    use SoftDeletes;

    protected $table = 'surveyors';

    protected $fillable = [
        'type',
        'code', 'name', 'email', 'phone',
        'address', 'province_id', 'regency_id', 'vat_id', 'is_active', 'parent_id', 'admin_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'type' => 'surveyor',
    ];

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Surveyor::class, 'parent_id')->where('type', 'branch');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Surveyor::class, 'parent_id');
    }

    public function vat(): BelongsTo
    {
        return $this->belongsTo(Vat::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    public function isBranch(): bool
    {
        return $this->type === 'branch';
    }

    public function scopeHoOnly($query)
    {
        return $query->where('type', 'surveyor');
    }

    public function scopeBranchOnly($query)
    {
        return $query->where('type', 'branch');
    }
}

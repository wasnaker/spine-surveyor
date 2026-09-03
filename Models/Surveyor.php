<?php

declare(strict_types=1);

namespace Modules\Surveyor\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Vat\Models\Vat;
use Spine\Traits\HasLifecycleHooks;

/**
 * Surveyor — entity utama modul Surveyor.
 *
 * - branches: kantor cabang / site / pabrik (hasMany).
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
        'code', 'name', 'email', 'phone',
        'vat_id', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function vat(): BelongsTo
    {
        return $this->belongsTo(Vat::class);
    }
}

<?php

declare(strict_types=1);

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Vat\Models\Vat;
use Spine\Traits\HasLifecycleHooks;

/**
 * Customer — entity utama modul Customer.
 *
 * - branches: kantor cabang / site / pabrik (hasMany).
 * - vat:     NPWP HO (belongsTo vats, nullable). 1 NPWP = 1 row global;
 *            banyak customer bisa share row Vat yang sama via FK id.
 */
class Customer extends Model
{
    use HasLifecycleHooks;
    use HasUlids;
    use SoftDeletes;

    protected $table = 'customers';

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

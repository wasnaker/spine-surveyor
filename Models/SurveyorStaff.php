<?php

namespace Modules\Surveyor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class SurveyorStaff extends Model
{
    protected $table = 'surveyor_staffs';

    protected $fillable = [
        'user_id',
        'surveyor_id',
        'realname',
        'jabatan',
        'phone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function surveyor(): BelongsTo
    {
        return $this->belongsTo(Surveyor::class, 'surveyor_id');
    }
}
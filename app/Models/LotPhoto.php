<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['lot_id', 'path', 'sha256', 'view', 'quality'])]
class LotPhoto extends Model
{
    public const VIEWS = [
        'toan_qua' => 'Toàn quả',
        'be_mat' => 'Cận bề mặt',
        'cuong' => 'Cuống',
        'khac' => 'Khác',
    ];

    const UPDATED_AT = null;

    protected function casts(): array
    {
        return ['quality' => 'array'];
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    public function url(): string
    {
        return asset('storage/'.$this->path);
    }

    public function isUsable(): bool
    {
        return (bool) ($this->quality['ok'] ?? false);
    }
}

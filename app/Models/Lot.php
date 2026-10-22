<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

#[Fillable(['code', 'supplier', 'variety', 'purpose', 'weight_kg', 'harvest_date', 'status', 'created_by', 'published_at'])]
class Lot extends Model
{
    public const STATUSES = [
        'draft' => 'Mới tạo',
        'analyzing' => 'AI đang phân tích',
        'analyzed' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'published' => 'Đã công bố',
    ];

    protected function casts(): array
    {
        return [
            'harvest_date' => 'date',
            'published_at' => 'datetime',
            'weight_kg' => 'decimal:2',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    /** Ma lo in tren tem: XL-ngay-4 ky tu ngau nhien (cot code unique chan trung). */
    public static function newCode(): string
    {
        return 'XL-'.now()->format('ymd').'-'.Str::upper(Str::random(4));
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(LotPhoto::class)->orderBy('id');
    }

    public function suggestions(): HasMany
    {
        return $this->hasMany(AiSuggestion::class);
    }

    public function latestSuggestion(): HasOne
    {
        return $this->hasOne(AiSuggestion::class)->latestOfMany();
    }

    public function labels(): HasMany
    {
        return $this->hasMany(VerifiedLabel::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class)->orderBy('id');
    }

    /**
     * Lan xac nhan moi nhat cua moi tieu chi (xac nhan lai = them dong moi).
     *
     * @return Collection<string, VerifiedLabel>
     */
    public function currentLabels(): Collection
    {
        return $this->labels()->with('confirmer')->orderBy('id')->get()->keyBy('criterion');
    }
}

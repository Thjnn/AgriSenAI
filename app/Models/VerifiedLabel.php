<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['lot_id', 'suggestion_id', 'criterion', 'decision', 'ai_value', 'final_value', 'note', 'confirmed_by', 'confirmed_at'])]
class VerifiedLabel extends Model
{
    public const DECISIONS = [
        'accept' => 'Đồng ý với AI',
        'edit' => 'Sửa gợi ý AI',
        'reject' => 'Bác bỏ',
        'manual' => 'Nhập thủ công',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'ai_value' => 'array',
            'final_value' => 'array',
            'confirmed_at' => 'datetime',
        ];
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}

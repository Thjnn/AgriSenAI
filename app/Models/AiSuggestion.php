<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/** Chi them: phan tich lai = them dong moi, khong sua dong cu. */
#[Fillable(['lot_id', 'model', 'prompt_version', 'photo_ids', 'result', 'error', 'latency_ms'])]
class AiSuggestion extends Model
{
    const UPDATED_AT = null;

    protected function casts(): array
    {
        return ['photo_ids' => 'array', 'result' => 'array'];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('ai_suggestions chi duoc them, khong duoc sua.'));
    }

    /** AI tra loi duoc va anh dung duoc -> moi co goi y de xac nhan. */
    public function hasResult(): bool
    {
        return $this->result !== null && ($this->result['usable'] ?? false);
    }
}

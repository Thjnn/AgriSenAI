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

    /**
     * Anh AI danh dau khong dung duoc (anh man hinh, khong phai xoai...).
     * Gemini tra so thu tu 1-based theo thu tu anh gui di = thu tu photo_ids.
     *
     * @return array<int, string> photo_id => ly do
     */
    public function badPhotos(): array
    {
        $out = [];
        foreach ($this->result['bad_photos'] ?? [] as $b) {
            $id = $this->photo_ids[(int) ($b['index'] ?? 0) - 1] ?? null;
            if ($id !== null) {
                $out[$id] = $b['reason'] ?? 'AI đánh dấu ảnh không dùng được.';
            }
        }

        // Ca lo khong dung duoc ma AI khong chi ro anh nao -> danh dau het.
        if ($out === [] && $this->result !== null && ! ($this->result['usable'] ?? true)) {
            $out = array_fill_keys($this->photo_ids, $this->result['unusable_reason'] ?? 'AI không dùng được ảnh này.');
        }

        return $out;
    }

    /** AI tra loi duoc va anh dung duoc -> moi co goi y de xac nhan. */
    public function hasResult(): bool
    {
        return $this->result !== null && ($this->result['usable'] ?? false);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * Nhat ky chi them. Ghi qua App\Services\AuditChain, khong tao truc tiep.
 *
 * ponytail: chan sua/xoa o muc model; sua thang DB van duoc (co y — de demo
 * "dai do" tren trang QR). Muon chan han thi them trigger MySQL.
 */
#[Fillable(['lot_id', 'actor_id', 'action', 'payload', 'hash', 'created_at'])]
class AuditLog extends Model
{
    public const ACTIONS = [
        'created' => 'Tạo lô',
        'photo_added' => 'Thêm ảnh',
        'photo_removed' => 'Xoá ảnh',
        'ai_suggested' => 'AI gợi ý ngoại quan',
        'confirmed' => 'Người phụ trách xác nhận',
        'published' => 'Công bố hồ sơ',
    ];

    protected $table = 'lot_audit_logs';

    public $timestamps = false;

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Nhat ky audit chi duoc them.'));
        static::deleting(fn () => throw new LogicException('Nhat ky audit chi duoc them.'));
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function data(): array
    {
        return json_decode($this->payload, true) ?? [];
    }
}

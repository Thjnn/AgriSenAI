<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Lot;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * Nhat ky audit noi chuoi bam theo tung lo (y tuong tu audit() / lot_view()
 * trong MVP mau cua co Hang):
 *
 *   hash_n = sha256(hash_{n-1} . payload_n)      (hash_0 = '')
 *
 * Sua bat ky payload nao -> moi hash phia sau lech -> trang QR hien dai do.
 */
class AuditChain
{
    public function append(Lot $lot, string $action, array $data, ?User $actor = null): AuditLog
    {
        $payload = self::canonicalJson($data);
        $prev = AuditLog::where('lot_id', $lot->id)->orderByDesc('id')->value('hash') ?? '';

        return AuditLog::create([
            'lot_id' => $lot->id,
            'actor_id' => $actor?->id,
            'action' => $action,
            'payload' => $payload,
            'hash' => hash('sha256', $prev.$payload),
            'created_at' => now(),
        ]);
    }

    /**
     * Kiem tra ho so: chuoi bam + anh tren dia + nhan hien tai trong DB phai
     * khop dung voi ban da cong bo.
     *
     * @return array{ok: bool, chain_ok: bool, photos_ok: bool, labels_ok: bool}
     */
    public function verify(Lot $lot): array
    {
        $logs = $lot->auditLogs()->get();

        $chainOk = $logs->isNotEmpty();
        $prev = '';
        foreach ($logs as $log) {
            if (! hash_equals(hash('sha256', $prev.$log->payload), $log->hash)) {
                $chainOk = false;
                break;
            }
            $prev = $log->hash;
        }

        $published = $logs->where('action', 'published')->last()?->data();

        $photosOk = $published !== null;
        foreach ($published['photos'] ?? [] as $p) {
            $disk = Storage::disk('public');
            if (! $disk->exists($p['path']) || ! hash_equals($p['sha256'], hash('sha256', $disk->get($p['path'])))) {
                $photosOk = false;
                break;
            }
        }

        $labelsOk = $published !== null
            && self::labelSnapshot($lot) == ($published['labels'] ?? null);

        return [
            'ok' => $chainOk && $photosOk && $labelsOk,
            'chain_ok' => $chainOk,
            'photos_ok' => $photosOk,
            'labels_ok' => $labelsOk,
        ];
    }

    /** Ket luan cuoi cung theo tieu chi — dung chung cho payload `published` va verify(). */
    public static function labelSnapshot(Lot $lot): array
    {
        return $lot->currentLabels()
            ->map(fn ($l) => [
                'decision' => $l->decision,
                'final_value' => $l->final_value,
                'confirmed_by' => $l->confirmer?->name,
            ])
            ->sortKeys()
            ->all();
    }

    /** JSON sap xep key de cung du lieu luon ra cung chuoi -> cung hash. */
    public static function canonicalJson(array $data): string
    {
        $sort = function ($v) use (&$sort) {
            if (! is_array($v)) {
                return $v;
            }
            if (! array_is_list($v)) {
                ksort($v);
            }

            return array_map($sort, $v);
        };

        return json_encode($sort($data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
    }
}

<?php

namespace App\Services;

use App\Models\AiQualityAssessment;
use Illuminate\Support\Facades\Storage;

/**
 * Xac minh toan ven cho bang ai_quality_assessments (append-only).
 * KHONG chua recordAssessment/live-scan bridge (ngoai pham vi ban demo).
 */
class TraceabilityService
{
    /** SHA-256 cua chuoi canonical — tinh lai duoc de doi chieu (muc 5 spec). */
    public function computeHash(AiQualityAssessment $a): string
    {
        $p = $a->product()->first();

        $canonical = implode('|', [
            $p?->qr_code,
            (string) $a->product_id,
            (string) $a->quality_score,
            (string) $a->ripeness_level,
            $a->defect_detected ? '1' : '0',
            (string) $a->image_sha256,
            (string) $p?->farmer_id,
            $a->assessed_at?->toIso8601String(),
        ]);

        return hash('sha256', $canonical);
    }

    /**
     * @return array{status: string, hash_ok: bool, image_ok: bool}
     */
    public function verify(AiQualityAssessment $a): array
    {
        if (! $a->integrity_hash) {
            return ['status' => 'unverified', 'hash_ok' => false, 'image_ok' => false];
        }

        $hashOk = hash_equals($a->integrity_hash, $this->computeHash($a));

        $imageOk = false;
        if ($a->image_sha256 && Storage::disk('public')->exists($a->image_url)) {
            $imageOk = hash_equals(
                $a->image_sha256,
                hash('sha256', Storage::disk('public')->get($a->image_url)),
            );
        }

        return [
            'status' => ($hashOk && $imageOk) ? 'verified' : 'mismatch',
            'hash_ok' => $hashOk,
            'image_ok' => $imageOk,
        ];
    }
}

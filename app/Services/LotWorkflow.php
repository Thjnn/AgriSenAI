<?php

namespace App\Services;

use App\Models\AiSuggestion;
use App\Models\Lot;
use App\Models\LotPhoto;
use App\Models\User;
use App\Models\VerifiedLabel;
use App\Services\Quality\ImageNormalizer;
use App\Services\Quality\PhotoQualityChecker;
use App\Services\Quality\QualityAssessor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * 5 buoc ho so lo (so do quy trinh cua co Hang):
 *   create -> addPhoto -> analyze (AI goi y) -> confirm (HITL) -> publish (QR).
 *
 * Moi buoc ghi mot dong audit noi chuoi bam. Controller, job va seeder demo
 * deu di qua day de du lieu demo giong het du lieu that.
 */
class LotWorkflow
{
    public function __construct(
        private readonly AuditChain $audit,
        private readonly ImageNormalizer $normalizer,
        private readonly PhotoQualityChecker $checker,
        private readonly QualityAssessor $assessor,
    ) {}

    public function create(array $data, User $by): Lot
    {
        return DB::transaction(function () use ($data, $by) {
            $lot = Lot::create($data + ['code' => Lot::newCode(), 'created_by' => $by->id, 'status' => 'draft']);

            $this->audit->append($lot, 'created', [
                'code' => $lot->code,
                'supplier' => $lot->supplier,
                'variety' => $lot->variety,
                'purpose' => $lot->purpose,
                'weight_kg' => (string) $lot->weight_kg,
                'harvest_date' => $lot->harvest_date->toDateString(),
                'by' => $by->name,
            ], $by);

            return $lot;
        });
    }

    /** Chuan hoa (xoay EXIF, cat vuong, 768) -> kiem tra mo/toi -> luu + bam SHA-256. */
    public function addPhoto(Lot $lot, string $srcPath, string $view, User $by): LotPhoto
    {
        $this->assertEditable($lot);

        [$w, $h] = getimagesize($srcPath) ?: [0, 0];
        $tmp = tempnam(sys_get_temp_dir(), 'agrisen_').'.jpg';

        try {
            $this->normalizer->normalize($srcPath, $tmp);
            $quality = $this->checker->check($tmp, $w, $h);
            $bytes = file_get_contents($tmp);
        } finally {
            @unlink($tmp);
        }

        $path = "lots/{$lot->code}/".Str::uuid().'.jpg';
        Storage::disk('public')->put($path, $bytes);

        return DB::transaction(function () use ($lot, $path, $bytes, $view, $quality, $by) {
            $photo = $lot->photos()->create([
                'path' => $path,
                'sha256' => hash('sha256', $bytes),
                'view' => $view,
                'quality' => $quality,
            ]);

            $this->audit->append($lot, 'photo_added', [
                'photo_id' => $photo->id,
                'sha256' => $photo->sha256,
                'view' => $view,
                'quality_ok' => $quality['ok'],
            ], $by);

            return $photo;
        });
    }

    /**
     * Xoa anh chup nham (anh man hinh, anh khong phai lo hang...). Goi y AI va
     * xac nhan cu da dua tren bo anh cu nen lo quay ve `draft`, phai lam lai.
     * Dong audit `photo_added` cu van con — nhat ky chi them, khong xoa.
     */
    public function removePhoto(Lot $lot, LotPhoto $photo, User $by): void
    {
        $this->assertEditable($lot);
        abort_unless($photo->lot_id === $lot->id, 404);

        DB::transaction(function () use ($lot, $photo, $by) {
            $this->audit->append($lot, 'photo_removed', [
                'photo_id' => $photo->id,
                'sha256' => $photo->sha256,
            ], $by);

            $photo->delete();
            $lot->update(['status' => 'draft']);
        });

        Storage::disk('public')->delete($photo->path);
    }

    /**
     * Goi Gemini tren cac anh dat chat luong. Luon ghi ai_suggestions (ca khi
     * loi) de con dau vet; loi thi nguoi dung van xac nhan thu cong duoc.
     */
    public function analyze(Lot $lot): AiSuggestion
    {
        $photos = $lot->photos()->get()->filter->isUsable()->take(config('agrisenai.max_photos_per_analysis'));
        $start = hrtime(true);
        $result = null;
        $error = null;

        try {
            if ($photos->isEmpty()) {
                throw new \RuntimeException('Chưa có ảnh đạt chất lượng để AI phân tích.');
            }
            $paths = $photos->map(fn ($p) => Storage::disk('public')->path($p->path))->all();
            $result = $this->assessor->assess($paths, $lot->purpose);
        } catch (Throwable $e) {
            $error = Str::limit($e->getMessage(), 1000);
        }

        $sugg = AiSuggestion::create([
            'lot_id' => $lot->id,
            'model' => (string) config('services.gemini.model'),
            'prompt_version' => config('agrisenai.prompt_version'),
            'photo_ids' => $photos->pluck('id')->values()->all(),
            'result' => $result,
            'error' => $error,
            'latency_ms' => (int) ((hrtime(true) - $start) / 1e6),
        ]);

        $this->audit->append($lot, 'ai_suggested', [
            'suggestion_id' => $sugg->id,
            'model' => $sugg->model,
            'prompt_version' => $sugg->prompt_version,
            'photo_ids' => $sugg->photo_ids,
            'result_sha256' => $result ? hash('sha256', AuditChain::canonicalJson($result)) : null,
            'error' => $error,
        ]);

        // Chi doi trang thai neu nguoi dung chua tu xac nhan trong luc cho AI.
        if ($lot->fresh()->status === 'analyzing') {
            $lot->update(['status' => 'analyzed']);
        }

        return $sugg;
    }

    /**
     * Nguoi phu trach xac nhan CA 5 tieu chi. Moi tieu chi = mot nhan du lieu.
     *
     * @param  array<string, array{decision: string, value?: string, defect_types?: string[], note?: string}>  $items
     */
    public function confirm(Lot $lot, array $items, User $by): void
    {
        $this->assertEditable($lot);

        $sugg = $lot->latestSuggestion;
        $ai = $sugg?->hasResult() ? $sugg->result : null;
        $rows = [];
        $errors = [];

        foreach (config('agrisenai.criteria') as $key => $c) {
            $item = $items[$key] ?? null;
            $decision = $item['decision'] ?? null;
            $aiValue = $ai[$key] ?? null;

            if (! array_key_exists((string) $decision, VerifiedLabel::DECISIONS)) {
                $errors["items.{$key}.decision"] = "Chưa chọn quyết định cho tiêu chí {$c['label']}.";

                continue;
            }
            if (in_array($decision, ['accept', 'edit', 'reject'], true) && $aiValue === null) {
                $errors["items.{$key}.decision"] = "Không có gợi ý AI cho {$c['label']} — hãy nhập thủ công.";

                continue;
            }

            $final = match ($decision) {
                'reject' => null,
                'accept' => array_filter([
                    'value' => $aiValue['value'] ?? null,
                    'note' => $aiValue['note'] ?? null,
                    'defect_types' => $key === 'surface'
                        ? array_values(array_unique(array_column($aiValue['defects'] ?? [], 'type')))
                        : null,
                ], fn ($v) => $v !== null),
                default => array_filter([
                    'value' => $item['value'] ?? null,
                    'note' => filled($item['note'] ?? null) ? $item['note'] : null,
                    'defect_types' => $key === 'surface' ? array_values($item['defect_types'] ?? []) : null,
                ], fn ($v) => $v !== null),
            };

            if (in_array($decision, ['edit', 'manual'], true)) {
                if (! array_key_exists((string) ($final['value'] ?? ''), $c['options'])) {
                    $errors["items.{$key}.value"] = "Chọn giá trị hợp lệ cho {$c['label']}.";

                    continue;
                }
                if (array_diff($final['defect_types'] ?? [], array_keys(config('agrisenai.defect_types')))) {
                    $errors["items.{$key}.defect_types"] = 'Loại khuyết tật không hợp lệ.';

                    continue;
                }
            }

            $rows[$key] = [
                'criterion' => $key,
                'decision' => $decision,
                'suggestion_id' => $aiValue !== null ? $sugg->id : null,
                'ai_value' => $aiValue,
                'final_value' => $final,
                'note' => $item['note'] ?? null,
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        DB::transaction(function () use ($lot, $rows, $by) {
            $now = now();
            foreach ($rows as $row) {
                $lot->labels()->create($row + ['confirmed_by' => $by->id, 'confirmed_at' => $now]);
            }

            $this->audit->append($lot, 'confirmed', [
                'by' => $by->name,
                'org' => $by->org_name,
                'labels' => collect($rows)->map(fn ($r) => [
                    'decision' => $r['decision'],
                    'final_value' => $r['final_value'],
                ])->sortKeys()->all(),
            ], $by);

            $lot->update(['status' => 'confirmed']);
        });
    }

    /** Khoa lo; payload chua SHA-256 anh + ket luan de trang QR chung minh khong bi doi. */
    public function publish(Lot $lot, User $by): void
    {
        $this->assertEditable($lot);

        if ($lot->status !== 'confirmed') {
            throw ValidationException::withMessages(['lot' => 'Lô phải được xác nhận đủ 5 tiêu chí trước khi công bố.']);
        }

        DB::transaction(function () use ($lot, $by) {
            $this->audit->append($lot, 'published', [
                'by' => $by->name,
                'org' => $by->org_name,
                'photos' => $lot->photos()->get()->map(fn ($p) => ['path' => $p->path, 'sha256' => $p->sha256])->all(),
                'labels' => AuditChain::labelSnapshot($lot),
            ], $by);

            $lot->update(['status' => 'published', 'published_at' => now()]);
        });
    }

    private function assertEditable(Lot $lot): void
    {
        if ($lot->isPublished()) {
            throw ValidationException::withMessages(['lot' => 'Lô đã công bố, không sửa được nữa. Muốn đổi thì tạo lô mới.']);
        }
    }
}

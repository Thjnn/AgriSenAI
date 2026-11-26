<?php

namespace App\Http\Controllers;

use App\Models\AiSuggestion;
use App\Models\AuditLog;
use App\Models\Lot;
use App\Models\LotPhoto;
use App\Models\VerifiedLabel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/**
 * So lieu van hanh (ROI cho thuyet trinh) + xuat dataset (anh + nhan da duyet).
 * Chi admin.
 */
class StatsController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $byStatus = Lot::selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');

        // Thoi gian lap ho so: tao lo -> cong bo (phut). Tinh bang PHP cho chay duoc ca MySQL lan SQLite.
        $minutes = Lot::whereNotNull('published_at')->get(['created_at', 'published_at'])
            ->map(fn ($l) => $l->created_at->diffInMinutes($l->published_at));

        // Ti le AI duoc chap nhan nguyen van, theo tung tieu chi (chi tinh lan co goi y AI).
        $decisions = VerifiedLabel::selectRaw('criterion, decision, count(*) as n')
            ->groupBy('criterion', 'decision')->get()
            ->groupBy('criterion')
            ->map(fn ($rows) => $rows->pluck('n', 'decision'));

        // Ti le dong y theo phien ban prompt — doi prompt co lam AI tot hon khong.
        $byVersion = VerifiedLabel::join('ai_suggestions', 'ai_suggestions.id', '=', 'verified_labels.suggestion_id')
            ->whereIn('verified_labels.decision', ['accept', 'edit', 'reject'])
            ->selectRaw('ai_suggestions.prompt_version as version, verified_labels.decision, count(*) as n')
            ->groupBy('version', 'verified_labels.decision')->get()
            ->groupBy('version')
            ->map(fn ($rows) => $rows->pluck('n', 'decision'))
            ->sortKeysDesc();

        // AI hay sai the nao: cap (goi y AI -> nguoi sua thanh) gap nhieu nhat.
        $topEdits = VerifiedLabel::where('decision', 'edit')->get(['criterion', 'ai_value', 'final_value'])
            ->countBy(fn ($l) => implode('|', [$l->criterion, $l->ai_value['value'] ?? '?', $l->final_value['value'] ?? '?']))
            ->sortDesc()->take(5)
            ->map(function ($n, $k) {
                [$c, $from, $to] = explode('|', $k);
                $opt = fn ($v) => config("agrisenai.criteria.{$c}.options.{$v}", $v);

                return ['criterion' => config("agrisenai.criteria.{$c}.label", $c), 'from' => $opt($from), 'to' => $opt($to), 'n' => $n];
            })->values();

        $latencies = AiSuggestion::whereNull('error')->pluck('latency_ms')->sort()->values();

        // KPI co tu so / mau so (gop y thay Nguyen 43). Dem anh loi bang PHP tren cot JSON cho chay ca MySQL lan SQLite.
        $photos = LotPhoto::get(['quality']);
        $published = $byStatus['published'] ?? 0;
        $feedback = AuditLog::where('action', 'buyer_feedback')->get(['payload'])->map->data();
        $kpis = [
            ['label' => 'Tỷ lệ hồ sơ hoàn tất', 'num' => $published, 'den' => $byStatus->sum(), 'note' => 'Lô đã công bố / tổng lô đã tạo'],
            ['label' => 'Tỷ lệ ảnh phải chụp lại', 'num' => $photos->reject->isUsable()->count(), 'den' => $photos->count(), 'note' => 'Ảnh không đạt kiểm tra mờ/tối/sáng phía server / tổng ảnh'],
            ['label' => 'Tỷ lệ tiêu chí nhập tay', 'num' => VerifiedLabel::where('decision', 'manual')->count(), 'den' => VerifiedLabel::count(), 'note' => 'Không có hoặc không dùng gợi ý AI / tổng lần xác nhận tiêu chí'],
            ['label' => 'Lô được người mua quét QR', 'num' => DB::table('lot_scans')->where('from_qr', true)->distinct()->count('lot_id'), 'den' => $published, 'note' => 'Lô có ít nhất 1 lượt quét QR (khách chưa đăng nhập) / lô đã công bố'],
            ['label' => 'Phản hồi "không đúng hồ sơ"', 'num' => $feedback->where('result', 'khong_dung')->count(), 'den' => $feedback->count(), 'note' => 'Bên nhận hàng báo không đúng / tổng phản hồi'],
            ['label' => 'Hồ sơ phải đính chính', 'num' => AuditLog::where('action', 'corrected')->distinct()->count('lot_id'), 'den' => $published, 'note' => 'Lô có đính chính / lô đã công bố'],
        ];

        return view('stats.index', [
            'byStatus' => $byStatus,
            'total' => $byStatus->sum(),
            'avgMinutes' => $minutes->avg(),
            'decisions' => $decisions,
            'byVersion' => $byVersion,
            'topEdits' => $topEdits,
            'scans' => DB::table('lot_scans')->selectRaw('sum(case when from_qr then 1 else 0 end) as qr, count(*) as total')->first(),
            'aiCalls' => AiSuggestion::count(),
            'aiErrors' => AiSuggestion::whereNotNull('error')->count(),
            'kpis' => $kpis,
            'medianLatency' => $latencies->isEmpty() ? null : $latencies[intdiv($latencies->count(), 2)],
        ]);
    }

    /**
     * Zip: images/<ma lo>/<file>.jpg + labels.jsonl (moi dong = 1 lo: anh,
     * goi y AI goc, nhan nguoi xac nhan). Day la tai san du lieu cho Giai doan 2.
     */
    public function dataset(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $zipPath = tempnam(sys_get_temp_dir(), 'dataset_');
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::OVERWRITE);
        $disk = Storage::disk('public');
        $lines = [];

        $lots = Lot::whereIn('status', ['confirmed', 'published'])->with(['photos', 'latestSuggestion'])->orderBy('id')->get();

        foreach ($lots as $lot) {
            $photos = [];
            foreach ($lot->photos as $p) {
                $file = "images/{$lot->code}/".basename($p->path);
                if ($disk->exists($p->path)) {
                    $zip->addFromString($file, $disk->get($p->path));
                }
                $photos[] = ['file' => $file, 'sha256' => $p->sha256, 'view' => $p->view, 'quality_ok' => $p->isUsable()];
            }

            $lines[] = json_encode([
                'lot_code' => $lot->code,
                'purpose' => $lot->purpose,
                'variety' => $lot->variety,
                'status' => $lot->status,
                'photos' => $photos,
                'ai_suggestion' => $lot->latestSuggestion ? [
                    'id' => $lot->latestSuggestion->id,
                    'model' => $lot->latestSuggestion->model,
                    'prompt_version' => $lot->latestSuggestion->prompt_version,
                    'result' => $lot->latestSuggestion->result,
                ] : null,
                'labels' => $lot->currentLabels()->map(fn ($l) => [
                    'decision' => $l->decision,
                    'ai_value' => $l->ai_value,
                    'final_value' => $l->final_value,
                    'confirmed_by' => $l->confirmer?->name,
                    'confirmed_at' => $l->confirmed_at->toIso8601String(),
                ])->all(),
            ], JSON_UNESCAPED_UNICODE);
        }

        $zip->addFromString('labels.jsonl', implode("\n", $lines)."\n");
        $zip->close();

        return response()->download($zipPath, 'agrisen-dataset-'.now()->format('Ymd-His').'.zip')->deleteFileAfterSend();
    }
}

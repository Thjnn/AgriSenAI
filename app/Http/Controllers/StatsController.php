<?php

namespace App\Http\Controllers;

use App\Models\AiSuggestion;
use App\Models\Lot;
use App\Models\VerifiedLabel;
use Illuminate\Http\Request;
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

        $latencies = AiSuggestion::whereNull('error')->pluck('latency_ms')->sort()->values();

        return view('stats.index', [
            'byStatus' => $byStatus,
            'total' => $byStatus->sum(),
            'avgMinutes' => $minutes->avg(),
            'decisions' => $decisions,
            'aiCalls' => AiSuggestion::count(),
            'aiErrors' => AiSuggestion::whereNotNull('error')->count(),
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

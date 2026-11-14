<?php

namespace App\Console\Commands;

use App\Services\Quality\EvalMetrics;
use App\Services\Quality\QualityAssessor;
use Illuminate\Console\Command;
use Throwable;

/**
 * Do do khop goi y AI tren tap test (goi Gemini that, ton quota).
 *
 *   php artisan quality:eval xoai [--limit=5]
 *
 * Khong do "hang" nua (da bo). Do:
 *   - co / khong khuyet tat (sach vs con lai): TP/FP/FN/TN + Precision/Recall/F1
 *     (duong tinh = co khuyet tat)
 *   - muc be mat khop dung / lech 1 bac
 *   - bat duoc khuyet tat an vao thit qua (into_flesh)
 * Anh testset DA chuan hoa san (768x768).
 *
 * Gop y thay Nguyen 31/47: nhan chua co nguoi duyet (`reviewed_by`) -> canh bao
 * KHONG dua so vao ho so. --csv=duong_dan ghi danh sach tung anh (danh sach loi).
 */
class EvalTestset extends Command
{
    protected $signature = 'quality:eval
                            {loai : Loai nong san (vd: xoai)}
                            {--limit= : Chi chay N anh dau (de thu nhanh)}
                            {--csv= : Ghi ket qua tung anh ra file CSV (UTF-8 BOM, mo bang Excel)}';

    protected $description = 'Goi y AI tren tap test roi so voi ground_truth.json';

    public function handle(QualityAssessor $assessor): int
    {
        $loai = $this->argument('loai');
        $dir = storage_path("app/testset/{$loai}");
        $gtFile = "{$dir}/ground_truth.json";

        if (! is_file($gtFile)) {
            $this->error("Khong thay {$gtFile}");

            return self::FAILURE;
        }

        $items = json_decode(file_get_contents($gtFile), true, 512, JSON_THROW_ON_ERROR)['labels'] ?? [];
        if ($limit = $this->option('limit')) {
            $items = array_slice($items, 0, (int) $limit);
        }

        $order = array_keys(config('agrisenai.criteria.surface.options'));   // sach -> nang
        $n = $exact = $near = $loi = $reviewed = 0;
        $pairs = [];
        $flesh = ['that' => 0, 'bat_duoc' => 0];
        $rows = [];

        foreach ($this->output->progressIterate($items) as $item) {
            try {
                $kq = $assessor->assess(["{$dir}/{$item['file']}"], $item['muc_dich_ban'] ?? 'an_chin', $loai);
            } catch (Throwable $e) {
                $loi++;
                $rows[] = [$item['file'], $item['surface'], 'LOI', substr($e->getMessage(), 0, 40), $item['reviewed_by'] ?? ''];

                continue;
            }

            $that = $item['surface'];
            $ai = $kq['surface']['value'] ?? '?';
            $kc = abs(array_search($that, $order, true) - (int) array_search($ai, $order, true));
            $aiFlesh = collect($kq['surface']['defects'] ?? [])->contains('into_flesh', true);

            $n++;
            $pairs[] = [$that !== 'sach', $ai !== 'sach'];
            $reviewed += filled($item['reviewed_by'] ?? null) ? 1 : 0;
            $exact += $ai === $that ? 1 : 0;
            $near += $kc <= 1 ? 1 : 0;
            if ($item['into_flesh'] ?? false) {
                $flesh['that']++;
                $flesh['bat_duoc'] += $aiFlesh ? 1 : 0;
            }

            $rows[] = [$item['file'], $that, $ai, $ai === $that ? 'dung' : ($kc === 1 ? 'lech 1 bac' : 'LECH XA'), $item['reviewed_by'] ?? ''];
        }

        $header = ['Anh', 'Be mat that', 'AI goi y', 'Ket qua', 'Nguoi duyet nhan'];
        $this->table($header, $rows);

        if ($csv = $this->option('csv')) {
            $f = fopen($csv, 'w');
            fwrite($f, "\xEF\xBB\xBF");
            fputcsv($f, $header);
            foreach ($rows as $r) {
                fputcsv($f, $r);
            }
            fclose($f);
            $this->info("Da ghi {$csv}");
        }

        if ($n === 0) {
            $this->error('Khong goi y duoc anh nao.');

            return self::FAILURE;
        }

        $pct = fn ($x) => sprintf('%d/%d (%.0f%%)', $x, $n, $x / $n * 100);
        $this->info('── KET QUA ─────────────────────────────');
        $m = EvalMetrics::binary($pairs);
        $ratio = fn (?float $x) => $x === null ? '— (mau so 0)' : sprintf('%.2f', $x);
        $this->line("  Co khuyet tat  TP {$m['tp']}  FP {$m['fp']}  FN {$m['fn']}  TN {$m['tn']}");
        $this->line('  Precision / Recall / F1  : '.$ratio($m['precision']).' / '.$ratio($m['recall']).' / '.$ratio($m['f1']));
        $this->line('  Muc be mat khop dung     : '.$pct($exact));
        $this->line('  Khop hoac lech 1 bac     : '.$pct($near));
        $this->line("  Bat duoc an vao thit qua : {$flesh['bat_duoc']}/{$flesh['that']}");
        if ($loi > 0) {
            $this->error("  Anh loi                  : {$loi}");
        }
        $this->newLine();
        if ($reviewed < $n) {
            $this->warn("  Chi {$reviewed}/{$n} nhan co nguoi duyet — KHONG dua con so nay vao ho so/PPT.");
        }
        $this->comment("  n={$n}: mau nho, sai so lon; chi la so do noi bo.");

        return self::SUCCESS;
    }
}

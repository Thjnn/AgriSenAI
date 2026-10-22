<?php

namespace App\Console\Commands;

use App\Services\Quality\QualityAssessor;
use Illuminate\Console\Command;
use Throwable;

/**
 * Do do khop goi y AI tren tap test (goi Gemini that, ton quota).
 *
 *   php artisan quality:eval xoai [--limit=5]
 *
 * Khong do "hang" nua (da bo). Do:
 *   - co / khong khuyet tat (sach vs con lai)
 *   - muc be mat khop dung / lech 1 bac
 *   - bat duoc khuyet tat an vao thit qua (into_flesh)
 * Anh testset DA chuan hoa san (768x768).
 */
class EvalTestset extends Command
{
    protected $signature = 'quality:eval
                            {loai : Loai nong san (vd: xoai)}
                            {--limit= : Chi chay N anh dau (de thu nhanh)}';

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
        $n = $presence = $exact = $near = $loi = 0;
        $flesh = ['that' => 0, 'bat_duoc' => 0];
        $rows = [];

        foreach ($this->output->progressIterate($items) as $item) {
            try {
                $kq = $assessor->assess(["{$dir}/{$item['file']}"], $item['muc_dich_ban'] ?? 'an_chin', $loai);
            } catch (Throwable $e) {
                $loi++;
                $rows[] = [$item['file'], $item['surface'], 'LOI', substr($e->getMessage(), 0, 40)];

                continue;
            }

            $that = $item['surface'];
            $ai = $kq['surface']['value'] ?? '?';
            $kc = abs(array_search($that, $order, true) - (int) array_search($ai, $order, true));
            $aiFlesh = collect($kq['surface']['defects'] ?? [])->contains('into_flesh', true);

            $n++;
            $presence += ($that === 'sach') === ($ai === 'sach') ? 1 : 0;
            $exact += $ai === $that ? 1 : 0;
            $near += $kc <= 1 ? 1 : 0;
            if ($item['into_flesh'] ?? false) {
                $flesh['that']++;
                $flesh['bat_duoc'] += $aiFlesh ? 1 : 0;
            }

            $rows[] = [$item['file'], $that, $ai, $ai === $that ? 'dung' : ($kc === 1 ? 'lech 1 bac' : 'LECH XA')];
        }

        $this->table(['Anh', 'Be mat that', 'AI goi y', 'Ket qua'], $rows);

        if ($n === 0) {
            $this->error('Khong goi y duoc anh nao.');

            return self::FAILURE;
        }

        $pct = fn ($x) => sprintf('%d/%d (%.0f%%)', $x, $n, $x / $n * 100);
        $this->info('── KET QUA ─────────────────────────────');
        $this->line('  Co/khong khuyet tat khop : '.$pct($presence));
        $this->line('  Muc be mat khop dung     : '.$pct($exact));
        $this->line('  Khop hoac lech 1 bac     : '.$pct($near));
        $this->line("  Bat duoc an vao thit qua : {$flesh['bat_duoc']}/{$flesh['that']}");
        if ($loi > 0) {
            $this->error("  Anh loi                  : {$loi}");
        }
        $this->newLine();
        $this->comment('  Luu y: nhan that do AI de xuat, chua co chuyen gia duyet; n=25 sai so ~±10 diem %.');

        return self::SUCCESS;
    }
}

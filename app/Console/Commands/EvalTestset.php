<?php

namespace App\Console\Commands;

use App\Services\Quality\QualityAssessor;
use Illuminate\Console\Command;
use Throwable;

/**
 * Do do chinh xac cua module AI tren tap test.
 *
 *   php artisan quality:eval xoai
 *
 * Anh trong testset DA duoc chuan hoa san (768x768) nen khong chuan hoa lai.
 */
class EvalTestset extends Command
{
    protected $signature = 'quality:eval
                            {loai : Loai nong san (vd: xoai)}
                            {--limit= : Chi chay N anh dau (de thu nhanh)}';

    protected $description = 'Cham tap test bang Gemini roi so voi ground_truth.json';

    public function handle(QualityAssessor $assessor): int
    {
        $loai = $this->argument('loai');
        $dir = storage_path("app/testset/{$loai}");
        $gtFile = "{$dir}/ground_truth.json";

        if (! is_file($gtFile)) {
            $this->error("Khong thay {$gtFile}");

            return self::FAILURE;
        }

        $gt = json_decode(file_get_contents($gtFile), true, 512, JSON_THROW_ON_ERROR);
        $items = $gt['labels'] ?? [];

        if ($limit = $this->option('limit')) {
            $items = array_slice($items, 0, (int) $limit);
        }

        $order = array_keys(config('agrisenai.grades'));
        $dung = 0;
        $lech1 = 0;      // lech dung 1 bac
        $loi = 0;
        $rows = [];
        $confusion = [];
        $haHang = 0;

        $bar = $this->output->createProgressBar(count($items));
        $bar->start();

        foreach ($items as $item) {
            $path = "{$dir}/{$item['file']}";

            try {
                $kq = $assessor->assess(
                    imagePath: $path,
                    loai: $loai,
                    mucDichBan: $item['muc_dich_ban'] ?? 'an_chin',
                    daChuanHoa: true,
                );
            } catch (Throwable $e) {
                $loi++;
                $rows[] = [$item['file'], $item['grade'], 'LOI', '-', substr($e->getMessage(), 0, 40)];
                $bar->advance();

                continue;
            }

            $that = $item['grade'];
            $duDoan = $kq['grade'] ?? '?';
            $khoangCach = abs(
                array_search($that, $order, true) - (array_search($duDoan, $order, true) ?: 0)
            );

            if ($duDoan === $that) {
                $dung++;
            } elseif ($khoangCach === 1) {
                $lech1++;
            }

            if ($kq['rule_engine']['da_ha_hang'] ?? false) {
                $haHang++;
            }

            $confusion[$that][$duDoan] = ($confusion[$that][$duDoan] ?? 0) + 1;

            $rows[] = [
                $item['file'],
                $that,
                $duDoan,
                number_format((float) ($kq['overall_score'] ?? 0), 1),
                $duDoan === $that ? 'dung' : ($khoangCach === 1 ? 'lech 1 bac' : 'LECH XA'),
            ];

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(['Anh', 'Hang that', 'AI cham', 'Diem', 'Ket qua'], $rows);

        $n = count($items) - $loi;
        if ($n <= 0) {
            $this->error('Khong cham duoc anh nao.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('── KET QUA ─────────────────────────────');
        $this->line(sprintf('  Khop chinh xac      : %d/%d  (%.1f%%)', $dung, $n, $dung / $n * 100));
        $this->line(sprintf('  Khop hoac lech 1 bac: %d/%d  (%.1f%%)', $dung + $lech1, $n, ($dung + $lech1) / $n * 100));
        $this->line(sprintf('  Rule engine ha hang : %d lan', $haHang));
        if ($loi > 0) {
            $this->error("  Anh loi             : {$loi}");
        }

        $this->newLine();
        $this->line('  Ma tran nham lan (hang that -> AI cham):');
        foreach ($order as $that) {
            if (! isset($confusion[$that])) {
                continue;
            }
            $chi = [];
            foreach ($confusion[$that] as $duDoan => $sl) {
                $chi[] = "{$duDoan}={$sl}";
            }
            $this->line(sprintf('    %-10s -> %s', $that, implode(', ', $chi)));
        }

        $this->newLine();
        $this->comment('  Luu y: nhan that do AI de xuat, chua co chuyen gia nong nghiep duyet.');
        $this->comment('  Hang `khong_dat` chi co 2 qua — con so tren hang do gan nhu vo nghia.');

        return self::SUCCESS;
    }
}

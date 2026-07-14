<?php

namespace App\Console\Commands;

use App\Services\Quality\ImageNormalizer;
use Illuminate\Console\Command;
use JsonException;
use Throwable;

/**
 * Dung lai bo anh few-shot tu dataset goc.
 *
 * Anh goc nam NGOAI git. Lenh nay chi chay tren may dev khi can dung lai
 * hoac doi anh mau — nguoi clone repo ve khong can chay, vi anh da commit san.
 *
 *   php artisan reference:build xoai
 */
class BuildReferenceSet extends Command
{
    protected $signature = 'reference:build
                            {loai : Loai nong san (vd: xoai)}
                            {--set=reference : reference | testset}';

    protected $description = 'Dung bo anh few-shot / tap test tu source.json (chuan hoa 768x768 JPEG)';

    public function handle(ImageNormalizer $normalizer): int
    {
        $loai = $this->argument('loai');
        $set = $this->option('set');

        if (! in_array($set, ['reference', 'testset'], true)) {
            $this->error("--set phai la 'reference' hoac 'testset'");

            return self::FAILURE;
        }

        $dir = storage_path("app/{$set}/{$loai}");
        $sourceFile = "{$dir}/source.json";

        if (! is_file($sourceFile)) {
            $this->error("Khong thay {$sourceFile}");

            return self::FAILURE;
        }

        try {
            $source = json_decode(file_get_contents($sourceFile), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->error("source.json hong: {$e->getMessage()}");

            return self::FAILURE;
        }

        $size = config('agrisenai.image.size');
        $grades = array_keys(config('agrisenai.grades'));
        $ok = 0;
        $loi = 0;

        foreach ($source['anh'] ?? [] as $anh) {
            $goc = base_path($anh['goc']);
            $dst = "{$dir}/{$anh['file']}";

            // Anh reference bat buoc co grade (no di thang vao prompt).
            // Anh testset thi khong — nhan that nam trong ground_truth.json.
            if ($set === 'reference' && ! in_array($anh['grade'] ?? null, $grades, true)) {
                $this->error("  {$anh['file']}: grade khong hop le '".($anh['grade'] ?? 'thieu')."'");
                $loi++;

                continue;
            }

            try {
                $normalizer->normalize($goc, $dst, $anh['crop']);
            } catch (Throwable $e) {
                $this->error("  {$anh['file']}: {$e->getMessage()}");
                $loi++;

                continue;
            }

            $kb = round(filesize($dst) / 1024);
            $this->line(sprintf('  <info>%-22s</info> %-10s %dx%d  %d KB',
                $anh['file'], $anh['grade'] ?? '-', $size, $size, $kb));
            $ok++;
        }

        $this->newLine();
        $this->info("Xong: {$ok} anh -> storage/app/{$set}/{$loai}/");

        if ($loi > 0) {
            $this->error("{$loi} anh loi.");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Lot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Demo "gia mao" cho buoi thuyet trinh: sua thang khoi luong lo da cong bo
 * trong DB (bo qua LotWorkflow, nhu ke co quyen ghi DB), roi quet QR thay dai do.
 *
 *   php artisan demo:gia-mao XL-261022-AB12            # tang khoi luong them 100 kg
 *   php artisan demo:gia-mao XL-261022-AB12 --hoan-tac # tra lai so lieu da cong bo
 *
 * Chi sua bang `lots`, khong dung toi nhat ky audit.
 */
class DemoTamper extends Command
{
    protected $signature = 'demo:gia-mao
                            {ma : Ma lo da cong bo}
                            {--hoan-tac : Tra khoi luong ve dung ban da cong bo}';

    protected $description = 'Demo: sua len khoi luong lo da cong bo de trang QR chuyen do';

    public function handle(): int
    {
        $lot = Lot::where('code', strtoupper($this->argument('ma')))->where('status', 'published')->first();
        if (! $lot) {
            $this->error('Khong tim thay lo da cong bo voi ma nay.');

            return self::FAILURE;
        }

        $published = $lot->auditLogs()->where('action', 'published')->get()->last()?->data();
        $original = $published['lot']['weight_kg'] ?? null;
        if ($original === null) {
            $this->error('Lo cong bo truoc khi co ky thong tin lo — chay lai migrate:fresh --seed.');

            return self::FAILURE;
        }

        $weight = $this->option('hoan-tac') ? $original : number_format((float) $original + 100, 2, '.', '');
        DB::table('lots')->where('id', $lot->id)->update(['weight_kg' => $weight]);

        $this->info("Khoi luong {$lot->code}: {$lot->weight_kg} kg -> {$weight} kg (ban cong bo: {$original} kg)");
        $this->line('Mo lai: '.route('public.show', $lot));

        return self::SUCCESS;
    }
}

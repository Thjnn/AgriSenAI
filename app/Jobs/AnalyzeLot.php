<?php

namespace App\Jobs;

use App\Models\Lot;
use App\Services\LotWorkflow;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Goi Gemini cho mot lo. Gemini cham va that thuong (trung vi 10 s, co lan > 30 s),
 * nen controller dispatch SAU KHI tra response, trang thi poll trang thai.
 *
 * ponytail: dispatchAfterResponse chay trong chinh tien trinh web, khong can
 * queue worker. Nhieu diem thu gom dung cung luc thi chuyen sang ShouldQueue.
 */
class AnalyzeLot
{
    use Dispatchable;

    public function __construct(public readonly int $lotId) {}

    public function handle(LotWorkflow $workflow): void
    {
        set_time_limit(0);
        $workflow->analyze(Lot::findOrFail($this->lotId));
    }
}
